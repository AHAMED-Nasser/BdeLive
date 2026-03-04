<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Events;

use App\Modules\Controllers\AdminController;
use App\Modules\Repositories\EventRegistrationRepository;
use App\Modules\Repositories\EventTeamRepository;

/**
 * SearchUsersController - AJAX endpoint for searching users and teams
 *
 * Returns JSON results for admin event management. Supports two modes:
 * - User search: users not registered to the given event (default)
 * - Team search: teams belonging to the given event (type=teams)
 *
 * Auto-detects search mode for users: numeric queries search by user_id,
 * text queries search by first_name/last_name.
 *
 * Only accessible to authenticated administrators.
 *
 * @package BdeLive\Controllers\Events
 * @version 1.1.0
 * @author BdeLive - Group 8
 *
 * @see AdminController For admin authentication requirements
 * @see EventRegistrationRepository::searchUsersNotRegistered() For user search logic
 * @see EventTeamRepository::getTeamsByEvent() For team search logic
 */
class SearchUsersController extends AdminController
{
    /**
     * Constructor - Handle AJAX search request
     *
     * Reads event_id, q (search query), and type from GET parameters.
     * Dispatches to the appropriate search handler and returns JSON.
     *
     * @throws \App\Core\Exception\AuthenticationException If user is not authenticated
     * @throws \App\Core\Exception\AuthorizationException If user lacks admin rights
     */
    public function __construct()
    {
        parent::__construct();

        $eventId = (int) $this->request->get('event_id', 0);
        $query = (string) $this->request->get('q', '');
        $type = (string) $this->request->get('type', 'users');

        if ($eventId <= 0) {
            $this->response->json(['error' => 'ID d\'événement invalide', 'results' => []], 400);
        }

        if ($type === 'teams') {
            $this->searchTeams($eventId);
        }

        if (strlen(trim($query)) < 1) {
            $this->response->json(['results' => []]);
        }

        $registrationRepo = new EventRegistrationRepository();
        $results = $registrationRepo->searchUsersNotRegistered($eventId, $query);

        $this->response->json(['results' => $results]);
    }

    /**
     * Search teams for a group event
     *
     * Returns all teams (with team_id, team_number, member count)
     * for the given event. Used by the group management UI
     * to populate team selector dropdowns.
     *
     * @param int $eventId The event identifier
     * @return never Sends JSON response and exits
     */
    private function searchTeams(int $eventId): void
    {
        $teamRepo = new EventTeamRepository();
        $teams = $teamRepo->getTeamsByEvent($eventId);

        // Simplify team data for the frontend
        $results = [];
        foreach ($teams as $team) {
            $results[] = [
                'team_id' => (int) $team['team_id'],
                'team_number' => (int) $team['team_number'],
                'status' => $team['status'] ?? 'unknown',
                'creator' => ($team['first_name'] ?? '') . ' ' . ($team['last_name'] ?? '')
            ];
        }

        $this->response->json(['teams' => $results]);
    }
}
