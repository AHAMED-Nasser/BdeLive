<?php

declare(strict_types=1);

class EventRegistrationRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function isUserRegistered(int $eventId, int $userId): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM EVENT_REGISTRATIONS WHERE event_id = ? AND user_id = ?');
        $stmt->execute([$eventId, $userId]);
        return $stmt->fetchColumn() !== false;
    }

    public function registerUser(int $eventId, int $userId): bool
    {
        $stmt = $this->pdo->prepare('INSERT INTO EVENT_REGISTRATIONS (event_id, user_id, registration_status) VALUES (?, ?, ?)');
        return $stmt->execute([$eventId, $userId, 'Confirmé']);
    }

    public function unregisterUser(int $eventId, int $userId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM EVENT_REGISTRATIONS WHERE event_id = ? AND user_id = ?');
        return $stmt->execute([$eventId, $userId]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getEventRegistrations(int $eventId): array
    {
        $stmt = $this->pdo->prepare('SELECT er.*, u.first_name, u.last_name, u.email 
                                    FROM EVENT_REGISTRATIONS er
                                    JOIN USERS u ON er.user_id = u.user_id
                                    WHERE er.event_id = ?
                                    ORDER BY er.registration_date ASC');
        $stmt->execute([$eventId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countEventRegistrations(int $eventId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM EVENT_REGISTRATIONS WHERE event_id = ?');
        $stmt->execute([$eventId]);
        return (int)$stmt->fetchColumn();
    }
}