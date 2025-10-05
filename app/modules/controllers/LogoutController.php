<?php

    class LogoutController {
        public function __construct() {
            $this -> logout();
        }

        public function logout() {
            $_SESSION = []; // clear session variable

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            session_unset();
            session_destroy();
            header('Location: index.php?page=home');
            exit;
        }
    }