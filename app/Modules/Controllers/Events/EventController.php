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
 * @author BDELIVE - Group 8
 *
 * 
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
            // Model
            $repository = new EventRepository();
            $viewMode = $this->request->get('view', 'list'); // View mode detection

            if ($viewMode === 'calendar') {
                $this->showCalendar($repository);
            } else {
                $this->showList($repository);
            }
        } catch (Exception $e) {
            $this->setError('Error loading events: ' . $e->getMessage());
            $this->redirect('index.php?page=home');
        }
    }

    /**
     * Displays the calendar view with all events
     *
     * @param EventRepository $repository Event repository instance
     * @return void
     */
    private function showCalendar(EventRepository $repository): void
    {
        // Calendar mode: fetch all events and use native calendar
        $events = $repository->findAll();

        // Calendar parameters (year and month)
        $calYear = (int) $this->request->get('calyear', date('Y'));
        $calMonth = (int) $this->request->get('calmonth', date('n'));

        // Validate parameters
        if ($calMonth < 1 || $calMonth > 12) {
            $calMonth = (int) date('n');
        }
        if ($calYear < 2000 || $calYear > 2100) {
            $calYear = (int) date('Y');
        }

        // Format events for CalendarManager
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

        // Generate calendar with CalendarManager
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

    /**
     * Displays the list view with pagination
     *
     * @param EventRepository $repository Event repository instance
     * @return void
     */
    private function showList(EventRepository $repository): void
    {
        // List mode: maintain existing pagination logic
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
