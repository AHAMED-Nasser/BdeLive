<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Public;

use App\Modules\Controllers\DefaultController;

/**
 * Home Controller
 *
 * Handles the display of the application's home page.
 * This is the main entry point for users visiting the application.
 *
 * @package BdeLive\Controllers
 */
class HomeController extends DefaultController
{
    /**
     * Display the home page
     *
     * Loads and renders the home page view for the application.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->render('public/homePageView');
    }
}
