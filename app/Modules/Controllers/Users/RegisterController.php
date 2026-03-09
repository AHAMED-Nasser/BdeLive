<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Users;

use App\Config\Mailer;
use App\Core\Security\RecaptchaValidator;
use App\Modules\Controllers\DefaultController;
use App\Modules\Models\Users\UserManager;
use Exception;

/**
 * Register Controller
 *
 * Handles user registration operations including form display, input validation,
 * account creation with email verification.
 *
 * @package BdeLive\Controllers
 * @author Mohamed-Amine Boudhib, Thomas Palot, Amin Helali, Willem Chetioui, Nasser Ahamed, Romain Cantor
 * @version 1.0.0
 */
class RegisterController extends DefaultController
{
    /**
     * User manager instance
     *
     * @var UserManager
     */
    private UserManager $userManager;

    /**
     * Constructor - Initialize the RegisterController
     *
     * Creates a new UserManager instance for handling registration operations.
     */
    public function __construct()
    {
        parent::__construct();

        $this->userManager = new UserManager();

        // Handle form submission
        if ($this->request->isPost() && $this->request->post('ok') !== null) {
            $this->handleRegistration();
        } else {
            $this->render('users/registerPageView', $this->buildRegisterViewData());
        }
    }

    /**
     * Process the registration form submission
     *
     * Validates all user input (required fields, email format, password strength,
     * class year), creates the user account via AuthController, and automatically
     * logs in the new user on success.
     *
     * @return void
     */
    /**
     * Build view data for registration page (reCAPTCHA site key).
     *
     * @return array<string, mixed>
     */
    private function buildRegisterViewData(): array
    {
        return [
            'recaptchaSiteKey' => (string) ($_ENV['RECAPTCHA_SITE_KEY'] ?? ''),
        ];
    }

    private function handleRegistration(): void
    {
        // Validate CSRF token
        $csrfToken = $this->request->post('csrf_token', '');

        if (!$this->csrf->validateToken((string) $csrfToken)) {
            $this->setError('Token de sécurité invalide. Veuillez réessayer.');
            $this->redirect('index.php?page=users/registerPageView');
        }

        // Validate reCAPTCHA (required for registration)
        $recaptchaToken = (string) ($_POST['g-recaptcha-response'] ?? '');
        $recaptchaValidator = new RecaptchaValidator();
        $clientIp = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
        if (empty($recaptchaToken) || !$recaptchaValidator->validate($recaptchaToken, $clientIp)) {
            $this->setError('Veuillez valider le CAPTCHA pour continuer l\'inscription.');
            $this->render('users/registerPageView', $this->buildRegisterViewData());
            return;
        }

        // Validate and sanitize inputs
        $last_name = trim((string) $this->request->post('last_name', ''));
        $first_name = trim((string) $this->request->post('first_name', ''));
        $user_status = trim((string) $this->request->post('user_status', ''));
        $email = trim((string) $this->request->post('email', ''));
        $pwd = (string) $this->request->post('password', '');
        $confirmPwd = trim((string) $this->request->post('confirm_password', ''));

        // old input values for repopulation in case of error
        $this->session->set('old_last_name', $last_name);
        $this->session->set('old_first_name', $first_name);
        $this->session->set('old_user_status', $user_status);
        $this->session->set('old_email', $email);

        // Validation
        if (empty($last_name) || empty($first_name) || empty($user_status) || empty($email) || empty($pwd)) {
            $this->setError('Tous les champs sont obligatoires');
            $this->render('users/registerPageView', $this->buildRegisterViewData());
            return;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setError('Format d\'email invalide');
            $this->render('users/registerPageView', $this->buildRegisterViewData());
            return;
        }

        // Validate password length
        if (strlen($pwd) < 12) {
            $this->setError('Le mot de passe doit contenir au moins 12 caractères');
            $this->render('users/registerPageView', $this->buildRegisterViewData());
            return;
        }

        // Validate user_status
        if (!in_array($user_status, ['BUT 1', 'BUT 2', 'BUT 3', 'Personnel Enseignant'])) {
            $this->setError('Statut d\'utilisateur invalide');
            $this->render('users/registerPageView', $this->buildRegisterViewData());
            return;
        }

        // Vérifier si l'email existe déjà
        if ($this->userManager->emailExists($email)) {
            $this->setError('Cette adresse email est déjà utilisée');
            $this->render('users/registerPageView', $this->buildRegisterViewData());
            return;
        }

        $pwdSecureRegex = "/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[^A-Za-z0-9]).{8,}$/";

        if (!preg_match($pwdSecureRegex, $pwd)) {
            $this->setError('Le mot de passe ne respecte pas les conditions de sécurité');
            $this->render('users/registerPageView', $this->buildRegisterViewData());
            return;
        }

        // Vérifier que les mots de passe correspondent
        if ($pwd !== $confirmPwd) {
            $this->setError('Les mots de passe ne correspondent pas');
            $this->render('users/registerPageView', $this->buildRegisterViewData());
            return;
        }

        // Attempt registration with verification token
        try {
            $result = $this->userManager->createUserWithVerification(
                $last_name,
                $first_name,
                $user_status,
                $email,
                $pwd
            );

            if ($result) {
                // Envoyer l'email de vérification
                $mailer = new Mailer();
                $emailSent = $mailer->sendVerificationEmail(
                    $email,
                    $first_name . ' ' . $last_name,
                    $result['token']
                );

                if ($emailSent) {
                    $this->setSuccess(
                        'Inscription réussie ! Un email de vérification a été envoyé à ' .
                        htmlspecialchars($email) . '. Veuillez vérifier votre boîte de réception.'
                    );
                } else {
                    $this->setError(
                        'Inscription réussie, mais l\'envoi de l\'email de vérification a échoué. ' .
                        'Veuillez contacter l\'administrateur.'
                    );
                }

                $this->session->remove('old_email');
                $this->session->remove('old_first_name');
                $this->session->remove('old_last_name');
                $this->session->remove('old_user_status');

                $this->render('users/registerPageView', $this->buildRegisterViewData());
            } else {
                $this->setError('Erreur lors de l\'inscription. Veuillez réessayer.');
                $this->render('users/registerPageView', $this->buildRegisterViewData());
            }
        } catch (Exception $e) {
            error_log('RegisterController::handleRegistration - ' . $e->getMessage());
            $this->setError('Une erreur est survenue lors de l\'inscription. Veuillez réessayer.');
            $this->render('users/registerPageView', $this->buildRegisterViewData());
        }
    }
}
