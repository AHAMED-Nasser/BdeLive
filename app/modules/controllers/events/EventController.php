<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Events;

use App\Modules\Controllers\DefaultController;
use App\Modules\Helpers\Pagination;
use App\Modules\Repositories\EventRepository;
use Exception;

/**
 * Event Controller - Event management with pagination
 *
 * @package BdeLive\Controllers
 * @version 1.0.0
 */
class EventController extends DefaultController
{
    private const ITEMS_PER_PAGE = 4; // Events per page

    /**
     * Constructor - Initialize the controller and load the view
     */
    public function __construct()
    {
        parent::__construct();
        $this->loadView();
    }

    /**
     * Load the view with the necessary data
     *
     * @param string $viewName Name of the view to load (ignored, always uses eventView)
     */
    protected function loadView(string $viewName = 'eventView'): void
    {
        try {
            // 1. MODEL (Repository)
            $repository = new EventRepository();
            $totalEvents = $repository->count();

            // 2. HELPER (Pagination)
            $pagination = new Pagination($totalEvents, self::ITEMS_PER_PAGE);

            // 3. MODEL (Repository)
            $events = $repository->findPaginated(
                $pagination->getOffset(),
                $pagination->getLimit()
            );

            // 4. VIEW - Include the view and pass the data to it
            // These variables ($events, $pagination) will be available in the view
            require __DIR__ . '/../../views/events/eventView.php';
        } catch (Exception $e) {
            // Handle errors (e.g., display an error page)
            echo 'Erreur : ' . $e->getMessage();
        }
    }
}
