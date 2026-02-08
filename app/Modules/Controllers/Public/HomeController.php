<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Public;

use App\Modules\Controllers\DefaultController;
use App\Modules\Models\Articles\ArticleModel;
use App\Modules\Models\Events\EventModel;
use App\Core\Database;

/**
 * Home Controller
 *
 * Handles the display of the application's home page.
 * This is the main entry point for users visiting the application.
 *
 * @package BdeLive\Controllers
 * @version 1.0.0
 * @author BDELIVE - Group 8
 */
class HomeController extends DefaultController
{
    /**
     * Display the home page
     *
     * Loads and renders the home page view for the application.
     * Retrieves the two latest articles and upcoming events to display on the homepage.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Retrieve the two latest articles
        $articleModel = new ArticleModel(Database::getInstance()->getConnection());
        $articles = $articleModel->getLatestArticles(2);

        // Retrieve upcoming events for the carousel
        $eventModel = new EventModel(Database::getInstance()->getConnection());
        $events = $eventModel->findLatestEvents(5);

        // Pass articles and events to the view (empty array if none exist)
        $this->render('public/homePageView', [
            'articles' => $articles,
            'events' => $events
        ]);
    }
}
