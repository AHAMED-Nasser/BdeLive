<?php

declare(strict_types=1);

class VerifyTokenController {
    
    public function index(): void {
        if (!isset($_SESSION['reset_email'])) {
            header('Location: index.php?page=forgot_password');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyToken();
            return;
        }
        
        $this->loadView('verifyTokenView');
    }
    
    private function verifyToken(): void {
        $token = trim($_POST['token'] ?? '');
        
        if (empty($token)) {
            $_SESSION['error'] = 'Veuillez saisir le code de vérification';
            header('Location: index.php?page=verify_token');
            exit;
        }
        
        try {
            require_once __DIR__ . '/../models/PasswordReset.php';
            $passwordReset = new PasswordReset();
            
            $verification = $passwordReset->verifyToken($token);
            
            if (!$verification['valid']) {
                $_SESSION['error'] = $verification['message'];
                header('Location: index.php?page=verify_token');
                exit;
            }
            
            $_SESSION['reset_token'] = $token;
            $_SESSION['reset_user_id'] = $verification['utilisateur_id'];
            
            header('Location: index.php?page=reset_password');
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'Une erreur est survenue lors de la vérification';
            header('Location: index.php?page=verify_token');
        }
        exit;
    }
    
    private function loadView(string $viewName): void {
        require_once __DIR__ . '/../views/' . $viewName . '.php';
    }
}

