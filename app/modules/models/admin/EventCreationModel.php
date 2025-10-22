<?php

class EventCreationModel {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function isAdmin(int $userId): bool {
        try {
            $query = "SELECT role FROM users WHERE role='admin' AND user_id = :user_id";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result !== false;
        } catch (PDOException $e) {
            error_log('EventCreationModel::isAdmin - ' . $e->getMessage());
            return false;
        }
    }

    public function insertEvent(string $eventName,
                                DateTime $eventDate,
                                DateTime $eventTime,
                                string $eventLocation,
                                string $eventTheme,
                                string $statusParticipating,
                                string $description
    ) {
        try {
            $query = "INSERT INTO EVENTS (event_name, event_date, event_time, event_location, event_theme, status_participating, description) VALUES (:event_name, :event_date, :event_time, :event_location, :event_theme, :status_participating, :description)";
            $stmt = $this->pdo->prepare($query);
            $stmt -> execute([
                ':event_name' => $eventName,
                ':event_date' => $eventDate->format('Y-m-d'),
                ':event_time' => $eventTime->format('H:i'),
                ':event_location' => $eventLocation,
                ':event_theme' => $eventTheme,
                ':status_participating' => $statusParticipating,
                ':description' => $description
            ]);
            return true;
        } catch (PDOException $e) {
            error_log('EventCreationModel::createEvent - ' . $e->getMessage());
            return false;
        }
    }

}