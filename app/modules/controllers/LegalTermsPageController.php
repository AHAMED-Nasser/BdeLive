<?php

class LegalTermsPageController
{

    public function index(): void
    {
        $this->loadView('legalTermsPageView');
    }

    private function loadView(string $viewName): void
    {
        require_once __DIR__ . '/../views/' . $viewName . '.php';
    }

}