<?php

declare(strict_types=1);

class ForgotPasswordController {
    
    public function index(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->sendResetEmail();
            return;
        }
        
        $this->loadView('forgotPasswordView');
    }
    
    private function sendResetEmail(): void {
        $email = trim($_POST['email'] ?? '');
        
        if (empty($email)) {
            $_SESSION['error'] = 'Veuillez saisir votre adresse email';
            header('Location: index.php?page=forgot_password');
            exit;
        }
        
        try {
            require_once __DIR__ . '/../models/PasswordReset.php';
            $passwordReset = new PasswordReset();
            
            $user = $passwordReset->getUserByEmail($email);
            
            if (!$user) {
                $_SESSION['error'] = 'Aucun compte n\'est associé à cette adresse email';
                header('Location: index.php?page=forgot_password');
                exit;
            }
            
            $token = $passwordReset->createToken($user['utilisateur_id']);
            
            if (!$token) {
                $_SESSION['error'] = 'Erreur lors de la génération du code';
                header('Location: index.php?page=forgot_password');
                exit;
            }
            
            // Envoi de l'email avec PHPMailer
            require_once __DIR__ . '/../../config/mailer.php';
            $mailer = new Mailer();
            
            $emailSent = $mailer->sendPasswordResetEmail(
                $user['email'],
                $user['prenom'] . ' ' . $user['nom'],
                $token
            );
            
            if ($emailSent) {
                $_SESSION['reset_email'] = $email;
                $_SESSION['success'] = 'Un code de vérification a été envoyé à votre adresse email';
                header('Location: index.php?page=verify_token');
            } else {
                $_SESSION['error'] = 'Erreur lors de l\'envoi de l\'email. Veuillez réessayer';
                header('Location: index.php?page=forgot_password');
            }
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'Une erreur est survenue';
            header('Location: index.php?page=forgot_password');
        }
        exit;
    }
    
    private function loadView(string $viewName): void {
        require_once __DIR__ . '/../views/' . $viewName . '.php';
    }
}

