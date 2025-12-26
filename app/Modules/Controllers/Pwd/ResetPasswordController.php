<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Pwd;

use App\Modules\Controllers\DefaultController;
use Exception;
use App\Modules\Models\Pwd\PasswordReset;

/**
 * Reset Password Controller
 *
 * Handles the final step of password reset workflow. Allows users to
 * enter a new password after successful token verification.
 * Validates password requirements and updates the user's password.
 *
 * @package BdeLive\Controllers
 * @author Mohamed-Amine Boudhib, Thomas Palot, Amin Helali, Willem Chetioui, Nasser Ahamed, Romain Cantor
 * @version 1.0.0
 */
class ResetPasswordController extends DefaultController
{
    /**
     * Handle password reset page requests
     *
     * Ensures user has valid reset token and user ID in session,
     * displays password reset form on GET, or processes password
     * change on POST.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        if (!$this->session->has('reset_token') || !$this->session->has('reset_user_id')) {
            $this->redirect('index.php?page=forgot_password');
        }

        if ($this->request->isPost()) {
            $this->resetPassword();
            return;
        }

        $this->render('pwd/resetPasswordView');
    }

    /**
     * Process password reset
     *
     * Validates new password (minimum length, confirmation match),
     * updates the password in database, marks token as used, and
     * redirects to login page on success.
     *
     * @return void
     */
    private function resetPassword(): void
    {
        // Validate CSRF token
        $csrfToken = $this->request->post('csrf_token', '');
        if (!$this->csrf->validateToken((string) $csrfToken)) {
            $this->setError('Jeton de sécurité invalide. Veuillez réessayer.');
            $this->redirect('index.php?page=reset_password');
        }

        $password = (string) $this->request->post('password', '');
        $confirm_password = (string) $this->request->post('confirm_password', '');

        // Show an error message if the password or the confirm password is empty
        if (empty($password) || empty($confirm_password)) {
            $this->setError('Veuillez remplir tous les champs');
            $this->redirect('index.php?page=reset_password');
        }
        // Show an error message if the password is less than 6 characters
        if (strlen($password) < 6) {
            $this->setError('Le mot de passe doit contenir au moins 6 caractères');
            $this->redirect('index.php?page=reset_password');
        }
        // Show an error message if the password and the confirm password do not match
        if ($password !== $confirm_password) {
            $this->setError('Les mots de passe ne correspondent pas');
            $this->redirect('index.php?page=reset_password');
        }

        try {
            // Update the password in the database
            $passwordReset = new PasswordReset();
            $resetUserId = $this->session->get('reset_user_id');
            $resetToken = $this->session->get('reset_token');

            $updated = $passwordReset->updatePassword($resetUserId, $password);

            if ($updated) {
                // Mark the token as used (used = 1)
                $passwordReset->markTokenAsUsed($resetToken);
                // Unset the session variables
                $this->session->remove('reset_token');
                $this->session->remove('reset_user_id');
                $this->session->remove('reset_email');
                // Show a success message
                $this->setSuccess(
                    'Votre mot de passe a été réinitialisé avec succès. ' .
                    'Vous pouvez maintenant vous connecter'
                );
                $this->redirect('index.php?page=login');
            } else {
                // If the password is not updated, show an error message
                $this->setError('Erreur lors de la mise à jour du mot de passe');
                $this->redirect('index.php?page=reset_password');
            }
        } catch (Exception $e) {
            // If another error occurs, show an error message
            $this->setError('Une erreur est survenue');
            $this->redirect('index.php?page=reset_password');
        }
    }
}
