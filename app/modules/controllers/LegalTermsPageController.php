<?php

/**
 * Legal Terms Page Controller
 * 
 * Handles the display of legal terms and conditions page.
 * Shows the application's terms of service, privacy policy, and legal information.
 * 
 * @package BdeLive\Controllers
 * @author Mohamed-Amine Boudhib, Thomas Palot, Amin Helali, Willem Chetioui, Nasser Ahamed, Romain Cantor
 * @version 1.0.0
 */
class LegalTermsPageController
{

    /**
     * Display the legal terms page
     * 
     * Loads and renders the legal terms view containing terms of service
     * and privacy policy information.
     * 
     * @return void
     */
    public function index(): void
    {
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
        require_once __DIR__ . '/../views/' . $viewName . '.php';
    }

}