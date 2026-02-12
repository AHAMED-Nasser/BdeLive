<?php

declare(strict_types=1);

namespace App\Modules\Repositories;

use PDO;
use App\Core\Database;

/**
 * EventRegistrationRepository - Event Registration Data Access
 *
 * Manages event registration data in the EVENT_REGISTRATIONS table.
 * Handles user registration and unregistration for events.
 *
 * Features:
 * - Check if user is registered to an event
 * - Register user to an event
 * - Unregister user from an event
 *
 * @package BdeLive\Repositories
 * @version 1.0.0
 * @author BdeLive - Group 8
 */
class EventRegistrationRepository
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
     * Check if a user is registered for an event
     *
     * Queries the EVENT_REGISTRATIONS table to verify if a registration exists.
     *
     * @param int $eventId The event identifier
     * @param int $userId The user identifier
     * @return bool True if user is registered, false otherwise
     */
    public function isUserRegistered(int $eventId, int $userId): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM EVENT_REGISTRATIONS WHERE event_id = ? AND user_id = ?');
        $stmt->execute([$eventId, $userId]);
        return $stmt->fetchColumn() !== false;
    }

    /**
     * Register a user to an event
     *
     * Creates a new registration record with "Confirmé" status.
     * Does not check for duplicates - use isUserRegistered() first.
     *
     * @param int $eventId The event identifier
     * @param int $userId The user identifier
     * @return bool True if registration successful, false otherwise
     */
    public function registerUser(int $eventId, int $userId): bool
    {
        $stmt = $this->pdo->prepare('INSERT INTO EVENT_REGISTRATIONS (event_id, user_id, registration_status) VALUES (?, ?, ?)');
        return $stmt->execute([$eventId, $userId, 'Confirmé']);
    }

    /**
     * Unregister a user from an event
     *
     * Deletes the registration record from the EVENT_REGISTRATIONS table.
     *
     * @param int $eventId The event identifier
     * @param int $userId The user identifier
     * @return bool True if unregistration successful, false otherwise
     */
    public function unregisterUser(int $eventId, int $userId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM EVENT_REGISTRATIONS WHERE event_id = ? AND user_id = ?');
        return $stmt->execute([$eventId, $userId]);
    }

    /**
     * Delete all registrations for an event
     *
     * Used when event type or team size changes to clear all existing registrations.
     *
     * @param int $eventId The event identifier
     * @return int Number of registrations deleted
     */
    public function deleteRegistrationsByEvent(int $eventId): int
    {
        try {
            $stmt = $this->pdo->prepare('DELETE FROM EVENT_REGISTRATIONS WHERE event_id = ?');
            $stmt->execute([$eventId]);
            return $stmt->rowCount();
        } catch (\PDOException $e) {
            error_log('EventRegistrationRepository::deleteRegistrationsByEvent - ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get team ID for a user in a specific event
     *
     * Checks if the user is part of a team for the given event.
     *
     * @param int $userId The user identifier
     * @param int $eventId The event identifier
     * @return int|null Team ID if user is in a team, null otherwise
     */
    public function getTeamIdByUserAndEvent(int $userId, int $eventId): ?int
    {
        $stmt = $this->pdo->prepare(
            'SELECT team_id FROM EVENT_REGISTRATIONS WHERE user_id = ? AND event_id = ? AND team_id IS NOT NULL'
        );
        $stmt->execute([$userId, $eventId]);
        $result = $stmt->fetchColumn();
        return $result !== false ? (int) $result : null;
    }

    /**
     * Delete all registrations for a specific team
     *
     * Removes all member registrations associated with a team.
     *
     * @param int $teamId The team identifier
     * @return int Number of registrations deleted
     */
    public function deleteRegistrationsByTeam(int $teamId): int
    {
        try {
            $stmt = $this->pdo->prepare('DELETE FROM EVENT_REGISTRATIONS WHERE team_id = ?');
            $stmt->execute([$teamId]);
            return $stmt->rowCount();
        } catch (\PDOException $e) {
            error_log('EventRegistrationRepository::deleteRegistrationsByTeam - ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Retrieves detailed information about users registered for a specific event.
     *
     * This method joins the USERS table with the EVENT_REGISTRATIONS table to provide
     * a list of participant names, statuses, and contact information.
     *
     * @param int $eventId The unique identifier of the event.
     * @return array<int, array{first_name: string, last_name: string, user_status: string, email: string}>
     * An indexed array of associative arrays containing user details.
     */
    public function getRegisteredUsersDetails(int $eventId): array
    {
        $sql = 'SELECT u.first_name, u.last_name, u.user_status
                FROM EVENT_REGISTRATIONS er
                JOIN USERS u ON er.user_id = u.user_id
                WHERE er.event_id = ?
                ORDER BY u.last_name ASC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$eventId]);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $results ?: [];
    }

    /**
     * Get registrations by event ID
     *
     * @param int $eventId The event identifier
     * @return array<int, array{firstname: string, lastname: string, email: string, registration_date: string}>
     */
    public function getRegistrationsByEventId(int $eventId): array
    {
        $sql = "SELECT u.firstname, u.lastname, u.email, er.registration_date 
            FROM event_registrations er
            JOIN users u ON er.user_id = u.id
            WHERE er.event_id = :event_id
            ORDER BY er.registration_date DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['event_id' => $eventId]);
        /** @var array<int, array{firstname: string, lastname: string, email: string, registration_date: string}> */
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Register a user to an event as part of a team
     *
     * Creates a new registration record with team association.
     *
     * @param int $eventId The event identifier
     * @param int $userId The user identifier
     * @param int $teamId The team identifier
     * @return bool True if registration successful, false otherwise
     */
    public function registerUserWithTeam(int $eventId, int $userId, int $teamId): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO EVENT_REGISTRATIONS (event_id, user_id, registration_status, team_id) 
             VALUES (?, ?, ?, ?)'
        );
        return $stmt->execute([$eventId, $userId, 'Confirmé', $teamId]);
    }

    /**
     * Get registered users grouped by team for an event
     *
     * Returns individual registrations (team_id IS NULL) followed by
     * team registrations grouped by team number.
     *
     * @param int $eventId The event identifier
     * @return array<int, array<string, mixed>> Array of registrations with team info
     */
    public function getRegisteredUsersWithTeams(int $eventId): array
    {
        $sql = 'SELECT u.first_name, u.last_name, u.user_status, u.email,
                       er.team_id, et.team_number
                FROM EVENT_REGISTRATIONS er
                JOIN USERS u ON er.user_id = u.user_id
                LEFT JOIN EVENT_TEAMS et ON er.team_id = et.team_id
                WHERE er.event_id = ?
                ORDER BY et.team_number ASC NULLS FIRST, u.last_name ASC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$eventId]);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $results ?: [];
    }

    /**
     * Get registered users grouped by team for PDF export
     *
     * Returns an array with 'individual' and 'teams' keys
     *
     * @param int $eventId The event identifier
     * @return array{individual: array<int, array<string, mixed>>, teams: array<int, array<string, mixed>>}
     */
    public function getRegisteredUsersGroupedForPdf(int $eventId): array
    {
        $sql = 'SELECT u.first_name, u.last_name, u.user_status, u.email,
                       er.team_id, et.team_number
                FROM EVENT_REGISTRATIONS er
                JOIN USERS u ON er.user_id = u.user_id
                LEFT JOIN EVENT_TEAMS et ON er.team_id = et.team_id
                WHERE er.event_id = ?
                ORDER BY (et.team_number IS NULL), et.team_number ASC, u.last_name ASC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$eventId]);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        /** @var array<int, array<string, mixed>> $individual */
        $individual = [];
        /** @var array<int, array<int, array<string, mixed>>> $teams */
        $teams = [];

        foreach ($results as $row) {
            /** @var array<string, mixed> $row */
            $teamId = $row['team_id'] ?? null;
            if ($teamId === null || $teamId === '') {
                $individual[] = $row;
            } else {
                $teamNumber = (int) ($row['team_number'] ?? 0);
                if ($teamNumber > 0) {
                    if (!isset($teams[$teamNumber])) {
                        $teams[$teamNumber] = [];
                    }
                    $teams[$teamNumber][] = $row;
                } else {
                    // Si team_id existe mais team_number est NULL, traiter comme individuel
                    $individual[] = $row;
                }
            }
        }

        /** @var array{individual: array<int, array<string, mixed>>, teams: array<int, array<string, mixed>>} */
        return [
            'individual' => $individual,
            'teams' => $teams
        ];
    }
    /**
     * Get individual registrations for an event
     *
     * Retrieves the list of users registered individually to an event.
     * Includes first name, last name, promotion (status), and registration date.
     * Join with USERS table to get user details.
     *
     * @param int $eventId The event identifier
     * @return array<int, array{first_name: string, last_name: string, promotion: string, registration_date: string}>
     */
    public function getIndividualRegistrantsForEvent(int $eventId): array
    {
        $sql = "SELECT u.first_name, u.last_name, u.user_status as promotion, er.registration_date
                FROM EVENT_REGISTRATIONS er
                JOIN USERS u ON er.user_id = u.user_id
                WHERE er.event_id = :event_id
                -- Ensure we only get individual registrations or where team logic doesn't apply
                -- Based on the request 'unique individual events', we assume all registrations appearing
                -- in this context are valid. If mixed, we might need 'AND er.team_id IS NULL'.
                -- For now, fetching all for the event as requested for 'individual inscription events'.
                ORDER BY er.registration_date DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['event_id' => $eventId]);

        /** @var array<int, array{first_name: string, last_name: string, promotion: string, registration_date: string}> */
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}

\class_alias(__NAMESPACE__ . '\\EventRegistrationRepository', 'EventRegistrationRepository');
