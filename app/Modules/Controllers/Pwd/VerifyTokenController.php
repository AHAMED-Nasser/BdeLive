<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Pwd;

use App\Modules\Controllers\DefaultController;
use Exception;
use App\Modules\Models\Pwd\PasswordReset;

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
class VerifyTokenController extends DefaultController
{
    /**
     * Handle token verification page requests
     *
     * Ensures user came from forgot password flow, displays token input
     * form on GET, or processes token verification on POST.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        
        if (!$this->session->has('reset_email')) {
            $this->redirect('index.php?page=forgot_password');
        }

        if ($this->request->isPost()) {
            $this->verifyToken();
            return;
        }

        $this->render('pwd/verifyTokenView');
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
    private function verifyToken(): void
    {
        // Validate CSRF token
        $csrfToken = $this->request->post('csrf_token', '');
        if (!$this->csrf->validateToken((string) $csrfToken)) {
            $this->setError('Jeton de sécurité invalide. Veuillez réessayer.');
            $this->redirect('index.php?page=verify_token');
        }

        $token = trim((string) $this->request->post('token', ''));
        // Show an error message if the token is empty
        if (empty($token)) {
            $this->setError('Veuillez saisir le code de vérification');
            $this->redirect('index.php?page=verify_token');
        }

        // Verify the token
        try {
            $passwordReset = new PasswordReset();
            // Verify the token
            $verification = $passwordReset->verifyToken($token);
            // Show an error message if the token is not valid
            if ($verification['valid'] === false) {
                $this->setError($verification['message']);
                $this->redirect('index.php?page=verify_token');
            }
            // Set the session variables
            $this->session->set('reset_token', $token);
            $this->session->set('reset_user_id', $verification['user_id']);
            // Redirect to the reset password page
            $this->redirect('index.php?page=reset_password');
        } catch (Exception $e) {
            // If another error occurs, show an error message
            $this->setError('Une erreur est survenue lors de la vérification');
            $this->redirect('index.php?page=verify_token');
        }
    }
}
