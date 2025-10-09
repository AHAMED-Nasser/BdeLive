<?php

declare(strict_types=1);

/**
 * Verify Token Controller
 * 
 * Handles the token verification step in the password reset workflow.
 * Validates the token sent to the user's email and ensures it's not
 * expired or already used. Second step in password recovery.
 * 
 * @package BdeLive\Controllers
 * @author Mohamed-Amine Boudhib, Thomas Palot, Amin Helali, Willem Chetioui, Nasser Ahamed, Romain Cantor
 * @version 1.0.0
 */
class VerifyTokenController {
    
    /**
     * Handle token verification page requests
     * 
     * Ensures user came from forgot password flow, displays token input
     * form on GET, or processes token verification on POST.
     * 
     * @return void
     */
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
    
    /**
     * Process token verification
     * 
     * Validates the submitted token against the database. Checks if token
     * is valid, not expired, and not already used. Redirects to password
     * reset page on success.
     * 
     * @return void
     */
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
