<?php

class LoginController {
    public function __construct(){
        $this -> loadView('loginPageView');
    }

    public function loadView($view) {
        require_once __DIR__ . '/../views/' . $view . '.php';
    }

    public function processLogin() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $mdp = $_POST['mdp'] ?? '';

            // Validation basique
            if (empty($email) || empty($mdp)) {
                $_SESSION['error'] = "Veuillez remplir tous les champs";
                header('Location: /login');
                exit;
            }

            // Tentative de connexion
            require_once __DIR__ . '/../models/User.php';
            $user = new User(null, null, null, null);

            if ($user->login($email, $mdp)) {
                // Connexion réussie
                $_SESSION['success'] = "Connexion réussie !";
                header('Location: /dashboard'); // Rediriger vers le tableau de bord
                exit;
            } else {
                // Échec de connexion
                $_SESSION['error'] = "Email ou mot de passe incorrect";
                header('Location: /login');
                exit;
            }
        }
    }

    public function logout() {
        session_start();
        session_destroy();
        header('Location: /login');
        exit;
    }
}