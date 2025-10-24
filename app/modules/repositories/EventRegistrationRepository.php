<?php

declare(strict_types=1);

/**
 * Event Registration Repository - Gestion des inscriptions aux événements
 * @package BdeLive\Repositories
 * @version 1.0.0
 */
class EventRegistrationRepository
{
    private PDO $pdo;

    public function __construct()
{
    $this->pdo = Database::getInstance()->getConnection();
}

public function isUserRegistered(int $eventId, int $userId): bool
{
    try {
        $stmt = $this->pdo->prepare('SELECT * FROM EVENT_REGISTRATIONS WHERE event_id = ? AND user_id = ?');
        $stmt->execute([$eventId, $userId]);
        return $stmt->fetch() !== false;
    } catch (PDOException $e) {
        error_log('EventRegistrationRepository::isUserRegistered - ' . $e->getMessage());
        return false;
    }
}   

public function registerUser(int $eventId, int $userId): bool
{
    try {
        $stmt = $this->pdo->prepare('INSERT INTO EVENT_REGISTRATIONS (event_id, user_id, registration_status) VALUES (?, ?, ?)');
        $stmt->execute([$eventId, $userId, 'Confirmé']);
        return true;
    } catch (PDOException $e) {
        error_log('EventRegistrationRepository::registerUser - ' . $e->getMessage());
        return false;
    }
}

public function unregisterUser(int $eventId, int $userId): bool
{
    try {
        $stmt = $this->pdo->prepare('DELETE FROM EVENT_REGISTRATIONS WHERE event_id = ? AND user_id = ?');
        $stmt->execute([$eventId, $userId]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log('EventRegistrationRepository::unregisterUser - ' . $e->getMessage());
        return false;
    }
}
