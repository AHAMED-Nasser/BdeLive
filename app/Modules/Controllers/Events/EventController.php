<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Events;

use App\Modules\Controllers\DefaultController;
use App\Modules\Helpers\Pagination;
use App\Modules\Repositories\EventRepository;
use App\Core\Database;
use Exception;

/**
 * Event Controller - Event management with pagination
 *
 * Refactored to use Data Mapper pattern with EventRepository
 * and Event entities.
 *
 * @package BdeLive\Controllers
 * @version 2.0.0 - Data Mapper refactoring
 * @author BDELIVE - Group 8
 */
class EventController extends DefaultController
{
    private const ITEMS_PER_PAGE = 4; // Events per page

    private EventRepository $eventRepository;

    /**
     * Constructor - Initialize the controller and load the view
     */
    public function __construct()
    {
        parent::__construct();

        // Instantiate concrete repository (no DI Container yet)
        $this->eventRepository = new EventRepository(Database::getInstance()->getConnection());

        try {
            $viewMode = $this->request->get('view', 'list'); // View mode detection

            if ($viewMode === 'calendar') {
                $this->showCalendar();
            } else {
                $this->showList();
            }
        } catch (Exception $e) {
            $this->setError('Error loading events: ' . $e->getMessage());
            $this->redirect('index.php?page=home');
        }
    }

    /**
     * Displays the calendar view with all events
     *
     * @return void
     */
    private function showCalendar(): void
    {
        // Calendar mode: fetch all events and use native calendar
        $events = $this->eventRepository->findAll();

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

        // Format events for CalendarManager (using entity getters)
        $formattedEvents = [];
        foreach ($events as $event) {
            $formattedEvents[] = [
                'id' => $event->getId(),
                'slug' => $event->getSlug(),
                'title' => $event->getName(),
                'date' => $event->getDate(),
                'time' => $event->getTime(),
                'description' => $event->getDescription()
            ];
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
     * @return void
     */
    private function showList(): void
    {
        // List mode: maintain existing pagination logic
        $totalEvents = $this->eventRepository->count();

        // 2. HELPER (Pagination)
        $pagination = new Pagination($totalEvents, self::ITEMS_PER_PAGE);

        // 3. REPOSITORY
        $events = $this->eventRepository->findPaginated(
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
