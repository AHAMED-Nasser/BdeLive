<?php
    class CreateEventController extends AdminController {
        public $creationModel;
        public function __construct() {
            $this -> creationModel = new EventCreationModel();
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ok'])){
                $this -> createEvent();
            } else {
                parent::__construct();
                $this -> loadView('createEventPageView');
            }
        }

        public function createEvent(): void {

            // Debug : Voir ce qui est reçu
            error_log("POST data: " . print_r($_POST, true));

            // Event creation logic goes here
            $eventName = $_POST['event-name'] ?? '';
            $eventDate = $_POST['event-date'] ?? '';
            $eventTime = $_POST['event-time'] ?? '';
            $eventLocation = $_POST['event-location'] ?? '';
            $eventTheme = $_POST['event-theme'] ?? '';
            $statusParticipatingArray = $_POST['status_participating'] ?? [];
            $statusParticipating = implode(',', $statusParticipatingArray);
            $description = $_POST['description'] ?? '';

            // Debug : Voir les valeurs récupérées
            error_log("Event Name: $eventName");
            error_log("Event Date: $eventDate");
            error_log("Event Time: $eventTime");
            error_log("Event Location: $eventLocation");
            error_log("Event Theme: $eventTheme");
            error_log("Status: $statusParticipating");
            error_log("Description: $description");

            // Validate required fields
            if (empty($eventName) || empty($eventDate) || empty($eventTime) || empty($eventLocation) || empty($eventTheme) || empty($statusParticipating) || empty($description)) {
                $_SESSION['error'] = 'Tous les champs sont obligatoires';
                header('Location: index.php?page=createEvent');
                exit();
            }

            error_log("Validation passed, calling insertEvent...");

            $eventCreated = $this->creationModel->insertEvent(
                $eventName,
                new DateTime($eventDate),
                new DateTime($eventTime),
                $eventLocation,
                $eventTheme,
                $statusParticipating,
                $description
            );

            error_log("Insert result: " . ($eventCreated ? "SUCCESS" : "FAILED"));

            if ($eventCreated){
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