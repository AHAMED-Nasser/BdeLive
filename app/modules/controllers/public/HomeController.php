<?php

/**
 * Home Controller
 * 
 * Handles the display of the application's home page.
 * This is the main entry point for users visiting the application.
 * 
 * @package BdeLive\Controllers
 */
class HomeController {

    /**
     * Display the home page
     * 
     * Loads and renders the home page view for the application.
     * 
     * @return void
     */
    public function __construct() {
        $this->loadView('homePageView');
    }

    /**
     * Load a view file
     * 
     * Helper method to include and render a view template.
     * 
     * @param string $viewName The name of the view file to load (without .php extension)
     * @return void
     */
    private function loadView(string $viewName): void {
        require_once __DIR__ . '/../../views/public/' . $viewName . '.php';
    }
}
?>


