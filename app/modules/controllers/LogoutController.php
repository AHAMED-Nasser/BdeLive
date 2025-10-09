<?php

declare(strict_types=1);

/**
 * Logout Controller
 * 
 * Handles user logout operations, including session destruction
 * and cleanup of session cookies. Redirects users to the home page
 * after successful logout.
 * 
 * @package BdeLive\Controllers
 * @author Mohamed-Amine Boudhib, Thomas Palot, Amin Helali, Willem Chetioui, Nasser Ahamed, Romain Cantor
 * @version 1.0.0
 */
class LogoutController {
    
    /**
     * Process user logout
     * 
     * Destroys the current user session, clears all session data and cookies,
     * then redirects to the home page with a success message.
     * 
     * @return void
     */
    public function index(): void {
        $_SESSION = array();
        
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time()-42000, '/');
        }
        
        session_destroy();
        
        session_start();
        $_SESSION['success'] = 'Vous avez été déconnecté avec succès.';
        
        header('Location: index.php?page=home');
        exit;
    }
}

