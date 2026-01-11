<?php

declare(strict_types=1);

namespace App\Modules\Repositories;

use PDO;
use PDOException;
use App\Core\Database;

/**
 * EventTeamInvitationRepository - Team Invitation Data Access
 *
 * Manages invitation data in the EVENT_TEAM_INVITATIONS table.
 * Handles invitation creation, validation, and status management.
 *
 * Features:
 * - Create invitations with unique validation tokens
 * - Validate invitations via token
 * - Check invitation status
 * - Get all invitations for a team
 *
 * @package BdeLive\Repositories
 * @version 1.0.0
 * @author BdeLive Team
 */
class EventTeamInvitationRepository
{
    /**
     * PDO database connection instance
     *
     * @var PDO
     */
    private PDO $pdo;

    /**
     * Constructor - Initialize database connection
     *
     * Retrieves the database connection from the Database singleton.
     *
     * @return void
     */
    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Create a new invitation for a team member
     *
     * @param int $teamId The team identifier
     * @param string $email The invitee's email address
     * @param int|null $userId The user ID if already registered on the platform
     * @return string|null The validation token or null on failure
     */
    public function createInvitation(int $teamId, string $email, ?int $userId = null): ?string
    {
        try {
            // Generate unique validation token
            $token = bin2hex(random_bytes(32));

            $stmt = $this->pdo->prepare(
                'INSERT INTO EVENT_TEAM_INVITATIONS (team_id, email, user_id, validation_token, validation_status) 
                 VALUES (:team_id, :email, :user_id, :validation_token, :validation_status)'
            );

            $stmt->execute([
                ':team_id' => $teamId,
                ':email' => $email,
                ':user_id' => $userId,
                ':validation_token' => $token,
                ':validation_status' => 'pending'
            ]);

            return $token;
        } catch (PDOException $e) {
            error_log('EventTeamInvitationRepository::createInvitation - ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Find an invitation by its validation token
     *
     * @param string $token The validation token
     * @return array<string, mixed>|null Invitation data or null if not found
     */
    public function findByToken(string $token): ?array
    {
        try {
            $sql = 'SELECT eti.*, et.event_id, et.team_number, et.status as team_status,
                           e.event_name, e.event_date, e.event_time
                    FROM EVENT_TEAM_INVITATIONS eti
                    JOIN EVENT_TEAMS et ON eti.team_id = et.team_id
                    JOIN EVENTS e ON et.event_id = e.event_id
                    WHERE eti.validation_token = :token';

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':token' => $token]);
            $invitation = $stmt->fetch(PDO::FETCH_ASSOC);
            return $invitation ?: null;
        } catch (PDOException $e) {
            error_log('EventTeamInvitationRepository::findByToken - ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Find an invitation by ID
     *
     * @param int $invitationId The invitation identifier
     * @return array<string, mixed>|null Invitation data or null if not found
     */
    public function findById(int $invitationId): ?array
    {
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM EVENT_TEAM_INVITATIONS WHERE invitation_id = :id');
            $stmt->execute([':id' => $invitationId]);
            $invitation = $stmt->fetch(PDO::FETCH_ASSOC);
            return $invitation ?: null;
        } catch (PDOException $e) {
            error_log('EventTeamInvitationRepository::findById - ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update invitation validation status
     *
     * @param int $invitationId The invitation identifier
     * @param string $status New status (pending, confirmed, declined)
     * @param int|null $userId Optional user ID to link if user just registered
     * @return bool True if update successful
     */
    public function updateValidationStatus(int $invitationId, string $status, ?int $userId = null): bool
    {
        try {
            $sql = 'UPDATE EVENT_TEAM_INVITATIONS 
                    SET validation_status = :status, validated_at = NOW()';

            if ($userId !== null) {
                $sql .= ', user_id = :user_id';
            }

            $sql .= ' WHERE invitation_id = :id';

            $stmt = $this->pdo->prepare($sql);
            $params = [
                ':status' => $status,
                ':id' => $invitationId
            ];

            if ($userId !== null) {
                $params[':user_id'] = $userId;
            }

            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log('EventTeamInvitationRepository::updateValidationStatus - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all invitations for a team
     *
     * @param int $teamId The team identifier
     * @return array<int, array<string, mixed>> List of invitations
     */
    public function getInvitationsByTeam(int $teamId): array
    {
        try {
            $sql = 'SELECT eti.*, u.first_name, u.last_name 
                    FROM EVENT_TEAM_INVITATIONS eti
                    LEFT JOIN USERS u ON eti.user_id = u.user_id
                    WHERE eti.team_id = :team_id
                    ORDER BY eti.invited_at ASC';

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':team_id' => $teamId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log('EventTeamInvitationRepository::getInvitationsByTeam - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if all invitations for a team are confirmed
     *
     * @param int $teamId The team identifier
     * @return bool True if all members confirmed
     */
    public function areAllInvitationsConfirmed(int $teamId): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) FROM EVENT_TEAM_INVITATIONS 
                 WHERE team_id = :team_id AND validation_status != 'confirmed'"
            );
            $stmt->execute([':team_id' => $teamId]);
            return (int) $stmt->fetchColumn() === 0;
        } catch (PDOException $e) {
            error_log('EventTeamInvitationRepository::areAllInvitationsConfirmed - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Count pending invitations for a team
     *
     * @param int $teamId The team identifier
     * @return int Number of pending invitations
     */
    public function countPendingInvitations(int $teamId): int
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) FROM EVENT_TEAM_INVITATIONS 
                 WHERE team_id = :team_id AND validation_status = 'pending'"
            );
            $stmt->execute([':team_id' => $teamId]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('EventTeamInvitationRepository::countPendingInvitations - ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Check if an email is already invited to a team
     *
     * @param int $teamId The team identifier
     * @param string $email The email to check
     * @return bool True if already invited
     */
    public function isEmailInvited(int $teamId, string $email): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT COUNT(*) FROM EVENT_TEAM_INVITATIONS 
                 WHERE team_id = :team_id AND email = :email'
            );
            $stmt->execute([
                ':team_id' => $teamId,
                ':email' => $email
            ]);
            return (int) $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('EventTeamInvitationRepository::isEmailInvited - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user ID by email from USERS table
     *
     * @param string $email The email to search
     * @return int|null User ID or null if not found
     */
    public function getUserIdByEmail(string $email): ?int
    {
        try {
            $stmt = $this->pdo->prepare('SELECT user_id FROM USERS WHERE email = :email');
            $stmt->execute([':email' => $email]);
            $result = $stmt->fetchColumn();
            return $result !== false ? (int) $result : null;
        } catch (PDOException $e) {
            error_log('EventTeamInvitationRepository::getUserIdByEmail - ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete an invitation
     *
     * @param int $invitationId The invitation identifier
     * @return bool True if deletion successful
     */
    public function deleteInvitation(int $invitationId): bool
    {
        try {
            $stmt = $this->pdo->prepare('DELETE FROM EVENT_TEAM_INVITATIONS WHERE invitation_id = :id');
            return $stmt->execute([':id' => $invitationId]);
        } catch (PDOException $e) {
            error_log('EventTeamInvitationRepository::deleteInvitation - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all confirmed members of a team with their details
     *
     * @param int $teamId The team identifier
     * @return array<int, array<string, mixed>> List of confirmed members
     */
    public function getConfirmedMembers(int $teamId): array
    {
        try {
            $sql = "SELECT eti.*, u.first_name, u.last_name, u.user_status 
                    FROM EVENT_TEAM_INVITATIONS eti
                    LEFT JOIN USERS u ON eti.user_id = u.user_id
                    WHERE eti.team_id = :team_id AND eti.validation_status = 'confirmed'
                    ORDER BY eti.validated_at ASC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':team_id' => $teamId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log('EventTeamInvitationRepository::getConfirmedMembers - ' . $e->getMessage());
            return [];
        }
    }
}

\class_alias(__NAMESPACE__ . '\\EventTeamInvitationRepository', 'EventTeamInvitationRepository');
