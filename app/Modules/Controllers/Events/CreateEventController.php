<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Events;

use App\Modules\Controllers\AdminController;
use App\Modules\Entities\Event;
use App\Modules\Repositories\EventRepository;
use App\Services\CloudinaryService;
use App\Core\Database;
use DateTime;

/**
 * CreateEventController - Event Creation for Administrators
 *
 * Handles the creation of new events with image upload to Cloudinary.
 * Only accessible to users with BDE (admin) status.
 *
 * Refactored to use Data Mapper pattern with Event entities and EventRepository.
 *
 * Features:
 * - Event form display
 * - Form validation (CSRF, required fields, date format)
 * - Multiple image upload to Cloudinary
 * - Event data persistence to database via repository
 * - Success/error feedback with flash messages
 *
 * @package BdeLive\Controllers\Events
 * @version 2.0.0 - Data Mapper refactoring
 * @author BDELIVE - Group 8
 *
 * @see AdminController For admin authentication requirements
 * @see EventRepository For database operations
 * @see CloudinaryService For image upload handling
 */
class CreateEventController extends AdminController
{
    /**
     * Constructor - Handle event creation form display and submission
     *
     * GET request: Displays the event creation form
     * POST request with action=submitEvent: Processes the form submission
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $action = $this->request->get('action', '');
        if ($this->request->isPost() && $action === 'submitEvent') {
            $this->createEvent();
        } else {
            $this->render('events/createEventPageView');
        }
    }

    /**
     * Create a new event
     *
     * Validates form data, uploads images to Cloudinary, and saves event to database.
     * Redirects back to form on validation error or to event list on success.
     *
     * Required form fields:
     * - event-name: Event title
     * - event-date: Event date (YYYY-MM-DD)
     * - event-time: Event time (HH:MM)
     * - event-location: Event location
     * - event-theme: Event theme/category
     * - status_participating: Array of allowed participant statuses
     * - description: Event description
     * - event-images: Optional image files (uploaded to Cloudinary)
     *
     * @return void Redirects to appropriate page with flash message
     */
    public function createEvent(): void
    {
        // Validate CSRF token
        $csrfToken = $this->request->post('csrf_token', '');

        if (!$this->csrf->validateToken((string) $csrfToken)) {
            $this->setError('Token de sécurité invalide. Veuillez réessayer.');
            $this->redirect('index.php?page=createEvent');
        }

        $eventName = (string) $this->request->post('event-name', '');
        $eventDate = (string) $this->request->post('event-date', '');
        $eventTime = (string) $this->request->post('event-time', '');
        $eventLocation = (string) $this->request->post('event-location', '');
        $eventTheme = (string) $this->request->post('event-theme', '');
        $statusParticipatingArray = $this->request->post('status_participating', []);
        $statusParticipating = is_array($statusParticipatingArray)
            ? implode(',', array_map('trim', array_filter($statusParticipatingArray)))
            : '';
        $description = (string) $this->request->post('description', '');

        // Group event fields
        $eventType = (string) $this->request->post('event_type', 'solo');
        $isGroupEvent = ($eventType === 'group');
        $teamSize = $isGroupEvent ? (int) $this->request->post('team_size', 2) : 1;

        $storeOldInput = function () use (
            $eventName, $eventDate, $eventTime, $eventLocation, $eventTheme,
            $statusParticipatingArray, $description, $eventType, $teamSize
        ): void {
            $this->session->set('old_event_name', $eventName);
            $this->session->set('old_event_date', $eventDate);
            $this->session->set('old_event_time', $eventTime);
            $this->session->set('old_event_location', $eventLocation);
            $this->session->set('old_event_theme', $eventTheme);
            $this->session->set('old_status_participating', is_array($statusParticipatingArray) ? $statusParticipatingArray : []);
            $this->session->set('old_event_description', $description);
            $this->session->set('old_event_type', $eventType);
            $this->session->set('old_team_size', (string) $teamSize);
        };

        // Validate team size for group events
        if ($isGroupEvent && ($teamSize < 2 || $teamSize > 20)) {
            $this->setError('Le nombre de personnes par groupe doit être entre 2 et 20');
            $storeOldInput();
            $this->redirect('index.php?page=createEvent');
        }

        // Validate required fields - preserve filled data on error
        if (
            empty($eventName) || empty($eventDate) || empty($eventTime) ||
            empty($eventLocation) || empty($eventTheme) ||
            empty($statusParticipating) || empty($description)
        ) {
            $this->setError('Tous les champs sont obligatoires');
            $storeOldInput();
            $this->redirect('index.php?page=createEvent');
        }

        // Upload images to Cloudinary
        $imageUrls = [];
        $files = $this->request->file('event-images');

        if ($files === null) {
            $files = [];
        }

        if (!empty($files['name'][0])) {
            try {
                $cloudinary = new CloudinaryService();
                /** @var array{name: array<int, string>, type: array<int, string>, tmp_name: array<int, string>, error: array<int, int>, size: array<int, int>} $files */
                $imageUrls = $cloudinary->uploadMultipleImages($files, 'events');
            } catch (\Exception $e) {
                error_log('CreateEventController::createEvent - Cloudinary error: ' . $e->getMessage());
                $this->setError('Erreur lors de l\'upload des images. Veuillez réessayer.');
                $storeOldInput();
                $this->redirect('index.php?page=createEvent');
            }
        }

        // Convert to JSON for storage
        $imagesJsonEncoded = !empty($imageUrls) ? json_encode($imageUrls) : '[]';
        $imagesJson = ($imagesJsonEncoded !== false) ? $imagesJsonEncoded : '[]';

        // Format dates for entity
        $dateFormatted = (new DateTime($eventDate))->format('Y-m-d');
        $timeFormatted = (new DateTime($eventTime))->format('H:i');

        // Create Event entity (id = null for new event)
        $event = new Event(
            id: null,
            name: $eventName,
            slug: '', // Will be generated by repository
            date: $dateFormatted,
            time: $timeFormatted,
            location: $eventLocation,
            theme: $eventTheme,
            statusParticipating: $statusParticipating,
            description: $description,
            images: $imagesJson,
            isGroupEvent: $isGroupEvent,
            teamSize: $teamSize
        );

        // Save via repository (will detect insert because id is null)
        $repository = new EventRepository(Database::getInstance()->getConnection());
        $success = $repository->save($event);

        if ($success) {
            $this->session->remove('old_event_name');
            $this->session->remove('old_event_date');
            $this->session->remove('old_event_time');
            $this->session->remove('old_event_location');
            $this->session->remove('old_event_theme');
            $this->session->remove('old_status_participating');
            $this->session->remove('old_event_description');
            $this->session->remove('old_event_type');
            $this->session->remove('old_team_size');
            $this->setSuccess('Événement créé avec succès');
            $this->redirect('index.php?page=event');
        } else {
            $this->setError('Une erreur est survenue lors de la création de l\'événement');
            $storeOldInput();
            $this->redirect('index.php?page=createEvent');
        }
    }
}
