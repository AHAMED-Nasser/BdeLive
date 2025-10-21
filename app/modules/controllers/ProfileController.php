<?php
class ProfileController
{
    public function __construct(){
        $this->loadView('profilePageView');
    }

    public function loadView(string $viewName): void {
        require_once __DIR__ . '/../views/' . $viewName . '.php';
    }
}