<?php

class LoginController {
    public function index(){
        $this -> loadView('loginPageView');
    }

    public function loadView($view) {
        require_once __DIR__ . '/../views/' . $view . '.php';
    }
}