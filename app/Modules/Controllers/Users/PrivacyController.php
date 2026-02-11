<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Users;

use App\Core\Exception\AuthenticationException;
use App\Modules\Controllers\AuthenticatedController;
use App\Modules\Models\Users\UserManager;
use App\Modules\Models\Users\PrivacyManager;
use App\Config\Mailer;

/**
 * PrivacyController - User Privacy Settings Management
 *
 * Handles user privacy settings including email and password modifications.
 * Implements security measures such as:
 * - Password verification and email code verification for email changes
 * - 6-digit verification code for password changes
 * - Account blocking after multiple failed attempts
 * - Security alert emails for suspicious activity
 *
 * @author BdeLive Team
 * @version 1.1.0
 * @package BdeLive\Controllers\Users
 *
 * @see AuthenticatedController For authentication requirements
 * @see PrivacyManager For security and blocking operations
 * @see UserManager For user data operations
 */
class PrivacyController extends AuthenticatedController
{
    /**
     * Privacy manager instance for security operations
     *
     * @var PrivacyManager
     */
    private PrivacyManager $privacyManager;

    /**
     * User manager instance for database operations
     *
     * @var UserManager
     */
    private UserManager $userManager;

    /**
     * Mailer instance for sending emails
     *
     * @var Mailer
     */
    private Mailer $mailer;

    /**
     * Constructor - Route to appropriate action based on request
     *
     * Handles different privacy actions:
     * - requestEmailCode: Send verification code to new email
     * - resendEmailCode: Resend email verification code
     * - verifyEmailCode: Verify code and change email
     * - requestPasswordCode: Send verification code for password change
     * - resendPasswordCode: Resend verification code
     * - verifyPasswordCode: Verify code and change password
     * - default: Display privacy page
     *
     * @return void
     * @throws AuthenticationException
     */
    public function __construct()
    {
        parent::__construct();

        $this->privacyManager = new PrivacyManager();
        $this->userManager = new UserManager();
        $this->mailer = new Mailer();

        // Check if user is blocked
        $user = $this->auth->getUser();
        if ($user && $this->privacyManager->isUserBlocked($user['user_id'])) {
            $remainingTime = $this->privacyManager->getRemainingBlockTime($user['user_id']);
            $this->render('users/privacyPageView', [
                'isBlocked' => true,
                'remainingTime' => $remainingTime
            ]);
            return;
        }

        $action = $this->request->get('action', '');

        // Email change actions
        if ($action === 'requestEmailCode' && $this->request->isPost()) {
            $this->requestEmailCode();
        } elseif ($action === 'resendEmailCode' && $this->request->isPost()) {
            $this->resendEmailCode();
        } elseif ($action === 'verifyEmailCode' && $this->request->isPost()) {
            $this->verifyEmailCode();
        // Password change actions
        } elseif ($action === 'requestPasswordCode' && $this->request->isPost()) {
            $this->requestPasswordCode();
        } elseif ($action === 'resendPasswordCode' && $this->request->isPost()) {
            $this->resendPasswordCode();
        } elseif ($action === 'verifyPasswordCode' && $this->request->isPost()) {
            $this->verifyPasswordCode();
        } else {
            $this->showPrivacyPage();
        }
    }

    /**
     * Display the privacy settings page
     *
     * Shows the privacy page with email and password modification forms.
     * Also displays resend status for codes.
     *
     * @return void
     */
    private function showPrivacyPage(): void
    {
        $user = $this->auth->getUser();
        if ($user === null || !isset($user['user_id'])) {
            $this->redirect('index.php?page=login');
        }

        $userId = (int) $user['user_id'];
        $passwordResendStatus = $this->privacyManager->canResendPasswordCode($userId);
        $emailResendStatus = $this->privacyManager->canResendEmailCode($userId);

        $this->render('users/privacyPageView', [
            'isBlocked' => false,
            'passwordResendStatus' => $passwordResendStatus,
            'emailResendStatus' => $emailResendStatus,
            'hasActivePasswordCode' => $passwordResendStatus['resend_count'] > 0,
            'hasActiveEmailCode' => $emailResendStatus['resend_count'] > 0,
            'pendingEmail' => $this->session->get('pending_email_change')
        ]);
    }

    /**
     * Request email change - Step 1: Verify password and send code to new email
     *
     * Validates current password, checks email format and availability,
     * then sends a verification code to the new email address.
     *
     * @return void Redirects to email verification form
     */
    private function requestEmailCode(): void
    {
        // Validate CSRF token
        $csrfToken = $this->request->post('csrf_token', '');

        if (!$this->csrf->validateToken((string) $csrfToken)) {
            $this->setError('Token de sécurité invalide. Veuillez réessayer.');
            $this->redirect('index.php?page=login');
        }

        $user = $this->auth->getUser();
        if (!$user || !isset($user['user_id'])) {
            $this->setError('Utilisateur non authentifié');
            $this->redirect('index.php?page=login');
        }

        $userId = $user['user_id'];
        $newEmail = trim((string) $this->request->post('new_email', ''));
        $password = (string) $this->request->post('password', '');

        // Validate email format
        if (!$this->privacyManager->isValidEmailFormat($newEmail)) {
            $this->setError('Format d\'email invalide.');
            $this->redirect('index.php?page=privacy&edit=email');
        }

        // Check if email already exists
        if ($this->userManager->emailExists($newEmail)) {
            $this->setError('Cette adresse email est déjà utilisée.');
            $this->redirect('index.php?page=privacy&edit=email');
        }

        // Get user with password for verification
        $userFull = $this->userManager->getUserById($userId);
        if (!$userFull) {
            $this->setError('Erreur lors de la récupération des données utilisateur.');
            $this->redirect('index.php?page=privacy');
        }

        // Verify password
        if (!$this->userManager->verifyPassword($password, $userFull['password'])) {
            $attempts = $this->privacyManager->trackEmailChangeAttempt($userId);

            // Check if max attempts reached
            if ($attempts >= $this->privacyManager->getMaxFailedAttempts()) {
                $this->handleMaxAttemptsReached($userId, 'email_change');
                return;
            }

            $remaining = $this->privacyManager->getMaxFailedAttempts() - $attempts;
            $this->setError("Mot de passe incorrect. Il vous reste {$remaining} tentative(s).");
            $this->redirect('index.php?page=privacy&edit=email');
        }

        // Password verified, generate and send code to NEW email
        $code = $this->privacyManager->generateVerificationCode();

        if (!$this->privacyManager->createEmailChangeToken($userId, $code, $newEmail)) {
            $this->setError('Erreur lors de la génération du code de vérification.');
            $this->redirect('index.php?page=privacy&edit=email');
        }

        // Store pending email in session
        $this->session->set('pending_email_change', $newEmail);

        // Send code to the NEW email address
        $userName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));

        if (!$this->mailer->sendEmailVerificationCodeEmail($newEmail, $userName, $code)) {
            $this->setError('Erreur lors de l\'envoi du code par email. Vérifiez que l\'adresse est valide.');
            $this->redirect('index.php?page=privacy&edit=email');
        }

        $this->privacyManager->resetEmailChangeAttempts($userId);
        $this->setSuccess('Un code de vérification a été envoyé à votre nouvelle adresse email.');
        $this->redirect('index.php?page=privacy&edit=email&step=verify');
    }

    /**
     * Resend email verification code
     *
     * Generates and sends a new verification code to the pending new email
     * if cooldown has passed and max resends not reached.
     *
     * @return void Redirects to email verification form
     */
    private function resendEmailCode(): void
    {
        // Validate CSRF token
        $csrfToken = $this->request->post('csrf_token', '');
        if (!$this->csrf->validateToken((string) $csrfToken)) {
            $this->setError('Jeton de sécurité invalide. Veuillez réessayer.');
            $this->redirect('index.php?page=privacy&edit=email&step=verify');
        }

        $user = $this->auth->getUser();
        if (!$user || !isset($user['user_id'])) {
            $this->setError('Utilisateur non authentifié');
            $this->redirect('index.php?page=login');
        }

        $userId = $user['user_id'];
        $pendingEmail = $this->session->get('pending_email_change');

        if (!$pendingEmail) {
            $this->setError('Aucune modification d\'email en cours.');
            $this->redirect('index.php?page=privacy');
        }

        // Check if can resend
        $resendStatus = $this->privacyManager->canResendEmailCode($userId);

        if (!$resendStatus['can_resend']) {
            if ($resendStatus['resend_count'] >= $this->privacyManager->getMaxResendAttempts()) {
                $this->setError('Nombre maximum de renvois atteint. Veuillez réessayer dans 30 minutes.');
                $this->privacyManager->deleteEmailChangeToken($userId);
                $this->session->remove('pending_email_change');
                $this->redirect('index.php?page=privacy');
            }
            $this->setError("Veuillez attendre {$resendStatus['wait_seconds']} seconde(s) avant de renvoyer le code.");
            $this->redirect('index.php?page=privacy&edit=email&step=verify');
        }

        // Generate new code
        $code = $this->privacyManager->generateVerificationCode();

        if (!$this->privacyManager->resendEmailCode($userId, $code)) {
            $this->setError('Erreur lors du renvoi du code.');
            $this->redirect('index.php?page=privacy&edit=email&step=verify');
        }

        // Send code to the pending email
        $userName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));

        if (!$this->mailer->sendEmailVerificationCodeEmail($pendingEmail, $userName, $code)) {
            $this->setError('Erreur lors de l\'envoi du code par email.');
            $this->redirect('index.php?page=privacy&edit=email&step=verify');
        }

        $newResendStatus = $this->privacyManager->canResendEmailCode($userId);
        $remaining = $this->privacyManager->getMaxResendAttempts() - $newResendStatus['resend_count'];

        $this->setSuccess("Nouveau code envoyé. Il vous reste {$remaining} renvoi(s) possible(s).");
        $this->redirect('index.php?page=privacy&edit=email&step=verify');
    }

    /**
     * Verify email code and update email
     *
     * Validates the 6-digit code sent to the new email address,
     * then updates the email. Blocks account after 10 failed attempts.
     *
     * @return void Redirects to privacy page with success or error message
     */
    private function verifyEmailCode(): void
    {
        // Validate CSRF token
        $csrfToken = $this->request->post('csrf_token', '');
        if (!$this->csrf->validateToken((string) $csrfToken)) {
            $this->setError('Jeton de sécurité invalide. Veuillez réessayer.');
            $this->redirect('index.php?page=privacy&edit=email&step=verify');
        }

        $user = $this->auth->getUser();
        if (!$user || !isset($user['user_id'])) {
            $this->setError('Utilisateur non authentifié');
            $this->redirect('index.php?page=login');
        }

        $userId = $user['user_id'];
        $code = trim((string) $this->request->post('verification_code', ''));
        $pendingEmail = $this->session->get('pending_email_change');

        if (!$pendingEmail) {
            $this->setError('Aucune modification d\'email en cours.');
            $this->redirect('index.php?page=privacy');
        }

        // Verify code
        $verification = $this->privacyManager->verifyEmailChangeToken($userId, $code);

        if (!$verification['valid']) {
            // Check if max attempts reached
            if ($verification['attempts'] >= $this->privacyManager->getMaxFailedAttempts()) {
                $this->session->remove('pending_email_change');
                $this->handleMaxAttemptsReached($userId, 'email_change');
                return;
            }

            $this->setError($verification['message']);
            $this->redirect('index.php?page=privacy&edit=email&step=verify');
        }

        // Code verified, update email
        if ($this->userManager->updateEmail($userId, $pendingEmail)) {
            $this->privacyManager->deleteEmailChangeToken($userId);
            $this->session->remove('pending_email_change');
            $this->session->set('user_email', $pendingEmail);
            $this->setSuccess('Adresse email mise à jour avec succès.');
            $this->redirect('index.php?page=privacy');
        } else {
            $this->setError('Erreur lors de la mise à jour de l\'email.');
            $this->redirect('index.php?page=privacy&edit=email&step=verify');
        }
    }

    /**
     * Request password change verification code
     *
     * Generates and sends a 6-digit verification code to the user's email.
     *
     * @return void Redirects to password verification form
     */
    private function requestPasswordCode(): void
    {
        // Validate CSRF token
        $csrfToken = $this->request->post('csrf_token', '');
        if (!$this->csrf->validateToken((string) $csrfToken)) {
            $this->setError('Jeton de sécurité invalide. Veuillez réessayer.');
            $this->redirect('index.php?page=privacy');
        }

        $user = $this->auth->getUser();
        if (!$user || !isset($user['user_id'])) {
            $this->setError('Utilisateur non authentifié');
            $this->redirect('index.php?page=login');
        }

        $userId = $user['user_id'];

        // Generate and store verification code
        $code = $this->privacyManager->generateVerificationCode();

        if (!$this->privacyManager->createPasswordChangeToken($userId, $code)) {
            $this->setError('Erreur lors de la génération du code de vérification.');
            $this->redirect('index.php?page=privacy');
        }

        // Send code by email
        $userName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
        $userEmail = $user['email'] ?? '';

        if (!$this->mailer->sendPasswordChangeCodeEmail($userEmail, $userName, $code)) {
            $this->setError('Erreur lors de l\'envoi du code par email.');
            $this->redirect('index.php?page=privacy');
        }

        $this->setSuccess('Un code de vérification a été envoyé à votre adresse email.');
        $this->redirect('index.php?page=privacy&edit=password&step=verify');
    }

    /**
     * Resend password change verification code
     *
     * Generates and sends a new verification code if cooldown has passed
     * and max resends not reached.
     *
     * @return void Redirects to password verification form
     */
    private function resendPasswordCode(): void
    {
        // Validate CSRF token
        $csrfToken = $this->request->post('csrf_token', '');
        if (!$this->csrf->validateToken((string) $csrfToken)) {
            $this->setError('Jeton de sécurité invalide. Veuillez réessayer.');
            $this->redirect('index.php?page=privacy&edit=password&step=verify');
        }

        $user = $this->auth->getUser();
        if (!$user || !isset($user['user_id'])) {
            $this->setError('Utilisateur non authentifié');
            $this->redirect('index.php?page=login');
        }

        $userId = $user['user_id'];

        // Check if can resend
        $resendStatus = $this->privacyManager->canResendPasswordCode($userId);

        if (!$resendStatus['can_resend']) {
            if ($resendStatus['resend_count'] >= $this->privacyManager->getMaxResendAttempts()) {
                $this->setError('Nombre maximum de renvois atteint. Veuillez réessayer dans 30 minutes.');
                $this->privacyManager->deletePasswordChangeToken($userId);
                $this->redirect('index.php?page=privacy');
            }
            $this->setError("Veuillez attendre {$resendStatus['wait_seconds']} seconde(s) avant de renvoyer le code.");
            $this->redirect('index.php?page=privacy&edit=password&step=verify');
        }

        // Generate new code
        $code = $this->privacyManager->generateVerificationCode();

        if (!$this->privacyManager->resendPasswordCode($userId, $code)) {
            $this->setError('Erreur lors du renvoi du code.');
            $this->redirect('index.php?page=privacy&edit=password&step=verify');
        }

        // Send code by email
        $userName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
        $userEmail = $user['email'] ?? '';

        if (!$this->mailer->sendPasswordChangeCodeEmail($userEmail, $userName, $code)) {
            $this->setError('Erreur lors de l\'envoi du code par email.');
            $this->redirect('index.php?page=privacy&edit=password&step=verify');
        }

        $newResendStatus = $this->privacyManager->canResendPasswordCode($userId);
        $remaining = $this->privacyManager->getMaxResendAttempts() - $newResendStatus['resend_count'];

        $this->setSuccess("Nouveau code envoyé. Il vous reste {$remaining} renvoi(s) possible(s).");
        $this->redirect('index.php?page=privacy&edit=password&step=verify');
    }

    /**
     * Verify password change code and update password
     *
     * Validates the 6-digit code and new password format,
     * ensures new password is different from current,
     * then updates the password. Blocks account after 10 failed attempts.
     *
     * @return void Redirects to privacy page with success or error message
     */
    private function verifyPasswordCode(): void
    {
        // Validate CSRF token
        $csrfToken = $this->request->post('csrf_token', '');
        if (!$this->csrf->validateToken((string) $csrfToken)) {
            $this->setError('Jeton de sécurité invalide. Veuillez réessayer.');
            $this->redirect('index.php?page=privacy&edit=password&step=verify');
        }

        $user = $this->auth->getUser();
        if (!$user || !isset($user['user_id'])) {
            $this->setError('Utilisateur non authentifié');
            $this->redirect('index.php?page=login');
        }

        $userId = $user['user_id'];
        $code = trim((string) $this->request->post('verification_code', ''));
        $newPassword = (string) $this->request->post('new_password', '');
        $confirmPassword = (string) $this->request->post('confirm_password', '');

        // Validate passwords match
        if ($newPassword !== $confirmPassword) {
            $this->setError('Les mots de passe ne correspondent pas.');
            $this->redirect('index.php?page=privacy&edit=password&step=verify');
        }

        // Validate password format
        $passwordValidation = $this->privacyManager->validatePasswordFormat($newPassword);
        if (!$passwordValidation['valid']) {
            $this->setError(implode(' ', $passwordValidation['errors']));
            $this->redirect('index.php?page=privacy&edit=password&step=verify');
        }

        // Check that new password is different from current
        $userFull = $this->userManager->getUserById($userId);
        if ($userFull && $this->userManager->verifyPassword($newPassword, $userFull['password'])) {
            $this->setError('Le nouveau mot de passe doit être différent de l\'ancien.');
            $this->redirect('index.php?page=privacy&edit=password&step=verify');
        }

        // Verify code
        $verification = $this->privacyManager->verifyPasswordChangeToken($userId, $code);

        if (!$verification['valid']) {
            // Check if max attempts reached
            if ($verification['attempts'] >= $this->privacyManager->getMaxFailedAttempts()) {
                $this->handleMaxAttemptsReached($userId, 'password_change');
                return;
            }

            $this->setError($verification['message']);
            $this->redirect('index.php?page=privacy&edit=password&step=verify');
        }

        // Code verified, update password
        if ($this->userManager->updatePassword($userId, $newPassword)) {
            $this->privacyManager->deletePasswordChangeToken($userId);
            $this->setSuccess('Mot de passe mis à jour avec succès.');
            $this->redirect('index.php?page=privacy');
        } else {
            $this->setError('Erreur lors de la mise à jour du mot de passe.');
            $this->redirect('index.php?page=privacy&edit=password&step=verify');
        }
    }

    /**
     * Handle max failed attempts reached
     *
     * Blocks the user account, sends security alert email,
     * and logs out the user.
     *
     * @param int $userId The user ID
     * @param string $alertType Type of alert (email_change or password_change)
     * @return void Redirects to login page
     */
    private function handleMaxAttemptsReached(int $userId, string $alertType): void
    {
        $user = $this->auth->getUser();

        // Block user
        $this->privacyManager->blockUser($userId);

        // Reset counters
        if ($alertType === 'email_change') {
            $this->privacyManager->resetEmailChangeAttempts($userId);
            $this->privacyManager->deleteEmailChangeToken($userId);
        } else {
            $this->privacyManager->deletePasswordChangeToken($userId);
        }

        // Send security alert email
        $userName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
        $userEmail = $user['email'] ?? '';
        $this->mailer->sendSecurityAlertEmail($userEmail, $userName, $alertType);

        // Logout user
        $this->auth->logout();

        $this->setError('Trop de tentatives échouées. Votre compte a été temporairement bloqué. Un email de sécurité vous a été envoyé.');
        $this->redirect('index.php?page=login');
    }
}
