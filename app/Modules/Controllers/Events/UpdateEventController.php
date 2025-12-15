<?php

namespace App\Modules\Controllers\Events;

use App\Modules\Controllers\AdminController;
use App\Modules\Models\Admin\EventCreationModel;
use App\Modules\Repositories\EventRepository;
use DateTime;

class UpdateEventController extends AdminController
{
    private EventCreationModel $eventModel;
    private EventRepository $eventRepository;
    private const REDIRECT_URL = 'index.php?page=event';
    private const REDIRECT_VIEW = 'events/updateEventPageView';

    public function __construct()
    {
        parent::__construct(); // Verify that's it an admin

        $this->eventModel = new EventCreationModel();
        $this->eventRepository = new EventRepository();

        $eventId = (int) $this -> request -> get('id', 0);

        if ($eventId <= 0) {
            $this->redirectWithError(self::REDIRECT_URL, "ID d'événement non spécifié ou invalide.");
            return;
        }

        if ($this->request->isPost() && $this->request->post('action') === 'submitUpdate') {
            $this->processUpdate($eventId);
        } else {
            $this->displayForm($eventId);
        }
    }

    private function displayForm(int $eventId): void
    {
        $event = $this->eventRepository->findById($eventId);

        if (!$event) {
            $this->redirectWithError(self::REDIRECT_URL, "L'événement à modifier n'existe pas.");
            return;
        }

        // Passage des données de l'événement à la vue
        $this->render(self::REDIRECT_VIEW, ['event' => $event]);
    }

    public function handleRequest(): void {
        $eventId = $this->request->get('id');

        if (!$eventId || !is_numeric($eventId)) {
            $this -> session->flash('error', "ID d'événement non spécifié ou invalide.");
            $this -> response -> redirect('/events');
            return;
        }

        $eventId = (int) $eventId;
        $event = $this -> eventRepository -> findById($eventId);

        if (!$event) {
            $this -> session -> flash('error', "L'événement modifié n'existe pas.");
            $this -> response -> redirect('/events');
            return;
        }

        // POST traitement (form submit)
        if ($this -> request -> isPost()) {
            $this -> processUpdate($eventId, $event);
            return;
        }

        // GET request (display form)
        // Event datas are passed at view to pre-fill the form
        $this -> render('events/updateEventPageView', ['event' => $event]);
    }

    private function processUpdate(int $eventId): void
    {
        // 1. Validation CSRF
        $csrfToken = $this->request->post('csrf_token', '');
        if (!$this->csrf->validateToken((string)$csrfToken)) {
            $this->redirectWithError(self::REDIRECT_URL, 'Jeton de sécurité invalide. Veuillez réessayer.');
            return;
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
        if (empty($eventName) || empty($eventDateStr) || empty($eventTimeStr) || empty($eventLocation) || empty($eventTheme) || empty($description)) {
            $this->redirectWithError('index.php?page=updateEvent&id=' . $eventId, 'Tous les champs sont obligatoires.');
            return;
        }

        $statusParticipating = is_array($statusParticipatingArray) ? implode(',', $statusParticipatingArray) : '';

        try {
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
                $description
            );

            if ($success) {
                $this->redirectWithSuccess(self::REDIRECT_URL, 'Événement mis à jour avec succès.');
            } else {
                $this->redirectWithError(self::REDIRECT_URL, 'Erreur lors de la mise à jour de l\'événement.');
            }
        } catch (Exception $e) {
            error_log('UpdateEventController::processUpdate - ' . $e->getMessage());
            $this->redirectWithError(self::REDIRECT_URL, 'Erreur interne du serveur lors de la mise à jour.');
        }
    }
}