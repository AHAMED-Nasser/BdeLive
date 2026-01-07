<?php

declare(strict_types=1);

namespace App\Modules\Repositories;

use PDO;
use PDOException;
use App\Core\Database;

/**
 * EventTeamRepository - Event Team Data Access
 *
 * Manages team data in the EVENT_TEAMS table for group registrations.
 * Handles team creation, status updates, and team retrieval.
 *
 * Features:
 * - Create new teams for group events
 * - Update team status (pending, confirmed, cancelled)
 * - Get teams by event or by creator
 * - Count confirmed teams for an event
 *
 * @package BdeLive\Repositories
 * @version 1.0.0
 * @author BdeLive Team
 */
class EventTeamRepository
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
     * Create a new team for a group event
     *
     * @param int $eventId The event identifier
     * @param int $creatorUserId The user who creates the team
     * @return int|null The created team ID or null on failure
     */
    public function createTeam(int $eventId, int $creatorUserId): ?int
    {
        try {
            // Get next team number for this event
            $teamNumber = $this->getNextTeamNumber($eventId);

            $stmt = $this->pdo->prepare(
                'INSERT INTO EVENT_TEAMS (event_id, team_number, creator_user_id, status) 
                 VALUES (:event_id, :team_number, :creator_user_id, :status)'
            );
            
            $stmt->execute([
                ':event_id' => $eventId,
                ':team_number' => $teamNumber,
                ':creator_user_id' => $creatorUserId,
                ':status' => 'pending'
            ]);

            return (int) $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log('EventTeamRepository::createTeam - ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get the next team number for an event
     *
     * @param int $eventId The event identifier
     * @return int The next team number
     */
    private function getNextTeamNumber(int $eventId): int
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT COALESCE(MAX(team_number), 0) + 1 FROM EVENT_TEAMS WHERE event_id = :event_id'
            );
            $stmt->execute([':event_id' => $eventId]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('EventTeamRepository::getNextTeamNumber - ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Find a team by its ID
     *
     * @param int $teamId The team identifier
     * @return array<string, mixed>|null Team data or null if not found
     */
    public function findById(int $teamId): ?array
    {
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM EVENT_TEAMS WHERE team_id = :team_id');
            $stmt->execute([':team_id' => $teamId]);
            $team = $stmt->fetch(PDO::FETCH_ASSOC);
            return $team ?: null;
        } catch (PDOException $e) {
            error_log('EventTeamRepository::findById - ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update team status
     *
     * @param int $teamId The team identifier
     * @param string $status New status (pending, confirmed, cancelled)
     * @return bool True if update successful
     */
    public function updateStatus(int $teamId, string $status): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                'UPDATE EVENT_TEAMS SET status = :status WHERE team_id = :team_id'
            );
            return $stmt->execute([
                ':team_id' => $teamId,
                ':status' => $status
            ]);
        } catch (PDOException $e) {
            error_log('EventTeamRepository::updateStatus - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all teams for an event
     *
     * @param int $eventId The event identifier
     * @param string|null $status Filter by status (null for all)
     * @return array<int, array<string, mixed>> List of teams
     */
    public function getTeamsByEvent(int $eventId, ?string $status = null): array
    {
        try {
            $sql = 'SELECT et.*, u.first_name, u.last_name, u.email 
                    FROM EVENT_TEAMS et
                    JOIN USERS u ON et.creator_user_id = u.user_id
                    WHERE et.event_id = :event_id';
            
            if ($status !== null) {
                $sql .= ' AND et.status = :status';
            }
            
            $sql .= ' ORDER BY et.team_number ASC';

            $stmt = $this->pdo->prepare($sql);
            $params = [':event_id' => $eventId];
            
            if ($status !== null) {
                $params[':status'] = $status;
            }
            
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log('EventTeamRepository::getTeamsByEvent - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get teams created by a specific user for an event
     *
     * @param int $eventId The event identifier
     * @param int $userId The user identifier
     * @return array<int, array<string, mixed>> List of teams
     */
    public function getUserTeamsForEvent(int $eventId, int $userId): array
    {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT * FROM EVENT_TEAMS 
                 WHERE event_id = :event_id AND creator_user_id = :user_id
                 ORDER BY team_number ASC'
            );
            $stmt->execute([
                ':event_id' => $eventId,
                ':user_id' => $userId
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log('EventTeamRepository::getUserTeamsForEvent - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Count confirmed teams for an event
     *
     * @param int $eventId The event identifier
     * @return int Number of confirmed teams
     */
    public function countConfirmedTeams(int $eventId): int
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) FROM EVENT_TEAMS WHERE event_id = :event_id AND status = 'confirmed'"
            );
            $stmt->execute([':event_id' => $eventId]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('EventTeamRepository::countConfirmedTeams - ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Delete a team (also cascades to invitations due to FK)
     *
     * @param int $teamId The team identifier
     * @return bool True if deletion successful
     */
    public function deleteTeam(int $teamId): bool
    {
        try {
            $stmt = $this->pdo->prepare('DELETE FROM EVENT_TEAMS WHERE team_id = :team_id');
            return $stmt->execute([':team_id' => $teamId]);
        } catch (PDOException $e) {
            error_log('EventTeamRepository::deleteTeam - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if a user is already part of any team for an event
     *
     * @param int $eventId The event identifier
     * @param int $userId The user identifier
     * @return bool True if user is already in a team
     */
    public function isUserInAnyTeam(int $eventId, int $userId): bool
    {
        try {
            $sql = "SELECT COUNT(*) FROM EVENT_TEAM_INVITATIONS eti
                    JOIN EVENT_TEAMS et ON eti.team_id = et.team_id
                    WHERE et.event_id = :event_id 
                    AND eti.user_id = :user_id 
                    AND eti.validation_status != 'declined'";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':event_id' => $eventId,
                ':user_id' => $userId
            ]);
            
            return (int) $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('EventTeamRepository::isUserInAnyTeam - ' . $e->getMessage());
            return false;
        }
    }
}

\class_alias(__NAMESPACE__ . '\\EventTeamRepository', 'EventTeamRepository');

