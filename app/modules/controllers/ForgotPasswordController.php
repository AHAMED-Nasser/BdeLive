<?php

class ForgotPasswordController {
    public function __construct() {
        $this->render();
    }

    private function render(): void {
        require_once __DIR__ . '/../views/forgotPasswordView.php';
    }
}

