<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Users;

use App\Modules\Controllers\AuthenticatedController;

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
class LogoutController extends AuthenticatedController
{
    /**
     * Process user logout
     *
     * Destroys the current user session, clears all session data and cookies,
     * then redirects to the home page with a success message.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        
        // Use AuthManager to handle logout
        $this->auth->logout();
        
        // Set success message
        $this->setSuccess('Vous avez été déconnecté avec succès.');
        
        // Redirect to home
        $this->redirect('index.php?page=home');
    }
}
