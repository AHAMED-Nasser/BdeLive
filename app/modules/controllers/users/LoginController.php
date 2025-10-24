<?php

declare(strict_types=1);


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
class LoginController
{
    /**
     * Authentication controller instance
     *
     * @var AuthController
     */
    private AuthController $authController;

    /**
     * Constructor - Initialize the LoginController
     *
     * Creates a new AuthController instance for handling authentication operations.
     */
    public function __construct()
    {
        $this->authController = new AuthController();
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ok'])) {
            $this->processLogin();
        } else {
            $this->loadView('loginPageView');
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
        // Start session for messages
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Validate CSRF token
        if (! isset($_POST['csrf_token']) || ! validateCsrfToken($_POST['csrf_token'])) {
            $_SESSION['error'] = 'Jeton de sécurité invalide. Veuillez réessayer.';
            $this->loadView('loginPageView');

            return;
        }

        // Get and sanitize inputs
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $mdp = isset($_POST['pwd']) ? $_POST['pwd'] : '';

        // Validation
        if (empty($email) || empty($mdp)) {
            $_SESSION['error'] = 'Veuillez remplir tous les champs';
            $this->loadView('loginPageView');

            return;
        }

        // Validate email format
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Format d\'email invalide';
            $this->loadView('loginPageView');

            return;
        }

        // Admin authentification
        $adminEmail = defined('ADMIN_EMAIL') ? ADMIN_EMAIL : 'admin@example.com';
        $adminPwd   = defined('ADMIN_PWD') ? ADMIN_PWD : 'motdepasse123';
        
        if ($email === $adminEmail && $mdp === $adminPwd) {
            // Store user information in session
            $_SESSION['user_id'] = 0;
            $_SESSION['last_name'] = 'Admin';
            $_SESSION['first_name'] = 'Me';
            $_SESSION['user_status'] = 'BDE';
            $_SESSION['email'] = $adminEmail;
            $_SESSION['suid'] = session_id();

            // Login admin success
            $_SESSION['success'] = 'Connexion réussie ! Bienvenue administrateur !';
            header('Location: index.php?page=home');
            exit;
        }


        // Attempt login
        if ($this->authController->login($email, $mdp)) {
            // Login successful
            $_SESSION['success'] = 'Connexion réussie ! Bienvenue ' . htmlspecialchars($this->authController->getCurrentUserFullName()) . ' !';
            header('Location: index.php?page=home');
            exit;
        } else {
            // Login failed
            $_SESSION['error'] = 'Email ou mot de passe incorrect';
            $this->loadView('loginPageView');
        }
    }

    /**
     * Load a view file
     *
     * Helper method to include and render a view template.
     *
     * @param string $view The name of the view file to load (without .php extension)
     * @return void
     */
    private function loadView(string $view): void
    {
        require_once __DIR__ . '/../../views/users/' . $view . '.php';
    }
}
