<?php

class LegalTermsPageController
{

    public function __construct()
    {
        $this->loadView('legalTermsPageView');
    }

    private function loadView(string $viewName): void
    {
        require_once __DIR__ . '/../views/' . $viewName . '.php';
    }

}