<?php

namespace App\Modules\Controllers\Events;

use App\Modules\Controllers\AdminController;
use App\Modules\Models\Admin\EventCreationModel;
use App\Modules\Repositories\EventRepository;
use DateTime;
use Exception;

/**
 * Class UpdateEventController
 *
 * This controller handles the logic for modifying existing events.
 * Access is restricted to users with administrative privileges via inheritance from AdminController.
 *
 * Main functionalities:
 * - Loading and pre-filling the update form (GET).
 * - Validating security tokens (CSRF) and input data (POST).
 * - Updating event details including name, date, time, location, theme, and description.
 * - Handling input errors and server-side exceptions during the update process.
 *
 * @package App\Modules\Controllers\Events
 */
class UpdateEventController extends AdminController
{
    private EventCreationModel $eventModel;
    private EventRepository $eventRepository;
    private const REDIRECT_URL = 'index.php?page=event';
    private const REDIRECT_VIEW = 'events/updateEventPageView';

    /**
     * Initializes the controller, verifies admin access, and routes the request
     * to either display the form or process the submission based on the HTTP method.
     */
    public function __construct()
    {
        parent::__construct(); // Verify that's it an admin

        $this->eventModel = new EventCreationModel();
        $this->eventRepository = new EventRepository();

        $eventId = (int) $this -> request -> post('event_id', $this->request->get('id', 0));

        if ($eventId <= 0) {
            $this->redirectWithError(self::REDIRECT_URL, "ID d'événement non spécifié ou invalide.");
        }

        if ($this->request->isPost() && $this->request->post('action') === 'submitUpdate') {
            $this->processUpdate($eventId);
            return;
        }

        $this->displayForm($eventId);
    }

    /**
     * Retrieves event data and renders the update form view.
     *
     * @param int $eventId The unique identifier of the event to be modified.
     * @return void
     */
    private function displayForm(int $eventId): void
    {
        $event = $this->eventRepository->findById($eventId);

        if (!$event) {
            $this->redirectWithError(self::REDIRECT_URL, "L'événement à modifier n'existe pas.");
        }

        // Passage des données de l'événement à la vue
        $this->render(self::REDIRECT_VIEW, ['event' => $event]);
    }

    /**
     * Alternative entry point to handle the update request cycle.
     * @return void
     */
    public function handleRequest(): void
    {
        $eventId = $this->request->get('id');

        if (!$eventId || !is_numeric($eventId)) {
            $this -> session->flash('error', "ID d'événement non spécifié ou invalide.");
            $this -> response -> redirect('/events');
        }

        $eventId = (int) $eventId;
        $event = $this -> eventRepository -> findById($eventId);

        if (!$event) {
            $this -> session -> flash('error', "L'événement modifié n'existe pas.");
            $this -> response -> redirect('/events');
        }

        // POST traitement (form submit)
        if ($this -> request -> isPost()) {
            $this -> processUpdate($eventId);
            return;
        }

        // GET request (display form)
        // Event datas are passed at view to pre-fill the form
        $this -> render('events/updateEventPageView', ['event' => $event]);
    }

    /**
     * Validates and persists the updated event data into the database.
     *
     * This method performs CSRF verification, ensures all mandatory fields are present,
     * and converts string inputs into DateTime objects before calling the model.
     *
     * @param int $eventId The unique identifier of the event to update.
     * @return void
     */
    private function processUpdate(int $eventId): void
    {
        // 1. Validation CSRF
       $csrfToken = $this->request->post('csrf_token', '');
       if (!$this->csrf->validateToken((string)$csrfToken)) {
           $this->redirectWithError(self::REDIRECT_URL, 'Jeton de sécurité invalide. Veuillez réessayer.');
       }

        // 2. Récupération des données POST
        $eventName = (string)$this->request->post('event-name', '');
        $eventDateStr = (string)$this->request->post('event-date', '');
        $eventTimeStr = (string)$this->request->post('event-time', '');
        $eventLocation = (string)$this->request->post('event-location', '');
        $eventTheme = (string)$this->request->post('event-theme', '');
        $statusParticipatingArray = $this->request->post('status_participating', []);
        $description = (string)$this->request->post('description', '');

        // 3. Validation de base
        if (
            empty($eventName) || empty($eventDateStr) || empty($eventTimeStr) ||
            empty($eventLocation) || empty($eventTheme) || empty($description)
        ) {
            $this->redirectWithError(
                'index.php?page=updateEvent&id=' . $eventId,
                'Tous les champs sont obligatoires.'
            );
        }

        $statusParticipating = is_array($statusParticipatingArray) ? implode(',', $statusParticipatingArray) : '';

        try {
            $cloudinary = new \App\Services\CloudinaryService();
            $event = $this->eventRepository->findById($eventId);

            // On décode les images actuelle, on renvoie un tableau vide dans le cas ou il n'y a rien
            $currentImages = json_decode($event['images'] ?? '[]', true) ?: [];

            // 1. Handle deletation
            $imageToDelete = $this->request->post('delete_images', []);
            if (!empty($imageToDelete) && is_array($imageToDelete)) {
                foreach ($imageToDelete as $publicId) {
                    if ($cloudinary->deleteImage($publicId)) {
                        // Remove from our local array
                        $currentImages = array_filter($currentImages, function ($img) use ($publicId) {
                            $imgId = is_array($img) ? ($img['public_id'] ?? '') : '';
                            return $imgId !== $publicId;
                        });
                    }
                }
            }

            // 2. Handle New Upload
            if (isset($_FILES['event_images']) && !empty($_FILES['event_images']['name'][0])) {
                $newUploadedImages = $cloudinary->uploadMultipleImages($_FILES['event_images']);
                $currentImages = array_merge($currentImages, $newUploadedImages);
            }

            $imageJson = json_encode(array_values($currentImages)) ?: '[]';

            // Conversion en objets DateTime
            $eventDate = new DateTime($eventDateStr);
            $eventTime = new DateTime($eventTimeStr);

            // 4. Appel du modèle de mise à jour (sans images pour l'instant)
            $success = $this->eventModel->updateEvent(
                $eventId,
                $eventName,
                $eventDate,
                $eventTime,
                $eventLocation,
                $eventTheme,
                $statusParticipating,
                $description,
                $imageJson
            );

            if ($success) {
                $this->redirectWithSuccess(self::REDIRECT_URL, 'Événement mis à jour avec succès.');
            } else {
                $this->redirectWithError(self::REDIRECT_URL, 'Erreur lors de la mise à jour de l\'événement.');
            }
        } catch (Exception $e) {
            // Affiche l'erreur réelle au lieu du message générique pour tester
            $this->redirectWithError(self::REDIRECT_URL, 'Erreur : ' . $e->getMessage());
        }
    }
}
