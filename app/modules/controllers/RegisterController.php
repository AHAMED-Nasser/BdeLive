<?php

    class RegisterController
    {
        public function __construct()
        {
            $this->loadView('registerPageView');
        }



        public function loadView($viewName)
        {
            require_once __DIR__ . '/../views/' . $viewName . '.php';
        }
    }