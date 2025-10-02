<?php

    class RegisterController {
        public function index() {
            $this -> loadView('registerPageView');
        }

        public function loadView($viewName) {
            require_once __DIR__ . '/../views/' . $viewName . '.php';
        }
    }