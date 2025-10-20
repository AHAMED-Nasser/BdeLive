<?php

/**
 * Legal Terms Controller
 * 
 * Handles the display of legal terms and conditions page.
 * 
 * @package BdeLive\Controllers
 */
class LegalTermsController
{

    /**
     * Display the legal terms page
     * 
     * Loads and renders the legal terms view containing terms of service
     * and privacy policy information.
     * 
     * @return void
     */
    public function __construct() {
        $this->loadView('legalTermsPageView');
    }

    /**
     * Load a view file
     * 
     * Helper method to include and render a view template.
     * 
     * @param string $viewName The name of the view file to load (without .php extension)
     * @return void
     */
    private function loadView(string $viewName): void
    {
        require_once __DIR__ . '/../../views/public/' . $viewName . '.php';
    }

}


