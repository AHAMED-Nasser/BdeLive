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
     * Validates CSRF token, deletes the user account from database,
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

        // Validate CSRF token
        $csrfToken = $this->request->post('csrf_token', '');
        if (!$this->csrf->validateToken((string) $csrfToken)) {
            $this->setError('Jeton invalide. Veuillez réessayer.');
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
