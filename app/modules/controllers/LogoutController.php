<?php

declare(strict_types=1);

class LogoutController {
    
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

