<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Users;

use App\Modules\Controllers\DefaultController;
use App\Modules\Models\Users\LoginAttemptManager;

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
    private const RECAPTCHA_VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    private LoginAttemptManager $attemptManager;
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

            if (empty($recaptchaToken) || !$this->validateRecaptcha($recaptchaToken)) {
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
        $userManager = new \App\Modules\Models\Users\UserManager();
        $user = $userManager->findUserByEmail($email);

        if (!$user || !$userManager->verifyPassword($password, $user['password'])) {
            $countBefore = $this->attemptManager->countRecentAttempts($this->clientIp, $email);
            $this->attemptManager->recordFailedAttempt($this->clientIp, $email);
            $this->isSuspect = ($countBefore + 1) >= self::ATTEMPT_THRESHOLD;

            $this->setError('Email ou mot de passe incorrect');
            $this->render('users/loginPageView', $this->buildViewData());
            return;
        }

        // Check for soft-deleted account
        if (!empty($user['deleted_at'])) {
            $this->setError('Ce compte a été clôturé. Contactez le support pour le réactiver.');
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

    /**
     * Validate a Google reCAPTCHA v2 response token
     *
     * Sends a POST request to the Google siteverify endpoint and returns
     * whether the token is valid. Reads the secret key from the environment.
     *
     * @param string $token The g-recaptcha-response value from the form
     * @return bool True if Google confirms the token is valid
     */
    private function validateRecaptcha(string $token): bool
    {
        $secretKey = (string) ($_ENV['RECAPTCHA_SECRET_KEY'] ?? '');

        if (empty($secretKey)) {
            error_log('LoginController::validateRecaptcha - RECAPTCHA_SECRET_KEY is not set in the environment.');
            return false;
        }

        $context = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query([
                    'secret'   => $secretKey,
                    'response' => $token,
                    'remoteip' => $this->clientIp,
                ]),
                'timeout' => 3,
            ],
        ]);

        $result = @file_get_contents(self::RECAPTCHA_VERIFY_URL, false, $context);

        if ($result === false) {
            error_log('LoginController::validateRecaptcha - Could not reach Google siteverify endpoint.');
            return false;
        }

        $data = json_decode($result, true);

        return isset($data['success']) && $data['success'] === true;
    }
}
