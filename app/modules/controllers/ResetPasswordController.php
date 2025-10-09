<?php

declare(strict_types=1);

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
class ResetPasswordController {
    
    /**
     * Handle password reset page requests
     * 
     * Ensures user has valid reset token and user ID in session,
     * displays password reset form on GET, or processes password
     * change on POST.
     * 
     * @return void
     */
    public function index(): void {
        if (!isset($_SESSION['reset_token']) || !isset($_SESSION['reset_user_id'])) {
            header('Location: index.php?page=forgot_password');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->resetPassword();
            return;
        }
        
        $this->loadView('resetPasswordView');
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
    private function resetPassword(): void {
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($password) || empty($confirm_password)) {
            $_SESSION['error'] = 'Veuillez remplir tous les champs';
            header('Location: index.php?page=reset_password');
            exit;
        }
        
        if (strlen($password) < 6) {
            $_SESSION['error'] = 'Le mot de passe doit contenir au moins 6 caractères';
            header('Location: index.php?page=reset_password');
            exit;
        }
        
        if ($password !== $confirm_password) {
            $_SESSION['error'] = 'Les mots de passe ne correspondent pas';
            header('Location: index.php?page=reset_password');
            exit;
        }
        
        try {
            require_once __DIR__ . '/../models/PasswordReset.php';
            $passwordReset = new PasswordReset();
            
            $updated = $passwordReset->updatePassword($_SESSION['reset_user_id'], $password);
            
            if ($updated) {
                // Marquer le token comme utilisé (utilise = 1)
                $passwordReset->markTokenAsUsed($_SESSION['reset_token']);
                
                unset($_SESSION['reset_token']);
                unset($_SESSION['reset_user_id']);
                unset($_SESSION['reset_email']);
                
                $_SESSION['success'] = 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter';
                header('Location: index.php?page=login');
            } else {
                $_SESSION['error'] = 'Erreur lors de la mise à jour du mot de passe';
                header('Location: index.php?page=reset_password');
            }
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'Une erreur est survenue';
            header('Location: index.php?page=reset_password');
        }
        exit;
    }
    
    /**
     * Load a view file
     * 
     * Helper method to include and render a view template.
     * 
     * @param string $viewName The name of the view file to load (without .php extension)
     * @return void
     */
    private function loadView(string $viewName): void {
        require_once __DIR__ . '/../views/' . $viewName . '.php';
    }
}
