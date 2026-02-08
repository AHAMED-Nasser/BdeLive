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
            $eventId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            $eventModel = new EventModel(Database::getInstance()->getConnection());
            $event = $eventModel->findById($eventId);

            if (!$event) {
                $this->redirectWithError('index.php?page=event', "L'événement en question n'a pas été événement trouvé");
            }

            // On passe l'ID de l'utilisateur et le repository d'inscription à la vue
            $this->render('events/showEventView', [
                'event' => $event,
                'userId' => $this->user['user_id'] ?? null, // Récupère l'ID via BaseController
                'registrationRepo' => new EventRegistrationRepository() // Instancie le repo
            ]);
        } catch (Exception $e) {
            $this->setError("Erreur : " . $e->getMessage());
            $this->redirect('index.php?page=event');
        }
    }
}
