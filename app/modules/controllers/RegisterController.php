<?php
declare(strict_types=1);

require_once __DIR__ . '/../../include/AuthController.php';

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
class RegisterController
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
        $this->authController = new AuthController();
    }

    /**
     * Handle registration page requests
     * 
     * Displays the registration form on GET requests, or processes the
     * registration attempt on POST requests.
     * 
     * @return void
     */
    public function index(): void
    {
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ok'])) {
            $this->handleRegistration();
        } else {
            $this->loadView('registerPageView');
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
        // Start session for error messages
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Validate and sanitize inputs
        $nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';
        $prenom = isset($_POST['prenom']) ? trim($_POST['prenom']) : '';
        $classeAnnee = isset($_POST['classe_annee']) ? trim($_POST['classe_annee']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $mdp = isset($_POST['password']) ? $_POST['password'] : '';

        // Validation
        if (empty($nom) || empty($prenom) || empty($classeAnnee) || empty($email) || empty($mdp)) {
            $_SESSION['error'] = 'Tous les champs sont obligatoires';
            $this->loadView('registerPageView');
            return;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Format d\'email invalide';
            $this->loadView('registerPageView');
            return;
        }

        // Validate password length
        if (strlen($mdp) < 6) {
            $_SESSION['error'] = 'Le mot de passe doit contenir au moins 6 caractères';
            $this->loadView('registerPageView');
            return;
        }

        // Validate classe_annee
        if (!in_array($classeAnnee, ['1', '2', '3'])) {
            $_SESSION['error'] = 'Année de classe invalide';
            $this->loadView('registerPageView');
            return;
        }

        // Attempt registration
        $userId = $this->authController->register($nom, $prenom, $classeAnnee, $email, $mdp);

        if ($userId) {
            // Registration successful - auto login
            if ($this->authController->login($email, $mdp)) {
                $_SESSION['success'] = 'Inscription réussie ! Bienvenue ' . htmlspecialchars($prenom) . ' !';
                header('Location: index.php?page=home');
                exit;
            } else {
                // Registration ok but login failed (shouldn't happen)
                $_SESSION['success'] = 'Inscription réussie ! Veuillez vous connecter.';
                header('Location: index.php?page=login');
                exit;
            }
        } else {
            $_SESSION['error'] = 'Cette adresse email est déjà utilisée';
            $this->loadView('registerPageView');
        }
    }

    /**
     * Load a view file
     * 
     * Helper method to include and render a view template.
     * 
     * @param string $viewName The name of the view file to load (without .php extension)
     * @return void
     */
    private function loadView(string $viewName): void
    {
        require_once __DIR__ . '/../views/' . $viewName . '.php';
    }
}
