<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Events;

use App\Modules\Controllers\AdminController;
use App\Modules\Repositories\EventRegistrationRepository;

/**
 * SearchUsersController - AJAX endpoint for searching users
 *
 * Returns JSON results of users not registered to a given event,
 * matching the provided search query. Auto-detects search mode:
 * numeric queries search by user_id, text queries search by name.
 *
 * Only accessible to authenticated administrators.
 *
 * @package BdeLive\Controllers\Events
 * @version 1.0.0
 * @author BdeLive - Group 8
 *
 * @see AdminController For admin authentication requirements
 * @see EventRegistrationRepository::searchUsersNotRegistered() For search logic
 */
class SearchUsersController extends AdminController
{
    /**
     * Constructor - Handle AJAX search request
     *
     * Reads event_id and q (search query) from GET parameters,
     * delegates to repository, and returns JSON response.
     *
     * @throws \App\Core\Exception\AuthenticationException If user is not authenticated
     * @throws \App\Core\Exception\AuthorizationException If user lacks admin rights
     */
    public function __construct()
    {
        parent::__construct();

        $eventId = (int) $this->request->get('event_id', 0);
        $query = (string) $this->request->get('q', '');

        if ($eventId <= 0) {
            $this->response->json(['error' => 'ID d\'événement invalide', 'results' => []], 400);
        }

        if (strlen(trim($query)) < 1) {
            $this->response->json(['results' => []]);
        }

        $registrationRepo = new EventRegistrationRepository();
        $results = $registrationRepo->searchUsersNotRegistered($eventId, $query);

        $this->response->json(['results' => $results]);
    }
}
