<?php

class LegalTermsPageController
{

    public function __construct()
    {
        $this->loadView('legalTermsPageView');
        
    }

    public function loadView($viewName)
    {
        require_once __DIR__ . '/../views/' . $viewName . '.php';
    }

}