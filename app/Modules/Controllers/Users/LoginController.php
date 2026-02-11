<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Users;

use App\Modules\Controllers\DefaultController;
use App\Modules\Controllers\Users\AuthController;

/**
 * Login Controller
 *
 * Handles user login operations including form display, input validation,
 * and authentication processing. Works with AuthController to verify
 * user credentials and establish authenticated sessions.
 *
 * @package BdeLive\Controllers
 * @author Mohamed-Amine Boudhib, Thomas Palot, Amin Helali, Willem Chetioui, Nasser Ahamed, Romain Cantor
 * @version 1.0.0
 */
class LoginController extends DefaultController
{
    /**
     * Constructor - Initialize the LoginController
     *
     * Displays the login form or processes the login submission.
     */
    public function __construct()
    {
        parent::__construct();

        // Handle form submission
        if ($this->request->isPost() && $this->request->post('ok') !== null) {
            $this->processLogin();
        } else {
            $this->render('users/loginPageView');
        }
    }

    /**
     * Process the login form submission
     *
     * Validates user input (email format, required fields), attempts authentication
     * via AuthController, and handles success/failure scenarios with appropriate
     * redirects and messages.
     *
     * @return void
     */
    private function processLogin(): void
    {
        // Validate CSRF token
        $csrfToken = $this->request->post('csrf_token', '');

        if (!$this->csrf->validateToken((string) $csrfToken)) {
            $this->setError('Token de sécurité invalide. Veuillez réessayer.');
            $this->redirect('users/loginPageView');
        }

        // Get and sanitize inputs
        $email = trim((string) $this->request->post('email', ''));
        $mdp = (string) $this->request->post('password', '');

        // Validation
        if (empty($email) || empty($mdp)) {
            $this->setError('Veuillez remplir tous les champs');
            $this->render('users/loginPageView');
            return;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setError('Format d\'email invalide');
            $this->render('users/loginPageView');
            return;
        }

        // Attempt login with old system to verify credentials
        $userManager = new \App\Modules\Models\Users\UserManager();
        $user = $userManager->findUserByEmail($email);

        if (!$user || !$userManager->verifyPassword($mdp, $user['password'])) {
            // Login failed
            $this->setError('Email ou mot de passe incorrect');
            $this->render('users/loginPageView');
            return;
        }

        // Vérifier si le compte a été clôturé (soft delete)
        if (!empty($user['deleted_at'])) {
            $this->setError('Ce compte a été clôturé. Contactez le support pour le réactiver.');
            $this->render('users/loginPageView');
            return;
        }

        $isBlocked = (int) $user['is_blocked'];
        // Verify if user blocked or not
        if ($isBlocked === 1) {
            $this->setError('Votre compte a été suspendu par l\'administrateur.');
            $this->render('users/loginPageView');
            return;
        }

        // Vérifier si l'email est vérifié
        if ((int)$user['is_verified'] === 0) {
            $this->setError(
                'Votre adresse email n\'a pas encore été vérifiée. ' .
                'Veuillez vérifier votre boîte de réception et cliquer sur le lien de vérification dans l\'email que nous vous avons envoyé.'
            );
            $this->render('users/loginPageView');
            return;
        }

        // Login successful - Use new AuthManager to store session
        $this->auth->login(
            (int) $user['user_id'],
            $user['user_status'],
            $user['email'],
            $user['role'],
            $isBlocked,
            $user['first_name'],
            $user['last_name']
        );

        $userName = trim($user['first_name'] . ' ' . $user['last_name']);
        $this->setSuccess('Connexion réussie ! Bienvenue ' . htmlspecialchars($userName) . ' !');
        $this->redirect('index.php?page=home');
    }
}
