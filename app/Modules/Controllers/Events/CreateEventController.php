<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Events;

use App\Modules\Controllers\AdminController;
use DateTime;
use App\Modules\Models\Admin\EventCreationModel;
use App\Services\CloudinaryService;

/**
 * CreateEventController - Event Creation for Administrators
 *
 * Handles the creation of new events with image upload to Cloudinary.
 * Only accessible to users with BDE (admin) status.
 *
 * Features:
 * - Event form display
 * - Form validation (CSRF, required fields, date format)
 * - Multiple image upload to Cloudinary
 * - Event data persistence to database
 * - Success/error feedback with flash messages
 *
 * @package BdeLive\Controllers\Events
 * @version 1.0.0
 * @author BdeLive Team
 *
 * @see AdminController For admin authentication requirements
 * @see EventCreationModel For database operations
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

        // Event creation logic goes here
        $eventName = (string) $this->request->post('event-name', '');
        $eventDate = (string) $this->request->post('event-date', '');
        $eventTime = (string) $this->request->post('event-time', '');
        $eventLocation = (string) $this->request->post('event-location', '');
        $eventTheme = (string) $this->request->post('event-theme', '');
        $statusParticipatingArray = $this->request->post('status_participating', []);
        $statusParticipating = is_array($statusParticipatingArray) ? implode(',', $statusParticipatingArray) : '';
        $description = (string) $this->request->post('description', '');

        // Validate required fields
        if (
            empty($eventName) || empty($eventDate) || empty($eventTime) ||
            empty($eventLocation) || empty($eventTheme) ||
            empty($statusParticipating) || empty($description)
        ) {
            $this->setError('Tous les champs sont obligatoires');
            $this->redirect('index.php?page=createEvent');
        }

        // Upload images to Cloudinary
        $imageUrls = [];
        $files = $this->request->file('event-images');

        if ($files === null) {
            // Aucun fichier reçu
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
                $this->redirect('index.php?page=createEvent');
            }
        }

        // Convert to JSON for storage
        $imagesJsonEncoded = !empty($imageUrls) ? json_encode($imageUrls) : '[]';
        $imagesJson = ($imagesJsonEncoded !== false) ? $imagesJsonEncoded : '[]';

        $creationModel = new EventCreationModel();
        $event = $creationModel -> insertEvent(
            $eventName,
            new DateTime($eventDate),
            new DateTime($eventTime),
            $eventLocation,
            $eventTheme,
            $statusParticipating,
            $description,
            $imagesJson
        );

        if ($event) {
            $this->setSuccess('Événement créé avec succès');
            $this->redirect('index.php?page=event');
        } else {
            $this->setError('Une erreur est survenue lors de la création de l\'événement');
            $this->redirect('index.php?page=createEvent');
        }
    }
}
