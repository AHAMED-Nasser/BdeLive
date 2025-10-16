<?php

declare(strict_types=1);

/**
 * Forgot Password Controller
 * 
 * Handles the password reset request process. Validates user email,
 * generates a secure reset token, and sends it via email.
 * First step in the password recovery workflow.
 * 
 * @package BdeLive\Controllers
 * @author Mohamed-Amine Boudhib, Thomas Palot, Amin Helali, Willem Chetioui, Nasser Ahamed, Romain Cantor
 * @version 1.0.0
 */
class ForgotPasswordController {
    
    /**
     * Handle forgot password page requests
     * 
     * Displays the forgot password form on GET requests, or processes
     * the email submission on POST requests.
     * 
     * @return void
     */
    public function __construct() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->sendResetEmail();
            return;
        }
        
        $this->loadView('forgotPasswordView');
    }
    
    /**
     * Process password reset email request
     * 
     * Validates the provided email, generates a reset token, and sends
     * it to the user's email address. Redirects to token verification
     * page on success.
     * 
     * @return void
     */
    private function sendResetEmail(): void {
        $email = trim($_POST['email'] ?? '');
        // Ask the user to enter their email
        if (empty($email)) {
            $_SESSION['error'] = 'Veuillez saisir votre adresse email';
            header('Location: index.php?page=forgot_password');
            exit;
        }
        
        try {
            // Get the user by email
            require_once __DIR__ . '/../models/PasswordReset.php';
            $passwordReset = new PasswordReset();
            
            $user = $passwordReset->getUserByEmail($email);
            
            // If the user is not found, show an error message
            if (!$user) {
                $_SESSION['error'] = 'Aucun compte n\'est associé à cette adresse email';
                header('Location: index.php?page=forgot_password');
                exit;
            }

            // If the user is found, create a token
            $token = $passwordReset->createToken($user['user_id']);

            // If the token is not created, show an error message
            if (!$token) {
                $_SESSION['error'] = 'Erreur lors de la génération du code';
                header('Location: index.php?page=forgot_password');
                exit;
            }
            
            // Send the email with PHPMailer
            require_once __DIR__ . '/../../config/mailer.php';
            $mailer = new Mailer();
            
            $emailSent = $mailer->sendPasswordResetEmail(
                $user['email'],
                $user['first_name'] . ' ' . $user['last_name'],
                $token
            );
            // If the email is sent (with the token in it), show a success message
            if ($emailSent) {
                $_SESSION['reset_email'] = $email;
                $_SESSION['success'] = 'Un code de vérification a été envoyé à votre adresse email';
                header('Location: index.php?page=verify_token');
                // Else, show an error message
            } else {
                $_SESSION['error'] = 'Erreur lors de l\'envoi de l\'email. Veuillez réessayer';
                header('Location: index.php?page=forgot_password');
            }
            
        } catch (Exception $e) {
            // If another error occurs, show an error message
            $_SESSION['error'] = 'Une erreur est survenue';
            header('Location: index.php?page=forgot_password');
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

