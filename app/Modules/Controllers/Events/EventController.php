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

        try {
            // 1. MODEL (Repository)
            $repository = new EventRepository();
            $viewMode = $_GET['view'] ?? 'list'; // Détection du mode

            if ($viewMode === 'calendar') {
                // Mode Calendrier : on récupère tout
                $events = $repository->findAll();
                $this->render('events/eventView', [
                    'events' => $events,
                    'viewMode' => 'calendar'
                ]);
            } else {
                // Mode Liste : conservation de la logique de pagination existante
                $totalEvents = $repository->count();

                // 2. HELPER (Pagination)
                $pagination = new Pagination($totalEvents, self::ITEMS_PER_PAGE);

                // 3. MODEL (Repository)
                $events = $repository->findPaginated(
                    $pagination->getOffset(),
                    $pagination->getLimit()
                );

                $this->render('events/eventView', [
                    'events' => $events,
                    'pagination' => $pagination,
                    'viewMode' => 'list'
                ]);
            }
        } catch (Exception $e) {
            $this->setError('Erreur lors du chargement : ' . $e->getMessage());
            $this->redirect('index.php?page=home');
        }

//        try {
//            // 1. MODEL (Repository)
//            $repository = new EventRepository();
//            $totalEvents = $repository->count();
//
//            // 2. HELPER (Pagination)
//            $pagination = new Pagination($totalEvents, self::ITEMS_PER_PAGE);
//
//            // 3. MODEL (Repository)
//            $events = $repository->findPaginated(
//                $pagination->getOffset(),
//                $pagination->getLimit()
//            );
//
//            // 4. VIEW - Render with BaseController (injects $csrf, $auth, $flash, $user)
//            $this->render('events/eventView', [
//                'events' => $events,
//                'pagination' => $pagination
//            ]);
//        } catch (Exception $e) {
//            // Handle errors
//            $this->setError('Erreur lors du chargement des événements : ' . $e->getMessage());
//            $this->redirect('index.php?page=home');
//        }
    }
}
