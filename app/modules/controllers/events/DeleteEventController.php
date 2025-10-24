<?php

declare(strict_types=1);

/**
 * Delete Event Controller
 * Handles event deletion for administrators
 *
 * @package BdeLive\Controllers
 * @version 1.0.0
 */
class DeleteEventController extends AdminController
{
    public function __construct()
    {
        // Only allow POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Méthode non autorisée';
            header('Location: index.php?page=pagination');
            exit;
        }

        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
            $_SESSION['error'] = 'Jeton de sécurité invalide';
            header('Location: index.php?page=pagination');
            exit;
        }

        // Get and validate event ID
        $eventId = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
        if ($eventId === false || $eventId <= 0) {
            $_SESSION['error'] = 'ID d\'événement invalide';
            header('Location: index.php?page=pagination');
            exit;
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
            $model = new EventCreationModel();
            $success = $model->deleteEvent($eventId);

            if ($success) {
                $_SESSION['success'] = 'Événement supprimé avec succès';
            } else {
                $_SESSION['error'] = 'Erreur lors de la suppression de l\'événement';
            }
        } catch (Exception $e) {
            error_log('DeleteEventController::deleteEvent - ' . $e->getMessage());
            $_SESSION['error'] = 'Erreur interne du serveur';
        }

        // Redirect back to pagination page
        header('Location: index.php?page=pagination');
        exit;
    }

    /**
     * Required by parent class - not used in this controller
     */
    protected function loadView(string $viewName): void
    {
        // This controller doesn't load views, it only handles POST requests
    }
}
