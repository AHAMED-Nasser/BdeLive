<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Events;

use App\Modules\Controllers\AuthenticatedController;
use App\Modules\Repositories\EventRepository;
use App\Modules\Repositories\EventTeamRepository;
use App\Modules\Repositories\EventTeamInvitationRepository;
use App\Modules\Entities\Event;
use App\Core\Database;
use App\Config\Mailer;

/**
 * GroupRegistrationController - Group Registration Management
 *
 * Handles user group registration for events with group mode enabled.
 * Allows users to create teams and invite members via email.
 *
 * Refactored to use Data Mapper pattern with Event entities.
 *
 * Features:
 * - Display group registration form
 * - Validate member emails
 * - Create teams and send invitations
 * - Track team creation status
 *
 * @package BdeLive\Controllers\Events
 * @version 2.0.0 - Data Mapper refactoring
 * @author BDELIVE - Group 8
 *
 * @see AuthenticatedController For authentication requirements
 * @see EventTeamRepository For team database operations
 * @see EventTeamInvitationRepository For invitation operations
 */
class GroupRegistrationController extends AuthenticatedController
{
    private EventRepository $eventRepository;

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
     * Constructor - Handle group registration actions
     *
     * Supports two actions:
     * - GET: Display the group registration form
     * - POST with action=submitGroup: Process the group registration
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->eventRepository = new EventRepository(Database::getInstance()->getConnection());
        $this->teamRepo = new EventTeamRepository();
        $this->invitationRepo = new EventTeamInvitationRepository();

        $eventId = (int) $this->request->get('event_id', 0);

        if ($eventId <= 0) {
            $this->redirectWithError('index.php?page=event', 'ID événement invalide');
        }

        $event = $this->eventRepository->findById($eventId);

        if (!$event) {
            $this->redirectWithError('index.php?page=event', 'Événement introuvable');
        }

        // Check if this is a group event
        if (!$event->isGroupEvent()) {
            $this->redirectWithError(
                'index.php?page=showEvent&id=' . $eventId,
                'Cet événement ne permet pas les inscriptions en groupe'
            );
        }

        $action = $this->request->post('action', '');

        if ($this->request->isPost() && $action === 'submitGroup') {
            $this->processGroupRegistration($event);
        } else {
            $this->displayForm($event);
        }
    }

    /**
     * Display the group registration form
     *
     * @param Event $event Event entity
     * @return void
     */
    private function displayForm(Event $event): void
    {
        $user = $this->auth->getUser();

        // Check if user is already in a team for this event
        $eventId = $event->getId();
        if ($user && $eventId !== null && $this->teamRepo->isUserInAnyTeam($eventId, (int) $user['user_id'])) {
            $this->redirectWithError(
                'index.php?page=showEvent&id=' . $eventId,
                'Vous êtes déjà inscrit à un groupe pour cet événement'
            );
        }

        // Get existing teams created by this user (if any pending)
        $userTeams = ($user && $eventId !== null) ? $this->teamRepo->getUserTeamsForEvent(
            $eventId,
            (int) $user['user_id']
        ) : [];

        $this->render('events/groupRegistrationView', [
            'event' => $event,
            'teamSize' => $event->getTeamSize(),
            'userTeams' => $userTeams
        ]);
    }

    /**
     * Process the group registration submission
     *
     * Creates a team and sends invitations to all specified email addresses.
     *
     * @param Event $event Event entity
     * @return void
     */
    private function processGroupRegistration(Event $event): void
    {
        $user = $this->auth->getUser();

        if (!$user || !isset($user['user_id'])) {
            $this->redirectWithError(
                'index.php?page=groupRegistration&event_id=' . $event->getId(),
                'Utilisateur non authentifié'
            );
        }

        $userId = (int) $user['user_id'];
        $eventId = $event->getId() ?? 0;
        $teamSize = $event->getTeamSize();

        // Check if user is already in a team
        if ($this->teamRepo->isUserInAnyTeam($eventId, $userId)) {
            $this->redirectWithError(
                'index.php?page=showEvent&id=' . $eventId,
                'Vous êtes déjà inscrit à un groupe pour cet événement'
            );
        }

        // Get member emails from form
        $memberEmails = $this->request->post('member_emails', []);

        if (!is_array($memberEmails)) {
            $memberEmails = [];
        }

        // Filter out empty emails and the creator's email
        $memberEmails = array_filter($memberEmails, function ($email) use ($user) {
            return !empty(trim($email)) && strtolower(trim($email)) !== strtolower($user['email']);
        });
        $memberEmails = array_map('trim', $memberEmails);
        $memberEmails = array_unique($memberEmails);

        // Validate email count (must be teamSize - 1 because creator is included)
        $requiredMembers = $teamSize - 1;

        if (count($memberEmails) !== $requiredMembers) {
            $this->redirectWithError(
                'index.php?page=groupRegistration&event_id=' . $eventId,
                "Vous devez inviter exactement {$requiredMembers} membre(s) pour former un groupe de {$teamSize}"
            );
        }

        // Validate email formats
        foreach ($memberEmails as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->redirectWithError(
                    'index.php?page=groupRegistration&event_id=' . $eventId,
                    "L'adresse email '{$email}' n'est pas valide"
                );
            }
        }

        // Create the team
        $teamId = $this->teamRepo->createTeam($eventId, $userId);

        if ($teamId === null) {
            $this->redirectWithError(
                'index.php?page=groupRegistration&event_id=' . $eventId,
                'Erreur lors de la création du groupe'
            );
        }

        // Add the creator as first member (auto-confirmed)

        $creatorToken = $this->invitationRepo->createInvitation($teamId, $user['email'], $userId);
        if ($creatorToken) {
            $this->invitationRepo->updateValidationStatus(
                $this->getInvitationIdByToken($creatorToken),
                'confirmed',
                $userId
            );
        }

        // Send invitations to other members
        $mailer = new Mailer();
        $team = $this->teamRepo->findById($teamId);
        $invitationsSent = 0;

        foreach ($memberEmails as $email) {
            // Check if this email corresponds to a registered user
            $memberUserId = $this->invitationRepo->getUserIdByEmail($email);

            // Create the invitation
            $token = $this->invitationRepo->createInvitation($teamId, $email, $memberUserId);

            if ($token) {
                // Send the invitation email
                $sent = $mailer->sendTeamInvitationEmail(
                    $email,
                    $email, // Use email as name if user not registered
                    $token,
                    $event->getName(),
                    $user['first_name'] . ' ' . $user['last_name'],
                    $team ? (int) $team['team_number'] : 1,
                    $teamSize
                );

                if ($sent) {
                    $invitationsSent++;
                }
            }
        }

        if ($invitationsSent === count($memberEmails)) {
            $this->redirectWithSuccess(
                'index.php?page=showEvent&id=' . $eventId,
                "Groupe créé avec succès ! {$invitationsSent} invitation(s) envoyée(s). " .
                "Le groupe sera validé quand tous les membres auront confirmé leur participation."
            );
        } else {
            $this->setWarning(
                "Groupe créé mais certaines invitations n'ont pas pu être envoyées. " .
                "{$invitationsSent}/" . count($memberEmails) . " envoyée(s)."
            );
            $this->redirect('index.php?page=showEvent&id=' . $eventId);
        }
    }

    /**
     * Get invitation ID by token (helper method)
     *
     * @param string $token The validation token
     * @return int The invitation ID
     */
    private function getInvitationIdByToken(string $token): int
    {
        $invitation = $this->invitationRepo->findByToken($token);
        return $invitation ? (int) $invitation['invitation_id'] : 0;
    }
}
