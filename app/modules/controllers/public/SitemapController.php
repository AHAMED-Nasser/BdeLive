<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Public;

class SitemapController
{
    public function __construct()
    {
        $this->loadView('sitemapView');
    }



    private function loadView(string $viewName): void
    {
        require_once __DIR__ . '/../../views/public/' . $viewName . '.php';
    }
}
