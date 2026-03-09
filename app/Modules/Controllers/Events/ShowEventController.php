<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Events;

use App\Modules\Controllers\DefaultController;
use App\Modules\Repositories\EventRepository;
use App\Modules\Repositories\EventRegistrationRepository;
use Exception;

/**
 * ShowEventController - Display single event details
 *
 * Refactored to use Data Mapper pattern with:
 * - EventRepository (injected via constructor)
 * - Event entity instead of raw arrays
 * - Repository injected via constructor (DI Container)
 *
 * @package BdeLive\Controllers\Events
 * @author BdeLive - Group 8
 * @version 2.0.0 - Data Mapper + DI
 */
class ShowEventController extends DefaultController
{
    private EventRepository $eventRepository;

    public function __construct(EventRepository $eventRepository)
    {
        parent::__construct();
        $this->eventRepository = $eventRepository;

        try {
            $slug = $this->request->get('slug', '');
            $eventId = (int) $this->request->get('id', 0);

            $event = null;

            // Priority 1: Slug (SEO-friendly URL)
            if (!empty($slug)) {
                $event = $this->eventRepository->findBySlug((string) $slug);
                if (!$event) {
                    $this->redirectWithError('index.php?page=event', "L'événement demandé est introuvable");
                }
            } elseif ($eventId > 0) {
                // Priority 2: ID (legacy, redirect to slug for SEO when slug présent)
                $event = $this->eventRepository->findById($eventId);
                if (!$event) {
                    $this->redirectWithError('index.php?page=event', "L'événement demandé est introuvable");
                }
                $eventSlug = $event->getSlug();
                // Redirection vers slug uniquement si non vide (évite boucle infinie)
                if ($eventSlug !== '') {
                    $slugUrl = 'index.php?page=showEvent&slug=' . urlencode($eventSlug);
                    header('Location: ' . $slugUrl, true, 301);
                    exit;
                }
            } else {
                $this->redirectWithError(
                    'index.php?page=event',
                    'Paramètres invalides. Veuillez sélectionner un événement depuis la liste.'
                );
            }

            $registrationRepo = new EventRegistrationRepository();
            $user = $this->auth->getUser();

            $registrants = [];
            $groupRegistrants = [];
            $totalGroupRegistrants = 0;

            if ($event->isGroupEvent()) {
                // Fetch group registrants grouped by team number
                $groupRegistrants = $registrationRepo->getGroupRegistrantsForEvent((int) $event->getId());
                foreach ($groupRegistrants as $members) {
                    $totalGroupRegistrants += count($members);
                }
            } else {
                // Fetch individual registrants
                $registrants = $registrationRepo->getIndividualRegistrantsForEvent((int) $event->getId());
            }

            $this->render('events/showEventView', [
                'event' => $event,
                'userId' => $user !== null ? ($user['user_id'] ?? null) : null,
                'registrationRepo' => $registrationRepo,
                'registrants' => $registrants,
                'groupRegistrants' => $groupRegistrants,
                'totalGroupRegistrants' => $totalGroupRegistrants
            ]);
        } catch (Exception $e) {
            $this->setError("Erreur : " . $e->getMessage());
            $this->redirect('index.php?page=event');
        }
    }
}
