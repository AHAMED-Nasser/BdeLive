<?php

class SitemapController
{
    //on laisse vide car c'est le routeur qui va gerer si afficher la vue ou generer le xml
    public function __construct() {
    }

    //affiche la page plan du site
    public function showHtmlPage() {
        $this->loadView('sitemapView');
    }

    //genere le xml, d'abord dans se fichier on met les pages existantes 
    // et leur priorité et dans .xml on met les autre pages ajouter 
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
        exit; // Arrêter l'exécution après génération du XML
    }

    
    private function loadView(string $viewName): void
    {
        require_once __DIR__ . '/../views/' . $viewName . '.php';
    }

}
