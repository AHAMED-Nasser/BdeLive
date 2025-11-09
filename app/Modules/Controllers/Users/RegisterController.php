<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Users;

use App\Modules\Controllers\DefaultController;
use App\Modules\Controllers\Users\AuthController;

/**
 * Register Controller
 *
 * Handles user registration operations including form display, input validation,
 * account creation, and automatic login after successful registration.
 * Works with AuthController to create new user accounts.
 *
 * @package BdeLive\Controllers
 * @author Mohamed-Amine Boudhib, Thomas Palot, Amin Helali, Willem Chetioui, Nasser Ahamed, Romain Cantor
 * @version 1.0.0
 */
class RegisterController extends DefaultController
{
    /**
     * Authentication controller instance
     *
     * @var AuthController
     */
    private AuthController $authController;

    /**
     * Constructor - Initialize the RegisterController
     *
     * Creates a new AuthController instance for handling registration operations.
     */
    public function __construct()
    {
        parent::__construct();

        $this->authController = new AuthController();

        // Handle form submission
        if ($this->request->isPost() && $this->request->post('ok') !== null) {
            $this->handleRegistration();
        } else {
            $this->render('users/registerPageView');
        }
    }

    /**
     * Process the registration form submission
     *
     * Validates all user input (required fields, email format, password strength,
     * class year), creates the user account via AuthController, and automatically
     * logs in the new user on success.
     *
     * @return void
     */
    private function handleRegistration(): void
    {
        // Validate CSRF token
        $csrfToken = $this->request->post('csrf_token', '');
        if (!$this->csrf->validateToken((string) $csrfToken)) {
            $this->setError('Jeton de sécurité invalide. Veuillez réessayer.');
            $this->render('users/registerPageView');
            return;
        }

        // Validate and sanitize inputs
        $last_name = trim((string) $this->request->post('last_name', ''));
        $first_name = trim((string) $this->request->post('first_name', ''));
        $user_status = trim((string) $this->request->post('user_status', ''));
        $email = trim((string) $this->request->post('email', ''));
        $pwd = (string) $this->request->post('password', '');

        // Validation
        if (empty($last_name) || empty($first_name) || empty($user_status) || empty($email) || empty($pwd)) {
            $this->setError('Tous les champs sont obligatoires');
            $this->render('users/registerPageView');
            return;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setError('Format d\'email invalide');
            $this->render('users/registerPageView');
            return;
        }

        // Validate password length
        if (strlen($pwd) < 6) {
            $this->setError('Le mot de passe doit contenir au moins 6 caractères');
            $this->render('users/registerPageView');
            return;
        }

        // Validate user_status
        if (!in_array($user_status, ['BUT 1', 'BUT 2', 'BUT 3', 'Personnel Enseignant'])) {
            $this->setError('Statut d\'utilisateur invalide');
            $this->render('users/registerPageView');
            return;
        }

        // Attempt registration
        $userId = $this->authController->register($last_name, $first_name, $user_status, $email, $pwd);

        if ($userId) {
            // Registration successful - auto login
            if ($this->authController->login($email, $pwd)) {
                $this->setSuccess('Inscription réussie ! Bienvenue ' . htmlspecialchars($first_name) . ' !');
                $this->redirect('index.php?page=home');
            } else {
                // Registration ok but login failed (shouldn't happen)
                $this->setSuccess('Inscription réussie ! Veuillez vous connecter.');
                $this->redirect('index.php?page=login');
            }
            // If the email is already used, show an error message
        } else {
            $this->setError('Cette adresse email est déjà utilisée');
            $this->render('users/registerPageView');
        }
    }
}
