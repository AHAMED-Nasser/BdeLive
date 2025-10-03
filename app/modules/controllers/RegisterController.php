<?php

    class RegisterController {
        public function execute(): void
        {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: /register');
                exit;
            }

            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($email) || empty($password)) {
                $_SESSION['error'] = 'Tous les champs sont requis';
                header('Location: /register');
                exit;
            }
            try {
                $user = new User(null, $username, $email, $password);

                if ($user->register()) {
                    $_SESSION['success'] = 'Inscription réussie ! Vous pouvez vous connecter.';
                    header('Location: /login');
                } else {
                    $_SESSION['error'] = 'Erreur lors de l\'inscription';
                    header('Location: /register');
                }
            } catch (Exception $e) {
                $_SESSION['error'] = 'Une erreur est survenue';
                header('Location: /register');
            }
            exit;
        }

        public function loadView($viewName) {
            require_once __DIR__ . '/../views/' . $viewName . '.php';
        }
    }