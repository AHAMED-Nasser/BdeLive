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

    public function createEvent(string $event_name, string $event_date, string $event_time, string $event_location, string $event_theme, string $description, int $max_capacity): bool {
        try {
            $query = "INSERT INTO EVENTS (event_name, event_date, event_time, event_location, event_theme, description, max_capacity) VALUES (:event_name, :event_date, :event_time, :event_location, :event_theme, :description, :max_capacity)";
            $stmt = $this->pdo->prepare($query);
            $success = $stmt->execute([
                ':event_name' => $event_name,
                ':event_date' => $event_date,
                ':event_time' => $event_time,
                ':event_location' => $event_location,
                ':event_theme' => $event_theme,
                ':description' => $description,
                ':max_capacity' => $max_capacity
            ]);
            return $success;
        } catch (PDOException $e) {
            error_log('EventCreationModel::createEvent - ' . $e->getMessage());
            return false;
        }
    }

    
}   