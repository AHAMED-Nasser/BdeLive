<?php
declare(strict_types=1);

require_once __DIR__ . '/../../include/AuthController.php';


class LoginController
{
    private AuthController $authController;

    public function __construct()
    {
        $this->authController = new AuthController();
    }

    public function index(): void
    {
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ok'])) {
            $this->processLogin();
        } else {
            $this->loadView('loginPageView');
        }
    }


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

    private function loadView(string $view): void
    {
        require_once __DIR__ . '/../views/' . $view . '.php';
    }
}

