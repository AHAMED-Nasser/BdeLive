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
 * @author BdeLive Team
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

        $stmt = $this -> pdo -> prepare($sql);
        $stmt -> execute([$eventId]);

        $results = $stmt -> fetchAll(PDO::FETCH_ASSOC);
        return $results ?: [];
    }

    // get inscription user by event ID
    /**
     * Get all registrations for a specific event
     *
     * @param int $eventId The event identifier
     * @return array<int, array<string, mixed>> Array of registrations with user details
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
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}

\class_alias(__NAMESPACE__ . '\\EventRegistrationRepository', 'EventRegistrationRepository');
