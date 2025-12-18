<?php

declare(strict_types=1);

namespace App\Modules\Models\Admin;

use PDO;
use PDOException;
use DateTime;
use App\Core\Database;

/**
 * EventCreationModel - Event Creation and Deletion
 *
 * Handles database operations for event management (admin functions).
 * Provides methods to insert and delete events from the EVENTS table.
 *
 * @package BdeLive\Models\Admin
 * @version 1.0.0
 * @author BdeLive Team
 *
 * @see EventRepository For event retrieval operations
 */
class EventCreationModel
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
     * @return void
     */
    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Insert a new event into the database
     *
     * Creates a new event record with all provided information including images.
     *
     * @param string $eventName Event title/name
     * @param DateTime $eventDate Event date
     * @param DateTime $eventTime Event time
     * @param string $eventLocation Event location/venue
     * @param string $eventTheme Event theme/category
     * @param string $statusParticipating Comma-separated allowed participant statuses
     * @param string $description Event description
     * @param string $images JSON string of image URLs from Cloudinary
     * @return bool True if insertion successful, false otherwise
     * @throws PDOException If database query fails
     */
    public function insertEvent(
        string $eventName,
        DateTime $eventDate,
        DateTime $eventTime,
        string $eventLocation,
        string $eventTheme,
        string $statusParticipating,
        string $description,
        string $images = ''
    ): bool {
        try {
            $query = "INSERT INTO EVENTS (event_name, event_date, event_time, event_location, event_theme, status_participating, description, images) VALUES (:event_name, :event_date, :event_time, :event_location, :event_theme, :status_participating, :description, :images)";
            $stmt = $this->pdo->prepare($query);
            return $stmt -> execute([
                ':event_name' => $eventName,
                ':event_date' => $eventDate->format('Y-m-d'),
                ':event_time' => $eventTime->format('H:i'),
                ':event_location' => $eventLocation,
                ':event_theme' => $eventTheme,
                ':status_participating' => $statusParticipating,
                ':description' => $description,
                ':images' => $images
            ]);
        } catch (PDOException $e) {
            error_log('EventCreationModel::insertEvent - ' . $e->getMessage());
            header('Location: index.php?page=createEvent');
            exit();
        }
    }

    /**
     * Update current event in database
     *
     * Image update excluded for the moment
     *
     * @param int $eventId Event ID to edit
     * @param string $eventName Event title/name
     * @param DateTime $eventDate Event date
     * @param DateTime $eventTime event time
     * @param string $eventLocation Event location
     * @param string $eventTheme Event theme
     * @param string $statusParticipating Event participating (BUT1, BUT2, ...)
     * @param string $description Event description
     * @return bool True if update success else false
     * @throws PDOException If the update fail
     */
    public function updateEvent(
        int $eventId,
        string $eventName,
        DateTime $eventDate,
        DateTime $eventTime,
        string $eventLocation,
        string $eventTheme,
        string $statusParticipating,
        string $description,
        string $images
    ): bool {

        try {
            $sql = "UPDATE EVENTS SET
            event_name = :event_name,
            event_date = :event_date,
            event_time = :event_time,
            event_location = :event_location,
            event_theme = :event_theme,
            status_participating = :status_participating,
            description = :description,
            images = :images
            WHERE event_id = :event_id";

            $stmt = $this->pdo->prepare($sql);

            return $stmt->execute([
                ':event_id' => $eventId,
                ':event_name' => $eventName,
                ':event_date' => $eventDate->format('Y-m-d'), // SQL format
                ':event_time' => $eventTime->format('H:i'), // SQL format
                ':event_location' => $eventLocation,
                ':event_theme' => $eventTheme,
                ':status_participating' => $statusParticipating,
                ':description' => $description,
                ':images' => $images
            ]);
        } catch (PDOException $e) {
            // En cas d'erreur, on log l'erreur et on retourne false
            error_log('EventCreationModel::updateEvent - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete an event by its ID
     *
     * Removes an event record from the EVENTS table.
     * This operation cannot be undone.
     *
     * @param int $eventId The event ID to delete
     * @return bool True if deletion successful, false otherwise
     * @throws PDOException If database query fails
     */
    public function deleteEvent(int $eventId): bool
    {
        try {
            $query = "DELETE FROM EVENTS WHERE event_id = :event_id";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('EventCreationModel::deleteEvent - ' . $e->getMessage());
            return false;
        }
    }
}

\class_alias(__NAMESPACE__ . '\\EventCreationModel', 'EventCreationModel');
