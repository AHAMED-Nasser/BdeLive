<?php

declare(strict_types=1);

class ModifyEventController extends AdminController {
        private EventManager $eventManager;
        /** @var array<string, mixed> */
        private array $eventData = [];

        public function __construct() {
            // Vérifier d'abord l'authentification admin
            parent::__construct();
            
            $this->eventManager = new EventManager();
            $action = $_GET['action'] ?? '';
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'submitModify'){
                $this->modifyEvent();
            } else {
                $this->displayModifyForm();
            }
        }

        private function displayModifyForm(): void {
            $eventId = $_GET['event_id'] ?? null;
            
            if (!$eventId || !is_numeric($eventId)) {
                $_SESSION['error'] = 'ID d\'événement invalide';
                header('Location: index.php?page=pagination');
                exit();
            }

            $eventData = $this->eventManager->getEventById((int)$eventId);
            
            if ($eventData === false) {
                $_SESSION['error'] = 'Événement non trouvé';
                header('Location: index.php?page=pagination');
                exit();
            }
            
            $this->eventData = $eventData;

            $this->loadView('modifyEventPageView');
        }

        public function modifyEvent(): void {
            $eventId = $_POST['event_id'] ?? null;
            
            if (!$eventId || !is_numeric($eventId)) {
                $_SESSION['error'] = 'Événement non trouvé';
                header('Location: index.php?page=pagination');
                exit();
            }

            $eventName = $_POST['event-name'] ?? '';
            $eventDate = $_POST['event-date'] ?? '';
            $eventTime = $_POST['event-time'] ?? '';
            $eventLocation = $_POST['event-location'] ?? '';
            $eventTheme = $_POST['event-theme'] ?? '';
            $statusParticipatingArray = $_POST['status_participating'] ?? [];
            $statusParticipating = implode(',', $statusParticipatingArray);
            $description = $_POST['description'] ?? '';

            // Valider les champs obligatoires
            if (empty($eventName) || empty($eventDate) || empty($eventTime) || empty($eventLocation) || empty($eventTheme) || empty($statusParticipating) || empty($description)) {
                $_SESSION['error'] = 'Tous les champs sont obligatoires';
                header('Location: index.php?page=modifyEvent&event_id=' . $eventId);
                exit();
            }

            $result = $this->eventManager->modifyEvent(
                (int)$eventId,
                $eventName,
                new DateTime($eventDate),
                new DateTime($eventTime),
                $eventLocation,
                $eventTheme,
                $statusParticipating,
                $description
            );

            if ($result){
                $_SESSION['success'] = 'Événement modifié avec succès';
                header('Location: index.php?page=pagination');
                exit();
            } else {
                $_SESSION['error'] = 'Une erreur est survenue lors de la modification de l\'événement';
                header('Location: index.php?page=modifyEvent&event_id=' . $eventId);
                exit();
            }
        }

        protected function loadView(string $viewName): void {
            // Rendre $eventData disponible dans la vue
            $eventData = $this->eventData;
            require_once __DIR__ . '/../../views/events/' . $viewName . '.php';
        }
    }

