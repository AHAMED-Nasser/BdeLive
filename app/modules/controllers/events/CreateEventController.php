<?php

    declare(strict_types=1);

    namespace App\Modules\Controllers\Events;

    use App\Modules\Controllers\AdminController;
    use App\Modules\Models\Events\EventManager;
    use CloudinaryService;
    use DateTime;
    use Exception;

    require_once __DIR__ . '/../../../include/csrf.php';

    // model

    class CreateEventController extends AdminController {
        public function __construct() {
            parent::__construct();


            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // if POST request, process form submission
                $this -> createEvent();
            } else {
                // if GET request, show the create event form
                $this -> loadView('createEventPageView');
            }
        }

        public function createEvent(): void {

            // Get form data of the event creation
            /*
             * event name
             * event date
             * event time
             * event location
             * event theme
             * status participating (BUT 1, BUT 2, BUT 3, Personnel Enseignant) (array)
             * description
             * images (array)
             */
            $eventName = $_POST['event-name'] ?? '';
            $eventDate = $_POST['event-date'] ?? '';
            $eventTime = $_POST['event-time'] ?? '';
            $eventDateTime = new DateTime($eventDate . ' ' . $eventTime);
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

            // Upload images from Cloudinary
            // $_FILES['images'] contains the uploaded images in the input field
            $uploadedImages = [];
            if (isset($_FILES['images']) &&  !empty($_FILES['images']['name'][0])) { // check if $_FILES['images'] is set and not empty
                try {
                    $cloudinaryService = new CloudinaryService(); // instance of my CloudinaryService (our class)
                    $uploadedImages = $cloudinaryService->uploadMultipleImages(
                        $_FILES['images'],
                        'events/' . date('Y/m')
                    );
                } catch (Exception $e) {
                    error_log('Image upload failed: ' . $e->getMessage());}
            }

            /*
             * Insert event in database
             */
            $creationModel = new EventManager();
            $event = $creationModel -> insertEvent(
                $eventName,
                $eventDateTime->format('Y-m-d'),
                $eventDateTime->format('H:i'),
                $eventLocation,
                $eventTheme,
                $statusParticipating,
                $description,
                $uploadedImages
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