<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Events;

use App\Modules\Controllers\AdminController;
use App\Modules\Repositories\EventRepository;
use App\Core\Database;
use Exception;

/**
 * Delete Event Controller
 * Handles event deletion for administrators
 *
 * Refactored to use EventRepository (Data Mapper pattern).
 *
 * @package BdeLive\Controllers
 * @version 2.0.0 - Data Mapper refactoring
 * @author BDELIVE - Group 8
 *
 * @see AdminController For admin authentication requirements
 * @see EventRepository For database operations
 */
class DeleteEventController extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        // Only allow POST requests
        if (!$this->request->isPost()) {
            $this->setError('Méthode non autorisée');
            $this->redirect('index.php?page=event');
        }
        // Validate CSRF token
        $csrfToken = $this->request->post('csrf_token', '');

        if (!$this->csrf->validateToken((string) $csrfToken)) {
            $this->setError('Token de sécurité invalide. Veuillez réessayer.');
            $this->redirect('index.php?page=event');
        }

        // Get and validate event ID
        $eventId = (int) $this->request->post('event_id', 0);
        if ($eventId <= 0) {
            $this->setError('ID d\'événement invalide');
            $this->redirect('index.php?page=event');
        }

        // Delete the event
        $this->deleteEvent($eventId);
    }

    /**
     * Delete an event
     * @param int $eventId The event ID to delete
     */
    private function deleteEvent(int $eventId): void
    {
        try {
            $repository = new EventRepository(Database::getInstance()->getConnection());
            $success = $repository->delete($eventId);

            if ($success) {
                $this->setSuccess('Événement supprimé avec succès');
            } else {
                $this->setError('Erreur lors de la suppression de l\'événement');
            }
        } catch (Exception $e) {
            error_log('DeleteEventController::deleteEvent - ' . $e->getMessage());
            $this->setError('Erreur interne du serveur');
        }

        // Redirect back to pagination page
        $this->redirect('index.php?page=event');
    }
}
