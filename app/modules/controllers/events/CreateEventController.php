<?php
    class CreateEventController extends AdminController {
        public function __construct() {
            $action = $_GET['action'] ?? '';
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'submitEvent'){
                $this -> createEvent();
            } else {
                parent::__construct();
                $this -> loadView('createEventPageView');
            }
        }

        public function createEvent(): void {
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

            if ($event){
                $_SESSION['success'] = 'Événement créé avec succès';
                header('Location: index.php?page=event');
                exit();
            } else {
                $_SESSION['error'] = 'Une erreur est survenue lors de la création de l\'événement';
                header('Location: index.php?page=createEvent');
                exit();
            }
        }
        protected function loadView(string $viewName): void {
            require_once __DIR__ . '/../../views/events/' . $viewName . '.php';
        }
    }