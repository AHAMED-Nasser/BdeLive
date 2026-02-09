<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Events;

use App\Modules\Controllers\DefaultController;
use App\Modules\Repositories\Interfaces\EventRepositoryInterface;
use App\Modules\Repositories\EventRegistrationRepository;
use Exception;

/**
 * ShowEventController - Display single event details
 *
 * Refactored to use Data Mapper pattern with:
 * - EventRepositoryInterface (Dependency Inversion Principle)
 * - Event entity instead of raw arrays
 * - Repository injected via constructor (DI Container)
 *
 * @package BdeLive\Controllers\Events
 * @author BdeLive - Group 8
 * @version 2.0.0 - Data Mapper + DI
 */
class ShowEventController extends DefaultController
{
    private EventRepositoryInterface $eventRepository;

    public function __construct(EventRepositoryInterface $eventRepository)
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
                // Priority 2: ID (legacy, redirect to slug for SEO)
                $event = $this->eventRepository->findById($eventId);
                if (!$event) {
                    $this->redirectWithError('index.php?page=event', "L'événement demandé est introuvable");
                }
                // Use entity getter instead of array access
                $slugUrl = 'index.php?page=showEvent&slug=' . urlencode($event->getSlug());
                header('Location: ' . $slugUrl, true, 301);
                exit;
            } else {
                $this->redirectWithError('index.php?page=event', 'Paramètres invalides');
            }

            $registrationRepo = new EventRegistrationRepository();
            $user = $this->auth->getUser();

            $this->render('events/showEventView', [
                'event' => $event,
                'userId' => $user !== null ? ($user['user_id'] ?? null) : null,
                'registrationRepo' => $registrationRepo
            ]);
        } catch (Exception $e) {
            $this->setError("Erreur : " . $e->getMessage());
            $this->redirect('index.php?page=event');
        }
    }
}
