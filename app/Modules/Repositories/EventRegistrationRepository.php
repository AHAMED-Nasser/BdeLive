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
     * Includes user ID, first name, last name, promotion (status), and registration date.
     * Join with USERS table to get user details.
     *
     * @param int $eventId The event identifier
     * @return array<int, array{user_id: int, first_name: string, last_name: string, promotion: string, registration_date: string}>
     */
    public function getIndividualRegistrantsForEvent(int $eventId): array
    {
        $sql = "SELECT u.user_id, u.first_name, u.last_name, u.user_status as promotion, er.registration_date
                FROM EVENT_REGISTRATIONS er
                JOIN USERS u ON er.user_id = u.user_id
                WHERE er.event_id = :event_id
                ORDER BY er.registration_date DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['event_id' => $eventId]);

        /** @var array<int, array{user_id: int, first_name: string, last_name: string, promotion: string, registration_date: string}> */
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get group registrants for an event, grouped by team number
     *
     * Retrieves the list of users registered in teams for a group event.
     * Results are grouped by team_number for display with group headers.
     * Includes first name, last name, promotion (status), and registration date.
     *
     * @param int $eventId The event identifier
     * @return array<int, array<int, array<string, mixed>>>
     */
    public function getGroupRegistrantsForEvent(int $eventId): array
    {
        $sql = "SELECT u.first_name, u.last_name, u.user_status AS promotion,
                       er.registration_date, et.team_number
                FROM EVENT_REGISTRATIONS er
                JOIN USERS u ON er.user_id = u.user_id
                LEFT JOIN EVENT_TEAMS et ON er.team_id = et.team_id
                WHERE er.event_id = :event_id
                ORDER BY et.team_number ASC, u.last_name ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['event_id' => $eventId]);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        /** @var array<int, array<int, array{first_name: string, last_name: string, promotion: string, registration_date: string, team_number: int}>> $teams */
        $teams = [];

        foreach ($results as $row) {
            /** @var array<string, mixed> $row */
            $teamNumber = (int) ($row['team_number'] ?? 0);
            if ($teamNumber > 0) {
                if (!isset($teams[$teamNumber])) {
                    $teams[$teamNumber] = [];
                }
                $teams[$teamNumber][] = $row;
            }
        }

        return $teams;
    }

    /**
     * Batch unregister multiple users from an event
     *
     * Deletes registration records for the given user IDs from the EVENT_REGISTRATIONS table.
     * Uses parameterized IN clause for safe batch deletion.
     *
     * @param int $eventId The event identifier
     * @param array<int, int> $userIds Array of user identifiers to unregister
     * @return int Number of registrations successfully deleted
     */
    public function unregisterUsers(int $eventId, array $userIds): int
    {
        if (empty($userIds)) {
            return 0;
        }

        try {
            $placeholders = implode(',', array_fill(0, count($userIds), '?'));
            $sql = "DELETE FROM EVENT_REGISTRATIONS WHERE event_id = ? AND user_id IN ($placeholders)";

            $stmt = $this->pdo->prepare($sql);
            $params = array_merge([$eventId], array_values($userIds));
            $stmt->execute($params);

            return $stmt->rowCount();
        } catch (\PDOException $e) {
            error_log('EventRegistrationRepository::unregisterUsers - ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Search users not registered to a specific event
     *
     * Returns users matching the search query who are not yet registered
     * for the given event. Automatically detects search mode:
     * - Numeric query: searches by exact user_id
     * - Text query: searches by first_name or last_name using LIKE
     *
     * Uses LEFT JOIN anti-pattern instead of NOT IN for reliability
     * with native prepared statements (EMULATE_PREPARES = false).
     *
     * @param int $eventId The event identifier
     * @param string $query The search query (name or user_id)
     * @param int $limit Maximum number of results (default 20)
     * @return array<int, array{user_id: int, first_name: string, last_name: string, user_status: string}> Matching users
     */
    public function searchUsersNotRegistered(int $eventId, string $query, int $limit = 20): array
    {
        $query = trim($query);

        if ($query === '') {
            return [];
        }

        try {
            $isNumeric = ctype_digit($query);

            // LEFT JOIN anti-pattern: exclude users already registered for this event
            $sql = "SELECT u.user_id, u.first_name, u.last_name, u.user_status
                    FROM USERS u
                    LEFT JOIN EVENT_REGISTRATIONS er
                        ON er.user_id = u.user_id AND er.event_id = :event_id
                    WHERE er.user_id IS NULL
                    AND u.is_blocked = 0";

            if ($isNumeric) {
                $sql .= " AND u.user_id = :search_id";
            } else {
                $sql .= " AND (u.first_name LIKE :search_fn OR u.last_name LIKE :search_ln)";
            }

            $sql .= " ORDER BY u.last_name ASC, u.first_name ASC LIMIT " . (int) $limit;

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':event_id', $eventId, \PDO::PARAM_INT);

            if ($isNumeric) {
                $stmt->bindValue(':search_id', (int) $query, \PDO::PARAM_INT);
            } else {
                $searchTerm = '%' . $query . '%';
                $stmt->bindValue(':search_fn', $searchTerm, \PDO::PARAM_STR);
                $stmt->bindValue(':search_ln', $searchTerm, \PDO::PARAM_STR);
            }

            $stmt->execute();

            /** @var array<int, array{user_id: int, first_name: string, last_name: string, user_status: string}> */
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException $e) {
            error_log('EventRegistrationRepository::searchUsersNotRegistered - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Register multiple users to an event in a single operation
     *
     * Inserts registration records for the given user IDs. Uses INSERT IGNORE
     * to gracefully skip users who are already registered.
     *
     * @param int $eventId The event identifier
     * @param array<int, int> $userIds Array of user identifiers to register
     * @return int Number of registrations successfully created
     */
    public function registerUsers(int $eventId, array $userIds): int
    {
        if (empty($userIds)) {
            return 0;
        }

        try {
            $inserted = 0;

            foreach ($userIds as $userId) {
                $stmt = $this->pdo->prepare(
                    'INSERT IGNORE INTO EVENT_REGISTRATIONS (event_id, user_id, registration_status) VALUES (?, ?, ?)'
                );
                if ($stmt->execute([$eventId, (int) $userId, 'Confirmé'])) {
                    $inserted += $stmt->rowCount();
                }
            }

            return $inserted;
        } catch (\PDOException $e) {
            error_log('EventRegistrationRepository::registerUsers - ' . $e->getMessage());
            return 0;
        }
    }
}

\class_alias(__NAMESPACE__ . '\\EventRegistrationRepository', 'EventRegistrationRepository');
