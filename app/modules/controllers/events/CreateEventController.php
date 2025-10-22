<?php

class CreateEventController extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleEventCreation();
        } else {
            $this->loadView('createEventPageView');
        }
    }

    private function handleEventCreation(): void
    {
        // Validate CSRF token
        if (! isset($_POST['csrf_token']) || ! validateCsrfToken($_POST['csrf_token'])) {
            $_SESSION['error'] = 'Jeton de sécurité invalide. Veuillez réessayer.';
            $this->loadView('createEventPageView');

            return;
        }

        // TODO: Add event creation logic here
        $_SESSION['success'] = 'Événement créé avec succès !';
        header('Location: index.php?page=home');
        exit;
    }

    protected function loadView($viewName): void
    {
        require_once __DIR__ . '/../../views/events/' . $viewName . '.php';
    }
}
