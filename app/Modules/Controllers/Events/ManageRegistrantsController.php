<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Events;

use App\Modules\Controllers\AdminController;
use App\Modules\Repositories\EventRegistrationRepository;
use Exception;

/**
 * ManageRegistrantsController - Admin batch management of event registrants
 *
 * Handles bulk registration and unregistration of users from individual
 * (non-group) events. Only accessible to administrators via POST with
 * CSRF validation.
 *
 * Supported actions (via POST 'action' field):
 * - 'add': Register selected users to the event
 * - default/remove: Unregister selected users from the event
 *
 * @package BdeLive\Controllers\Events
 * @version 1.1.0
 * @author BdeLive - Group 8
 *
 * @see AdminController For admin authentication requirements
 * @see EventRegistrationRepository For database operations
 */
class ManageRegistrantsController extends AdminController
{
    /**
     * Event registration repository instance
     *
     * @var EventRegistrationRepository
     */
    private EventRegistrationRepository $registrationRepo;

    /**
     * Constructor - Handle batch registrant management
     *
     * Validates HTTP method, CSRF token, event ID, and user IDs
     * before dispatching to the appropriate action handler.
     *
     * @throws \App\Core\Exception\AuthenticationException If user is not authenticated
     * @throws \App\Core\Exception\AuthorizationException If user lacks admin rights
     */
    public function __construct()
    {
        parent::__construct();

        $this->registrationRepo = new EventRegistrationRepository();

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

        // Get and validate user IDs
        $rawUserIds = $this->request->post('user_ids', []);
        if (!is_array($rawUserIds) || empty($rawUserIds)) {
            $this->setError('Aucun inscrit sélectionné');
            $this->redirect('index.php?page=showEvent&id=' . $eventId);
        }

        // Sanitize user IDs to integers and filter invalid values
        $userIds = array_filter(
            array_map('intval', $rawUserIds),
            static fn(int $id): bool => $id > 0
        );

        if (empty($userIds)) {
            $this->setError('Identifiants d\'inscrits invalides');
            $this->redirect('index.php?page=showEvent&id=' . $eventId);
        }

        // Dispatch to appropriate action
        $action = (string) $this->request->post('action', 'remove');

        if ($action === 'add') {
            $this->addRegistrants($eventId, $userIds);
        } else {
            $this->removeRegistrants($eventId, $userIds);
        }
    }

    /**
     * Add selected users as registrants to the event
     *
     * Delegates batch registration to the repository and sets appropriate
     * flash message based on the number of successful registrations.
     *
     * @param int $eventId The event identifier
     * @param array<int, int> $userIds Array of user identifiers to register
     * @return void Redirects to event detail page
     */
    private function addRegistrants(int $eventId, array $userIds): void
    {
        try {
            $addedCount = $this->registrationRepo->registerUsers($eventId, $userIds);

            if ($addedCount > 0) {
                $message = $addedCount === 1
                    ? '1 inscrit ajouté avec succès'
                    : $addedCount . ' inscrits ajoutés avec succès';
                $this->setSuccess($message);
            } else {
                $this->setError('Aucun inscrit n\'a été ajouté (déjà inscrits ou erreur)');
            }
        } catch (Exception $e) {
            error_log('ManageRegistrantsController::addRegistrants - ' . $e->getMessage());
            $this->setError('Erreur interne lors de l\'ajout des inscrits');
        }

        $this->redirect('index.php?page=showEvent&id=' . $eventId);
    }

    /**
     * Remove selected registrants from the event
     *
     * Delegates batch deletion to the repository and sets appropriate flash message
     * based on the number of successfully deleted registrations.
     *
     * @param int $eventId The event identifier
     * @param array<int, int> $userIds Array of user identifiers to remove
     * @return void Redirects to event detail page
     */
    private function removeRegistrants(int $eventId, array $userIds): void
    {
        try {
            $deletedCount = $this->registrationRepo->unregisterUsers($eventId, $userIds);

            if ($deletedCount > 0) {
                $message = $deletedCount === 1
                    ? '1 inscrit supprimé avec succès'
                    : $deletedCount . ' inscrits supprimés avec succès';
                $this->setSuccess($message);
            } else {
                $this->setError('Aucun inscrit n\'a été supprimé');
            }
        } catch (Exception $e) {
            error_log('ManageRegistrantsController::removeRegistrants - ' . $e->getMessage());
            $this->setError('Erreur interne lors de la suppression des inscrits');
        }

        $this->redirect('index.php?page=showEvent&id=' . $eventId);
    }
}
