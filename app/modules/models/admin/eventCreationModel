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

    
}   