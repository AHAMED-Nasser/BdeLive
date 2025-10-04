<?php

class LoginController {
    
    public function index() {
        // Si c'est une requête POST, traiter la connexion
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->login();
            return;
        }
        
        // Sinon, afficher la vue
        $this->loadView('loginPageView');
    }
    
    private function login(): void {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['pwd'] ?? '';

        if (empty($email) || empty($password)) {
            $_SESSION['error'] = 'Tous les champs sont requis';
            header('Location: index.php?page=login');
            exit;
        }

        try {
            require_once __DIR__ . '/../models/User.php';
            $user = new User(null, null, null, $email, $password);

            $loggedInUser = $user->login();
            
            if ($loggedInUser) {
                $_SESSION['user_id'] = $loggedInUser['utilisateur_id'];
                $_SESSION['nom'] = $loggedInUser['nom'];
                $_SESSION['prenom'] = $loggedInUser['prenom'];
                $_SESSION['email'] = $loggedInUser['email'];
                $_SESSION['classe_annee'] = $loggedInUser['classe_annee'];
                $_SESSION['success'] = 'Connexion réussie ! Bienvenue ' . $loggedInUser['prenom'] . ' ' . $loggedInUser['nom'];
                header('Location: index.php?page=home');
            } else {
                $_SESSION['error'] = 'Email ou mot de passe incorrect';
                header('Location: index.php?page=login');
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Une erreur est survenue : ' . $e->getMessage();
            header('Location: index.php?page=login');
        }
        exit;
    }

    private function loadView($view) {
        require_once __DIR__ . '/../views/' . $view . '.php';
    }
}