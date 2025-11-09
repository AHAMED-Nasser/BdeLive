<?php

declare(strict_types=1);

namespace App\Modules\Models\Admin;

use PDO;
use PDOException;
use DateTime;
use App\Core\Database;

class EventCreationModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }


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
     * Delete an event by its ID
     * @param int $eventId The event ID to delete
     * @return bool True if deletion successful, false otherwise
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
