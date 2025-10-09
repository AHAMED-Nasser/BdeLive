<?php

declare(strict_types=1);

class ResetPasswordController {
    
    public function __construct() {
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
    
    private function loadView(string $viewName): void {
        require_once __DIR__ . '/../views/' . $viewName . '.php';
    }
}
