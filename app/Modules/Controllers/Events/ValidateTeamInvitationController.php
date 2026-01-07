<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Events;

use App\Modules\Controllers\DefaultController;
use App\Modules\Repositories\EventTeamRepository;
use App\Modules\Repositories\EventTeamInvitationRepository;
use App\Modules\Repositories\EventRegistrationRepository;

/**
 * ValidateTeamInvitationController - Team Invitation Validation
 *
 * Handles the validation of team invitations via email link.
 * Users click on the link in their email to confirm or decline their participation.
 *
 * Features:
 * - Validate invitation tokens
 * - Confirm or decline participation
 * - Automatically confirm team when all members validated
 * - Register confirmed teams to the event
 *
 * @package BdeLive\Controllers\Events
 * @version 1.0.0
 * @author BdeLive Team
 */
class ValidateTeamInvitationController extends DefaultController
{
    /**
     * Team repository instance
     *
     * @var EventTeamRepository
     */
    private EventTeamRepository $teamRepo;

    /**
     * Invitation repository instance
     *
     * @var EventTeamInvitationRepository
     */
    private EventTeamInvitationRepository $invitationRepo;

    /**
     * Registration repository instance
     *
     * @var EventRegistrationRepository
     */
    private EventRegistrationRepository $registrationRepo;

    /**
     * Constructor - Handle invitation validation
     *
     * Supports actions via GET parameter:
     * - Default: Display invitation details
     * - confirm: Confirm participation
     * - decline: Decline participation
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->teamRepo = new EventTeamRepository();
        $this->invitationRepo = new EventTeamInvitationRepository();
        $this->registrationRepo = new EventRegistrationRepository();

        $token = $this->request->get('token', '');
        $action = $this->request->get('action', '');

        if (empty($token)) {
            $this->redirectWithError('index.php?page=home', 'Lien de validation invalide');
        }

        // Find the invitation by token
        $invitation = $this->invitationRepo->findByToken($token);

        if (!$invitation) {
            $this->redirectWithError('index.php?page=home', 'Invitation introuvable ou expirée');
        }

        // Check if already processed
        if ($invitation['validation_status'] !== 'pending') {
            $this->render('events/teamInvitationResultView', [
                'invitation' => $invitation,
                'alreadyProcessed' => true,
                'status' => $invitation['validation_status']
            ]);
            return;
        }

        // Handle actions
        switch ($action) {
            case 'confirm':
                $this->confirmInvitation($invitation, $token);
                break;
            case 'decline':
                $this->declineInvitation($invitation, $token);
                break;
            default:
                $this->displayInvitation($invitation, $token);
        }
    }

    /**
     * Display the invitation details for the user to confirm or decline
     *
     * @param array<string, mixed> $invitation Invitation data
     * @param string $token The validation token
     * @return void
     */
    private function displayInvitation(array $invitation, string $token): void
    {
        // Get team info and other members
        $teamId = (int) $invitation['team_id'];
        $team = $this->teamRepo->findById($teamId);
        $members = $this->invitationRepo->getInvitationsByTeam($teamId);

        $this->render('events/teamInvitationView', [
            'invitation' => $invitation,
            'team' => $team,
            'members' => $members,
            'token' => $token
        ]);
    }

    /**
     * Confirm the invitation
     *
     * @param array<string, mixed> $invitation Invitation data
     * @param string $token The validation token
     * @return void
     */
    private function confirmInvitation(array $invitation, string $token): void
    {
        $invitationId = (int) $invitation['invitation_id'];
        $teamId = (int) $invitation['team_id'];
        $eventId = (int) $invitation['event_id'];

        // Check if user is logged in and link them
        $user = $this->auth->getUser();
        $userId = null;
        
        if ($user && strtolower($user['email']) === strtolower($invitation['email'])) {
            $userId = (int) $user['user_id'];
        } elseif ($invitation['user_id']) {
            $userId = (int) $invitation['user_id'];
        }

        // Update the invitation status
        $updated = $this->invitationRepo->updateValidationStatus($invitationId, 'confirmed', $userId);

        if (!$updated) {
            $this->redirectWithError(
                'index.php?page=validateTeamInvitation&token=' . $token,
                'Erreur lors de la validation de votre participation'
            );
        }

        // Check if all members have confirmed
        if ($this->invitationRepo->areAllInvitationsConfirmed($teamId)) {
            // Update team status to confirmed
            $this->teamRepo->updateStatus($teamId, 'confirmed');
            
            // Register all confirmed members to the event
            $this->registerTeamMembers($teamId, $eventId);
        }

        // Refresh invitation data
        $invitation = $this->invitationRepo->findByToken($token);

        $this->render('events/teamInvitationResultView', [
            'invitation' => $invitation,
            'alreadyProcessed' => false,
            'status' => 'confirmed',
            'allConfirmed' => $this->invitationRepo->areAllInvitationsConfirmed($teamId)
        ]);
    }

    /**
     * Decline the invitation
     *
     * @param array<string, mixed> $invitation Invitation data
     * @param string $token The validation token
     * @return void
     */
    private function declineInvitation(array $invitation, string $token): void
    {
        $invitationId = (int) $invitation['invitation_id'];
        $teamId = (int) $invitation['team_id'];

        // Update the invitation status
        $this->invitationRepo->updateValidationStatus($invitationId, 'declined');

        // Cancel the entire team since one member declined
        $this->teamRepo->updateStatus($teamId, 'cancelled');

        // Refresh invitation data
        $invitation = $this->invitationRepo->findByToken($token);

        $this->render('events/teamInvitationResultView', [
            'invitation' => $invitation,
            'alreadyProcessed' => false,
            'status' => 'declined',
            'teamCancelled' => true
        ]);
    }

    /**
     * Register all confirmed team members to the event
     *
     * @param int $teamId The team identifier
     * @param int $eventId The event identifier
     * @return void
     */
    private function registerTeamMembers(int $teamId, int $eventId): void
    {
        $members = $this->invitationRepo->getConfirmedMembers($teamId);

        foreach ($members as $member) {
            if (!empty($member['user_id'])) {
                $userId = (int) $member['user_id'];
                
                // Check if not already registered individually
                if (!$this->registrationRepo->isUserRegistered($eventId, $userId)) {
                    $this->registrationRepo->registerUserWithTeam($eventId, $userId, $teamId);
                }
            }
        }
    }
}

