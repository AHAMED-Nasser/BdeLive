<?php

/**
 * SitemapController
 * 
 * Handles the display of the HTML sitemap page and the generation
 * of the XML sitemap. The controller allows for SEF site structure 
 * documentation and can respond to requests by providing the 
 * user-friendly site map or the dynamically generated XML file 
 * for search engine indexing.
 * 
 * @package BdeLive\Controllers
 * @author Mohamed-Amine Boudhib, Thomas Palot, Amin Helali, Willem Chetioui, Nasser Ahamed, Romain Cantor
 * @version 1.0.0
 */

class SitemapController
{
    /**
     * Constructor for SitemapController
     * 
     * Initializes the controller. The routing logic determines whether
     * to display the HTML sitemap view or generate the XML sitemap.
     * 
     * @return void
     */
    public function __construct() {
    }

    /**
     * Display the HTML sitemap page
     * 
     * Renders the user-friendly HTML sitemap page by loading
     * the corresponding view template.
     * 
     * @return void
     */
    public function showHtmlPage() {
        $this->loadView('sitemapView');
    }

    /**
     * Generate the XML sitemap for search engine indexing
     * 
     * Creates a dynamic XML sitemap containing all static pages with their
     * respective priorities, change frequencies, and last modification dates.
     * The generated XML is compliant with the Sitemap protocol and can be
     * used by search engines for better indexing.
     * 
     * Sets the appropriate XML content-type header and outputs the sitemap.
     * The script execution terminates after the XML is generated to prevent
     * any additional output.
     * 
     * @return void This method outputs XML directly and exits
     */
    public function generateXml() {
        header('Content-Type: application/xml; charset=utf-8');
        
        $baseUrl = 'https://bdelivesae.alwaysdata.net';
        $staticPages = [
            'home' => ['priority' => '1.0', 'changefreq' => 'daily', 'lastmod' => date('Y-m-d\TH:i:s\Z')],
            'register' => ['priority' => '0.8', 'changefreq' => 'monthly', 'lastmod' => date('Y-m-d\TH:i:s\Z')],
            'login' => ['priority' => '0.8', 'changefreq' => 'monthly', 'lastmod' => date('Y-m-d\TH:i:s\Z')],
            'legalTerms' => ['priority' => '0.5', 'changefreq' => 'yearly', 'lastmod' => date('Y-m-d\TH:i:s\Z')],
            'forgot_password' => ['priority' => '0.6', 'changefreq' => 'monthly', 'lastmod' => date('Y-m-d\TH:i:s\Z')],
            'sitemap' => ['priority' => '0.3', 'changefreq' => 'monthly', 'lastmod' => date('Y-m-d\TH:i:s\Z')],
            'profile' => ['priority' => '0.8', 'changefreq' => 'monthly', 'lastmod' => date('Y-m-d\TH:i:s\Z')],
        ];

        require_once __DIR__ . '/../../sitemap.xml.php';
        
        $sitemapGenerator = new SitemapGenerator($baseUrl);
        
        $sitemapGenerator->addStaticPages($staticPages);
        
        echo $sitemapGenerator->generate();
        exit;
    }

    /**
     * Load and include a view template
     * 
     * Loads the specified view file from the views directory.
     * This is a helper method to maintain consistent view loading
     * throughout the controller.
     * 
     * @param string $viewName The name of the view file (without extension)
     * @return void
     * @throws Exception If the view file does not exist
     */
    private function loadView(string $viewName): void
    {
        require_once __DIR__ . '/../views/' . $viewName . '.php';
    }

}
