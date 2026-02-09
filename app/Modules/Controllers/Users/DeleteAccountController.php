<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Users;

use App\Modules\Controllers\AuthenticatedController;
use Exception;
use App\Modules\Models\Users\UserManager;

/**
 * Delete Account Controller - User Account Deletion
 *
 * Handles user account deletion with CSRF protection.
 * Only accessible to authenticated users who can delete their own account.
 *
 * Features:
 * - Display account deletion confirmation form
 * - CSRF token validation
 * - Permanent account deletion
 * - Automatic logout after deletion
 * - Success/error feedback with flash messages
 *
 * @package BdeLive\Controllers\Users
 * @version 1.0.0
 * @author BdeLive Team
 *
 * @see AuthenticatedController For authentication requirements
 * @see UserManager For database operations
 */
class DeleteAccountController extends AuthenticatedController
{
    /**
     * User manager instance for database operations
     *
     * @var UserManager
     */
    private UserManager $userManager;

    /**
     * Constructor - Handle account deletion form and submission
     *
     * GET request: Displays the account deletion confirmation page
     * POST request: Processes the account deletion
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->userManager = new UserManager();

        if ($this->request->isPost()) {
            $this->handleDelete();
        } else {
            $this->render('users/deleteAccountView');
        }
    }

    /**
     * Process account deletion
     *
     * Validates CSRF token, validates email confirmation, deletes the user account from database,
     * logs out the user, and redirects to home page with success message.
     *
     * @return void Redirects to home page or delete_account page on error
     */
    private function handleDelete(): void
    {
        // Get user from auth (already authenticated by parent)
        $user = $this->auth->getUser();
        if (!$user || !isset($user['user_id'])) {
            $this->setError('Vous devez être connecté pour supprimer votre compte.');
            $this->redirect('index.php?page=login');
        }

        $userId = (int) $user['user_id'];
        $userEmail = strtolower(trim($user['email'] ?? ''));

        // ====================================================================
        // Code CSRF to be corrected
        // ====================================================================
        // CSRF validation temporarily disabled
        // Problem identified: CSRF token not retrieved correctly with multipart/form-data
        // when uploading files. Permanent solution to be implemented in S4
        // ====================================================================

        // Temporary flag to disable CSRF validation

        $skipCsrfValidation = false; // To be set to false after the problem has been corrected.

        // Validate CSRF token
        /** @phpstan-ignore-next-line */
        if (!$skipCsrfValidation) {
            $csrfToken = $this->request->post('csrf_token', '');

            if (!$this->csrf->validateToken((string) $csrfToken)) {
                $this->setError('Token de sécurité invalide. Veuillez réessayer.');
                $this->redirect('index.php?page=delete_account');
            }
        }

        // Validate email confirmation
        $confirmEmail = strtolower(trim($this->request->post('confirm_email', '')));
        if (empty($confirmEmail)) {
            $this->setError('Veuillez confirmer votre email en le tapant manuellement.');
            $this->redirect('index.php?page=delete_account');
        }

        if ($confirmEmail !== $userEmail) {
            $this->setError('L\'email saisi ne correspond pas à votre email actuel. Veuillez réessayer.');
            $this->redirect('index.php?page=delete_account');
        }

        try {
            $deleted = $this->userManager->deleteUser($userId);

            if ($deleted) {
                // Logout user (destroys session)
                $this->auth->logout();

                // Set success message for after logout
                $this->setSuccess('Votre compte a bien été supprimé !');
                $this->session->flash('show_register_link', true);
                $this->redirect('index.php?page=home');
            } else {
                $this->setError('Impossible de supprimer le compte.');
                $this->redirect('index.php?page=delete_account');
            }
        } catch (Exception $e) {
            error_log('DeleteAccountController::handleDelete - ' . $e->getMessage());
            $this->setError('Erreur serveur. Contactez un administrateur.');
            $this->redirect('index.php?page=delete_account');
        }
    }
}
