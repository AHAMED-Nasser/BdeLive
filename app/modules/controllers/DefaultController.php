<?php

declare(strict_types=1);

namespace App\Modules\Controllers;

abstract class DefaultController
{
    public function __construct()
    {
    }

    /**
     * Render a view by relative path from `modules/views` without extension.
     * Example: render('users/profilePageView').
     */
    protected function render(string $viewPath): void
    {
        require_once __DIR__ . '/../views/' . $viewPath . '.php';
    }
}
