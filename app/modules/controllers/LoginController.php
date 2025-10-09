<?php
declare(strict_types=1);

require_once __DIR__ . '/../../include/AuthController.php';

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
    }

    /**
     * Handle login page requests
     * 
     * Displays the login form on GET requests, or processes the login
     * attempt on POST requests.
     * 
     * @return void
     */
    public function index(): void
    {
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
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Format d\'email invalide';
            $this->loadView('loginPageView');
            return;
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
        require_once __DIR__ . '/../views/' . $view . '.php';
    }
}

