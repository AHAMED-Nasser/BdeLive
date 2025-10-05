<?php
declare(strict_types=1);

require_once __DIR__ . '/../../include/AuthController.php';


class RegisterController
{
    private AuthController $authController;

    public function __construct()
    {
        $this->authController = new AuthController();
        
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ok'])) {
            $this->handleRegistration();
        } else {
            $this->loadView('registerPageView');
        }
    }

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

    private function loadView(string $viewName): void
    {
        require_once __DIR__ . '/../views/' . $viewName . '.php';
    }
}
