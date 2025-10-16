<?php

class SitemapController
{
    public function __construct() {
        $this->loadView('sitemapView');
    }


    private function loadView(string $viewName): void
    {
        require_once __DIR__ . '/../views/' . $viewName . '.php';
    }

}
