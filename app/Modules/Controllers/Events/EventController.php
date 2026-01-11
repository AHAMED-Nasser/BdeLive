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
                $this->showCalendar($repository);
            } else {
                $this->showList($repository);
            }
        } catch (Exception $e) {
            $this->setError('Erreur lors du chargement : ' . $e->getMessage());
            $this->redirect('index.php?page=home');
        }
    }

    private function showCalendar($repository): void
    {
        // Mode Calendrier : on récupère tout et on utilise le calendrier natif
        $events = $repository->findAll();

        // Paramètres du calendrier (année et mois)
        $calYear = isset($_GET['calyear']) ? (int)$_GET['calyear'] : (int)date('Y');
        $calMonth = isset($_GET['calmonth']) ? (int)$_GET['calmonth'] : (int)date('n');

        // Valider les paramètres
        if ($calMonth < 1 || $calMonth > 12) {
            $calMonth = (int)date('n');
        }
        if ($calYear < 2000 || $calYear > 2100) {
            $calYear = (int)date('Y');
        }

        // Formater les événements pour le CalendarManager
        $formattedEvents = [];
        foreach ($events as $event) {
            $eventDate = $event['event_date'] ?? null;
            if ($eventDate) {
                $formattedEvents[] = [
                    'id' => $event['event_id'],
                    'title' => $event['event_name'] ?? '',
                    'date' => $eventDate,
                    'time' => $event['event_time'] ?? '',
                    'description' => $event['description'] ?? ''
                ];
            }
        }

        // Générer le calendrier avec CalendarManager
        $calendarManager = new \App\Core\CalendarManager();
        $nativeCalendar = $calendarManager->generateMonthCalendar($calYear, $calMonth, $formattedEvents);

        $this->render('events/eventView', [
            'events' => $events,
            'viewMode' => 'calendar',
            'nativeCalendar' => $nativeCalendar,
            'calYear' => $calYear,
            'calMonth' => $calMonth
        ]);
    }

    private function showList($repository): void
    {
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
}
