<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Public;

use App\Modules\Controllers\DefaultController;
use App\Modules\Models\Admin\ArticleModel;

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
     * Retrieves the two latest articles to display on the homepage.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Retrieve the two latest articles
        $articleModel = new ArticleModel();
        $articles = $articleModel->getLatestArticles(2);

        // Pass articles to the view (empty array if none exist)
        $this->render('public/homePageView', [
            'articles' => $articles
        ]);
    }
}
