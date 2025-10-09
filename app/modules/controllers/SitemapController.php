<?php

class SitemapController
{

    public function index(): void
    {
        $this->loadView('sitemapView');
    }

    private function loadView(string $viewName): void
    {
        require_once __DIR__ . '/../views/' . $viewName . '.php';
    }

}
