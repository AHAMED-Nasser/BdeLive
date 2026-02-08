<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Events;

use App\Modules\Controllers\DefaultController;
use App\Modules\Models\Events\EventModel;
use App\Modules\Repositories\EventRegistrationRepository;
use App\Core\Database;
use Exception;

class ShowEventController extends DefaultController
{
    public function __construct()
    {
        parent::__construct();

        try {
            $slug = $this->request->get('slug', '');
            $eventId = (int) $this->request->get('id', 0);

            $eventModel = new EventModel(Database::getInstance()->getConnection());
            $event = null;

            // Priority 1: Slug (SEO-friendly URL)
            if (!empty($slug)) {
                $event = $eventModel->findBySlug((string) $slug);
                if (!$event) {
                    $this->redirectWithError('index.php?page=event', "L'événement demandé est introuvable");
                }
            } elseif ($eventId > 0) {
                // Priority 2: ID (legacy, redirect to slug for SEO)
                $event = $eventModel->findById($eventId);
                if (!$event) {
                    $this->redirectWithError('index.php?page=event', "L'événement demandé est introuvable");
                }
                $slugUrl = 'index.php?page=showEvent&slug=' . urlencode($event['slug']);
                header('Location: ' . $slugUrl, true, 301);
                exit;
            } else {
                $this->redirectWithError('index.php?page=event', 'Paramètres invalides');
            }

            // Instantiate repository once (cleaner code as per user recommendation)
            $registrationRepo = new EventRegistrationRepository();

            // Render event view
            $this->render('events/showEventView', [
                'event' => $event,
                'userId' => $this->user['user_id'] ?? null,
                'registrationRepo' => $registrationRepo
            ]);
        } catch (Exception $e) {
            $this->setError("Erreur : " . $e->getMessage());
            $this->redirect('index.php?page=event');
        }
    }
}
