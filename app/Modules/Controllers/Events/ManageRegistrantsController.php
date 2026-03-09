<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Events;

use App\Modules\Controllers\AdminController;
use App\Modules\Repositories\EventRegistrationRepository;
use App\Modules\Repositories\EventTeamRepository;
use Exception;

/**
 * ManageRegistrantsController - Admin batch management of event registrants
 *
 * Handles bulk registration and unregistration of users from events,
 * including both individual and group event operations.
 * Only accessible to administrators via POST with CSRF validation.
 *
 * Supported actions (via POST 'action' field):
 * - 'add': Register selected users to an individual event
 * - 'remove' (default): Unregister selected users from an individual event
 * - 'group_add': Add selected users to a specific team in a group event
 * - 'group_remove': Remove selected users from their group
 * - 'group_move': Move a user to a different team
 * - 'group_delete': Delete an entire team and its registrations
 *
 * @package BdeLive\Controllers\Events
 * @version 2.0.0
 * @author BdeLive - Group 8
 *
 * @see AdminController For admin authentication requirements
 * @see EventRegistrationRepository For registration database operations
 * @see EventTeamRepository For team database operations
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
     * Event team repository instance
     *
     * @var EventTeamRepository
     */
    private EventTeamRepository $teamRepo;

    /**
     * Constructor - Handle batch registrant management
     *
     * Validates HTTP method, CSRF token, and event ID before
     * dispatching to the appropriate action handler.
     *
     * @throws \App\Core\Exception\AuthenticationException If user is not authenticated
     * @throws \App\Core\Exception\AuthorizationException If user lacks admin rights
     */
    public function __construct()
    {
        parent::__construct();

        $this->registrationRepo = new EventRegistrationRepository();
        $this->teamRepo = new EventTeamRepository();

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

        // Dispatch to appropriate action
        $action = (string) $this->request->post('action', 'remove');

        switch ($action) {
            case 'add':
                $this->addRegistrants($eventId, $this->getValidatedUserIds($eventId));
                break;
            case 'group_add':
                $this->groupAdd($eventId);
                break;
            case 'group_create':
                $this->groupCreate($eventId);
                break;
            case 'group_remove':
                $this->groupRemove($eventId, $this->getValidatedUserIds($eventId));
                break;
            case 'group_move':
                $this->groupMove($eventId);
                break;
            case 'group_delete':
                $this->groupDelete($eventId);
                break;
            default:
                $this->removeRegistrants($eventId, $this->getValidatedUserIds($eventId));
                break;
        }
    }

    /**
     * Validate and return sanitized user IDs from POST data
     *
     * Reads user_ids[] from POST, sanitizes to positive integers,
     * and redirects with error if none are valid.
     *
     * @param int $eventId The event identifier for redirect on error
     * @return array<int, int> Array of validated positive user identifiers
     */
    private function getValidatedUserIds(int $eventId): array
    {
        $rawUserIds = $this->request->post('user_ids', []);
        if (!is_array($rawUserIds) || empty($rawUserIds)) {
            $this->setError('Aucun inscrit sélectionné');
            $this->redirect('index.php?page=showEvent&id=' . $eventId);
        }

        $userIds = array_filter(
            array_map('intval', $rawUserIds),
            static fn(int $id): bool => $id > 0
        );

        if (empty($userIds)) {
            $this->setError('Identifiants d\'inscrits invalides');
            $this->redirect('index.php?page=showEvent&id=' . $eventId);
        }

        return $userIds;
    }

    // ===================================================================
    // Individual event actions
    // ===================================================================

    /**
     * Add selected users as registrants to the event
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

    // ===================================================================
    // Group event actions
    // ===================================================================

    /**
     * Add selected users to a specific team in a group event
     *
     * Reads team_id from POST data, validates it, then adds each
     * selected user to that team.
     *
     * @param int $eventId The event identifier
     * @return void Redirects to event detail page
     */
    private function groupAdd(int $eventId): void
    {
        $teamId = (int) $this->request->post('team_id', 0);
        if ($teamId <= 0) {
            $this->setError('Groupe invalide');
            $this->redirect('index.php?page=showEvent&id=' . $eventId);
        }

        $userIds = $this->getValidatedUserIds($eventId);

        try {
            $addedCount = 0;
            foreach ($userIds as $userId) {
                if ($this->registrationRepo->addUserToGroup($eventId, $userId, $teamId)) {
                    $addedCount++;
                }
            }

            if ($addedCount > 0) {
                $message = $addedCount === 1
                    ? '1 inscrit ajouté au groupe'
                    : $addedCount . ' inscrits ajoutés au groupe';
                $this->setSuccess($message);
            } else {
                $this->setError('Aucun inscrit n\'a été ajouté (déjà inscrits ou erreur)');
            }
        } catch (Exception $e) {
            error_log('ManageRegistrantsController::groupAdd - ' . $e->getMessage());
            $this->setError('Erreur interne lors de l\'ajout au groupe');
        }

        $this->redirect('index.php?page=showEvent&id=' . $eventId);
    }

    /**
     * Remove selected users from their group in a group event
     *
     * @param int $eventId The event identifier
     * @param array<int, int> $userIds Array of user identifiers to remove
     * @return void Redirects to event detail page
     */
    private function groupRemove(int $eventId, array $userIds): void
    {
        try {
            $removedCount = 0;
            foreach ($userIds as $userId) {
                if ($this->registrationRepo->removeUserFromGroup($eventId, $userId)) {
                    $removedCount++;
                }
            }

            if ($removedCount > 0) {
                $message = $removedCount === 1
                    ? '1 inscrit retiré du groupe'
                    : $removedCount . ' inscrits retirés du groupe';
                $this->setSuccess($message);
            } else {
                $this->setError('Aucun inscrit n\'a été retiré');
            }
        } catch (Exception $e) {
            error_log('ManageRegistrantsController::groupRemove - ' . $e->getMessage());
            $this->setError('Erreur interne lors du retrait du groupe');
        }

        $this->redirect('index.php?page=showEvent&id=' . $eventId);
    }

    /**
     * Move a user to a different team within the same event
     *
     * Reads user_id and new_team_id from POST data.
     *
     * @param int $eventId The event identifier
     * @return void Redirects to event detail page
     */
    private function groupMove(int $eventId): void
    {
        $userId = (int) $this->request->post('user_id', 0);
        $newTeamId = (int) $this->request->post('new_team_id', 0);

        if ($userId <= 0 || $newTeamId <= 0) {
            $this->setError('Paramètres de déplacement invalides');
            $this->redirect('index.php?page=showEvent&id=' . $eventId);
        }

        try {
            // Verify the target team exists and belongs to this event
            $team = $this->teamRepo->findById($newTeamId);
            if ($team === null || (int) $team['event_id'] !== $eventId) {
                $this->setError('Le groupe de destination n\'existe pas pour cet événement');
                $this->redirect('index.php?page=showEvent&id=' . $eventId);
            }

            if ($this->registrationRepo->changeUserTeam($eventId, $userId, $newTeamId)) {
                $this->setSuccess('Inscrit déplacé vers le groupe ' . (int) $team['team_number']);
            } else {
                $this->setError('Erreur lors du déplacement de l\'inscrit');
            }
        } catch (Exception $e) {
            error_log('ManageRegistrantsController::groupMove - ' . $e->getMessage());
            $this->setError('Erreur interne lors du déplacement');
        }

        $this->redirect('index.php?page=showEvent&id=' . $eventId);
    }

    /**
     * Delete an entire team and all its registrations
     *
     * Reads team_id from POST data. Deletes registrations first,
     * then the team record.
     *
     * @param int $eventId The event identifier
     * @return void Redirects to event detail page
     */
    private function groupDelete(int $eventId): void
    {
        $teamId = (int) $this->request->post('team_id', 0);

        if ($teamId <= 0) {
            $this->setError('Groupe invalide');
            $this->redirect('index.php?page=showEvent&id=' . $eventId);
        }

        try {
            // Verify the team belongs to this event
            $team = $this->teamRepo->findById($teamId);
            if ($team === null || (int) $team['event_id'] !== $eventId) {
                $this->setError('Le groupe n\'existe pas pour cet événement');
                $this->redirect('index.php?page=showEvent&id=' . $eventId);
            }

            $teamNumber = (int) $team['team_number'];

            // Delete registrations first, then the team
            $this->registrationRepo->deleteGroupRegistrants($eventId, $teamId);
            $this->teamRepo->deleteTeam($teamId);

            $this->setSuccess('Groupe ' . $teamNumber . ' supprimé avec succès');
        } catch (Exception $e) {
            error_log('ManageRegistrantsController::groupDelete - ' . $e->getMessage());
            $this->setError('Erreur interne lors de la suppression du groupe');
        }

        $this->redirect('index.php?page=showEvent&id=' . $eventId);
    }

    /**
     * Create a new group and add selected users to it
     *
     * Creates a new team for the event, then registers
     * all selected users into the newly created team.
     *
     * @param int $eventId The event identifier
     * @return void Redirects to event detail page
     */
    private function groupCreate(int $eventId): void
    {
        $userIds = $this->getValidatedUserIds($eventId);

        try {
            // Create new team (uses getNextTeamNumber internally)
            $newTeamId = $this->teamRepo->createTeam($eventId, $userIds[0]);

            if ($newTeamId === null) {
                $this->setError('Impossible de créer un nouveau groupe');
                $this->redirect('index.php?page=showEvent&id=' . $eventId);
            }

            $addedCount = 0;
            foreach ($userIds as $userId) {
                if ($this->registrationRepo->addUserToGroup($eventId, $userId, $newTeamId)) {
                    $addedCount++;
                }
            }

            // Get team number for message
            $team = $this->teamRepo->findById($newTeamId);
            $teamNumber = $team !== null ? (int) $team['team_number'] : '?';

            $this->setSuccess('Groupe ' . $teamNumber . ' créé avec ' . $addedCount . ' inscrit(s)');
        } catch (Exception $e) {
            error_log('ManageRegistrantsController::groupCreate - ' . $e->getMessage());
            $this->setError('Erreur interne lors de la création du groupe');
        }

        $this->redirect('index.php?page=showEvent&id=' . $eventId);
    }
}
