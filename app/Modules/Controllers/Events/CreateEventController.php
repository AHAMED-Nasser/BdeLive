<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Events;

use App\Modules\Controllers\AdminController;
use DateTime;
use App\Modules\Models\Admin\EventCreationModel;

require_once __DIR__ . '/../../../include/csrf.php';

// model

class CreateEventController extends AdminController
{
    public function __construct()
    {
        $action = $_GET['action'] ?? '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'submitEvent') {
            $this -> createEvent();
        } else {
            parent::__construct();
            $this -> loadView('createEventPageView');
        }
    }

    public function createEvent(): void
    {
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
            $_SESSION['error'] = 'Token de sécurité invalide. Veuillez réessayer.';
            header('Location: index.php?page=createEvent');
            exit();
        }

        // Event creation logic goes here
        $eventName = $_POST['event-name'] ?? '';
        $eventDate = $_POST['event-date'] ?? '';
        $eventTime = $_POST['event-time'] ?? '';
        $eventLocation = $_POST['event-location'] ?? '';
        $eventTheme = $_POST['event-theme'] ?? '';
        $statusParticipatingArray = $_POST['status_participating'] ?? [];
        $statusParticipating = implode(',', $statusParticipatingArray);
        $description = $_POST['description'] ?? '';

        // Validate required fields
        if (empty($eventName) || empty($eventDate) || empty($eventTime) || empty($eventLocation) || empty($eventTheme) || empty($statusParticipating) || empty($description)) {
            $_SESSION['error'] = 'Tous les champs sont obligatoires';
            header('Location: index.php?page=createEvent');
            exit();
        }

        $creationModel = new EventCreationModel();
        $event = $creationModel -> insertEvent(
            $eventName,
            new DateTime($eventDate),
            new DateTime($eventTime),
            $eventLocation,
            $eventTheme,
            $statusParticipating,
            $description
        );

        if ($event) {
            $_SESSION['success'] = 'Événement créé avec succès';
            header('Location: index.php?page=event');
            exit();
        } else {
            $_SESSION['error'] = 'Une erreur est survenue lors de la création de l\'événement';
            header('Location: index.php?page=createEvent');
            exit();
        }
    }
    protected function loadView(string $viewName): void
    {
        require_once __DIR__ . '/../../views/events/' . $viewName . '.php';
    }
}
