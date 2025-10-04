<?php

class RegisterController {
    
    public function index() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->register();
            return;
        }
        
        $this->loadView('registerPageView');
    }
    
    private function register(): void {
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $classe_annee = intval($_POST['classe_annee'] ?? 0);

        if (empty($nom) || empty($prenom) || empty($email) || empty($password) || empty($classe_annee)) {
            $_SESSION['error'] = 'Tous les champs sont requis';
            header('Location: index.php?page=register');
            exit;
        }
        
        if ($classe_annee < 1 || $classe_annee > 3) {
            $_SESSION['error'] = 'La classe doit être entre 1 et 3';
            header('Location: index.php?page=register');
            exit;
        }
        
        try {
            require_once __DIR__ . '/../models/User.php';
            $user = new User(null, $nom, $prenom, $email, $password, $classe_annee);

            if ($user->register()) {
                $_SESSION['success'] = 'Inscription réussie ! Vous pouvez vous connecter.';
                header('Location: index.php?page=login');
            } else {
                $_SESSION['error'] = 'Erreur lors de l\'inscription. L\'email existe peut-être déjà.';
                header('Location: index.php?page=register');
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Une erreur est survenue : ' . $e->getMessage();
            header('Location: index.php?page=register');
        }
        exit;
    }

    private function loadView($viewName) {
        require_once __DIR__ . '/../views/' . $viewName . '.php';
    }
}