<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Events;

use App\Modules\Controllers\AuthenticatedController;
use App\Modules\Repositories\EventRegistrationRepository;
use App\Modules\Repositories\EventTeamRepository;

/**
 * RegisterEventController - Event Registration Management
 *
 * Handles user registration and unregistration for events.
 * Only accessible to authenticated users.
 *
 * Features:
 * - User registration to events
 * - User unregistration from events (deletes entire team if group event)
 * - Duplicate registration prevention
 * - User authentication verification
 * - Success/error feedback with flash messages
 *
 * @package BdeLive\Controllers\Events
 * @version 1.1.0
 * @author BdeLive Team
 *
 * @see AuthenticatedController For authentication requirements
 * @see EventRegistrationRepository For database operations
 */
class RegisterEventController extends AuthenticatedController
{
    /**
     * Event registration repository instance
     *
     * @var EventRegistrationRepository
     */
    private EventRegistrationRepository $repo;

    /**
     * Event team repository instance
     *
     * @var EventTeamRepository
     */
    private EventTeamRepository $teamRepo;

    /**
     * Constructor - Handle event registration actions
     *
     * Supports two actions via GET parameter 'action':
     * - register: Register current user to the event
     * - unregister: Unregister current user from the event
     *
     * Requires GET parameter 'event_id' (positive integer).
     *
     * @return void Redirects with appropriate flash message
     */
    public function __construct()
    {
        parent::__construct();
        $this->repo = new EventRegistrationRepository();
        $this->teamRepo = new EventTeamRepository();

        $action = $this->request->get('action', '');
        $eventId = (int) $this->request->get('event_id', 0);

        if ($eventId <= 0) {
            $this->redirectWithMessage($eventId, 'ID événement invalide', false);
            return;
        }

        match ($action) {
            'register' => $this->register($eventId),
            'unregister' => $this->unregister($eventId),
            default => $this->redirectWithMessage($eventId, 'Action invalide', false)
        };
    }

    /**
     * Register the current user to an event
     *
     * Verifies user authentication and checks for duplicate registration
     * before adding the user to the event.
     *
     * @param int $eventId The event ID to register to
     * @return void Redirects to event page with flash message
     */
    private function register(int $eventId): void
    {
        $user = $this->auth->getUser();
        if (!$user || !isset($user['user_id'])) {
            $this->redirectWithMessage($eventId, 'Utilisateur non authentifié', false);
            return;
        }

        $userId = $user['user_id'];

        if ($this->repo->isUserRegistered($eventId, $userId)) {
            $this->redirectWithMessage($eventId, 'Vous êtes déjà inscrit à cet événement', false);
            return;
        }

        $this->repo->registerUser($eventId, $userId);
        $this->redirectWithMessage($eventId, 'Inscription réussie à l\'événement');
    }

    /**
     * Unregister the current user from an event
     *
     * If user is part of a team, the entire team is deleted.
     * All team members are unregistered when any member leaves.
     *
     * @param int $eventId The event ID to unregister from
     * @return void Redirects to event page with flash message
     */
    private function unregister(int $eventId): void
    {
        $user = $this->auth->getUser();
        if (!$user || !isset($user['user_id'])) {
            $this->redirectWithMessage($eventId, 'Utilisateur non authentifié', false);
            return;
        }

        $userId = $user['user_id'];

        if (!$this->repo->isUserRegistered($eventId, $userId)) {
            $this->redirectWithMessage($eventId, 'Vous n\'êtes pas inscrit à cet événement', false);
            return;
        }

        // Check if user is part of a team
        $teamId = $this->repo->getTeamIdByUserAndEvent($userId, $eventId);

        if ($teamId !== null) {
            // User is in a team - delete entire team and all member registrations
            $this->repo->deleteRegistrationsByTeam($teamId);
            $this->teamRepo->deleteTeam($teamId);
            $this->redirectWithMessage($eventId, 'Désinscription réussie. Le groupe entier a été supprimé.');
        } else {
            // Individual registration
            $this->repo->unregisterUser($eventId, $userId);
            $this->redirectWithMessage($eventId, 'Désinscription réussie');
        }
    }

    /**
     * Redirect with a flash message
     *
     * Helper method to redirect to event list with success or error message.
     *
     * @param string $message The message to display
     * @param bool $success True for success message, false for error message
     * @return void Redirects to event page
     */
    private function redirectWithMessage(int $eventId, string $message, bool $success = true): void
    {
        if ($success) {
            $this->setSuccess($message);
        } else {
            $this->setError($message);
        }
        parent::redirect('index.php?page=showEvent&id=' . $eventId);
    }
}
