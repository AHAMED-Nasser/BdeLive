<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Public;

use App\Modules\Controllers\DefaultController;
use App\Modules\Repositories\ArticleRepository;
use App\Modules\Repositories\EventRepository;
use App\Core\Database;

/**
 * Home Controller
 *
 * Handles the display of the application's home page.
 * This is the main entry point for users visiting the application.
 *
 * Refactored to use Data Mapper pattern with entities and repositories.
 *
 * @package BdeLive\Controllers
 * @version 2.0.0 - Data Mapper refactoring
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

        // Retrieve the two latest articles (entities)
        $articleRepository = new ArticleRepository(Database::getInstance()->getConnection());
        $articles = $articleRepository->findLatestArticles(2);

        // Retrieve events for carousel: upcoming first, then recent past if needed (max 5)
        $eventRepository = new EventRepository(Database::getInstance()->getConnection());
        $events = $eventRepository->findEventsForHomepage(5);

        // Pass articles and events entities to the view (empty array if none exist)
        $this->render('public/homePageView', [
            'articles' => $articles,
            'events' => $events
        ]);
    }
}
