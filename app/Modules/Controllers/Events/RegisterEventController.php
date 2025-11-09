<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Events;

use App\Modules\Controllers\AuthenticatedController;
use App\Modules\Repositories\EventRegistrationRepository;

class RegisterEventController extends AuthenticatedController
{
    private EventRegistrationRepository $repo;

    public function __construct()
    {
        parent::__construct();
        $this->repo = new EventRegistrationRepository();

        $action = $_GET['action'] ?? '';
        $eventId = (int)($_GET['event_id'] ?? 0);

        if ($eventId <= 0) {
            $this->redirect('ID événement invalide');
            return;
        }

        match ($action) {
            'register' => $this->register($eventId),
            'unregister' => $this->unregister($eventId),
            default => $this->redirect('Action invalide')
        };
    }

    private function register(int $eventId): void
    {
        $userId = $_SESSION['user_id'];

        if ($this->repo->isUserRegistered($eventId, $userId)) {
            $this->redirect('Déjà inscrit', false);
            return;
        }

        $this->repo->registerUser($eventId, $userId);
        $this->redirect('Inscription réussie');
    }

    private function unregister(int $eventId): void
    {
        $userId = $_SESSION['user_id'];

        if (!$this->repo->isUserRegistered($eventId, $userId)) {
            $this->redirect('Non inscrit', false);
            return;
        }

        $this->repo->unregisterUser($eventId, $userId);
        $this->redirect('Désinscription réussie');
    }

    private function redirect(string $message, bool $success = true): void
    {
        $_SESSION[$success ? 'success' : 'error'] = $message;
        header('Location: index.php?page=event');
        exit();
    }

    protected function loadView(string $viewName): void
    {
    }
}
