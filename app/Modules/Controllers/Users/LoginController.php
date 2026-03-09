<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Users;

use App\Core\Security\RecaptchaValidator;
use App\Modules\Controllers\DefaultController;
use App\Modules\Models\Users\LoginAttemptManager;
use App\Modules\Models\Users\UserManager;
use DateTime;

/**
 * Login Controller
 *
 * Handles user login operations including form display, input validation,
 * and authentication processing. Works with AuthController to verify
 * user credentials and establish authenticated sessions.
 *
 * Implements a conditional anti-brute-force mechanism: after 5 failed attempts
 * from the same IP + email pair within 15 minutes, a Google reCAPTCHA v2 widget
 * is shown and validated before credentials are checked.
 *
 * @package BdeLive\Controllers
 * @author Mohamed-Amine Boudhib, Thomas Palot, Amin Helali, Willem Chetioui, Nasser Ahamed, Romain Cantor
 * @version 2.0.0
 */
class LoginController extends DefaultController
{
    private const ATTEMPT_THRESHOLD = 5;

    private LoginAttemptManager $attemptManager;
    private RecaptchaValidator $recaptchaValidator;
    private string $clientIp;
    private bool $isSuspect;

    /**
     * Constructor - Initialize the LoginController
     *
     * Resolves the client IP, checks the failed-attempt count, then either
     * displays the login form or processes the submission.
     */
    public function __construct()
    {
        parent::__construct();

        $this->attemptManager = new LoginAttemptManager();
        $this->recaptchaValidator = new RecaptchaValidator();
        $this->clientIp = (string) ($_SERVER['REMOTE_ADDR'] ?? '');

        $email = trim((string) $this->request->post('email', ''));
        $isPost = $this->request->isPost() && $this->request->post('ok') !== null;
        // Skip DB on simple GET (no email): no need to count attempts for empty email
        $this->isSuspect = $isPost || $email !== ''
            ? $this->attemptManager->countRecentAttempts($this->clientIp, $email) >= self::ATTEMPT_THRESHOLD
            : false;

        if ($isPost) {
            $this->processLogin();
        } else {
            $this->render('users/loginPageView', $this->buildViewData());
        }
    }

    /**
     * Build the data array passed to the login view
     *
     * @return array<string, mixed>
     */
    private function buildViewData(): array
    {
        return [
            'isSuspect'        => $this->isSuspect,
            'recaptchaSiteKey' => (string) ($_ENV['RECAPTCHA_SITE_KEY'] ?? ''),
        ];
    }

    /**
     * Process the login form submission
     *
     * Validates CSRF token, optionally validates reCAPTCHA, verifies credentials,
     * and on success establishes the authenticated session. On failure, records
     * the attempt and updates the suspect flag for the view.
     *
     * @return void
     */
    private function processLogin(): void
    {
        // Validate CSRF token
        $csrfToken = $this->request->post('csrf_token', '');

        if (!$this->csrf->validateToken((string) $csrfToken)) {
            $this->setError('Token de sécurité invalide. Veuillez réessayer.');
            $this->redirect('users/loginPageView');
        }

        // Get and sanitize inputs
        $email = trim((string) $this->request->post('email', ''));
        $password = (string) $this->request->post('password', '');

        $this->session->set('old_email', $email);

        // Recalculate suspect flag with the actual submitted email
        $this->isSuspect = $this->attemptManager->countRecentAttempts(
            $this->clientIp,
            $email
        ) >= self::ATTEMPT_THRESHOLD;

        // Validate reCAPTCHA before anything else when the user is suspect
        if ($this->isSuspect) {
            $recaptchaToken = (string) ($_POST['g-recaptcha-response'] ?? '');

            if (empty($recaptchaToken) || !$this->recaptchaValidator->validate($recaptchaToken, $this->clientIp)) {
                $this->setError('Please complete the anti-robot validation.');
                $this->render('users/loginPageView', $this->buildViewData());
                return;
            }
        }

        // Basic field validation
        if (empty($email) || empty($password)) {
            $this->setError('Veuillez remplir tous les champs');
            $this->render('users/loginPageView', $this->buildViewData());
            return;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setError('Format d\'email invalide');
            $this->render('users/loginPageView', $this->buildViewData());
            return;
        }

        // Verify credentials
        $userManager = new UserManager();
        $user = $userManager->findUserByEmail($email);

        if (!$user || !$userManager->verifyPassword($password, $user['password'])) {
            $countBefore = $this->attemptManager->countRecentAttempts($this->clientIp, $email);
            $this->attemptManager->recordFailedAttempt($this->clientIp, $email);
            $this->isSuspect = ($countBefore + 1) >= self::ATTEMPT_THRESHOLD;

            $this->setError('Email ou mot de passe incorrect');
            $this->render('users/loginPageView', $this->buildViewData());
            return;
        }

        // Check for soft-deleted account (30-day grace period)
        if (!empty($user['deleted_at'])) {
            $deletedAt = new DateTime($user['deleted_at']);
            $expiresAt = (clone $deletedAt)->modify('+30 days');
            $now = new DateTime();
            $daysLeft = max(0, (int) $now->diff($expiresAt)->days);
            $this->setError(
                "Votre compte est en cours de suppression (période de grâce : {$daysLeft} jour(s) restant(s)). "
                . 'Contactez le support pour le réactiver.'
            );
            $this->render('users/loginPageView', $this->buildViewData());
            return;
        }

        $isBlocked = (int) $user['is_blocked'];

        if ($isBlocked === 1) {
            $this->setError('Votre compte a été suspendu par l\'administrateur.');
            $this->render('users/loginPageView', $this->buildViewData());
            return;
        }

        // Check email verification
        if ((int) $user['is_verified'] === 0) {
            $this->setError(
                'Votre adresse email n\'a pas encore été vérifiée. ' .
                'Veuillez vérifier votre boîte de réception et cliquer sur le lien de vérification dans l\'email que nous vous avons envoyé.'
            );
            $this->render('users/loginPageView', $this->buildViewData());
            return;
        }

        // Login successful — clear attempts and start session
        $this->attemptManager->clearAttempts($this->clientIp, $email);
        $this->session->remove('old_email');

        $this->auth->login(
            (int) $user['user_id'],
            $user['user_status'],
            $user['email'],
            $user['role'],
            $isBlocked,
            $user['first_name'],
            $user['last_name']
        );

        $userName = trim($user['first_name'] . ' ' . $user['last_name']);
        $this->setSuccess('Connexion réussie ! Bienvenue ' . htmlspecialchars($userName) . ' !');
        $this->redirect('index.php?page=home');
    }
}
