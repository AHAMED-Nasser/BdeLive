<?php

declare(strict_types=1);

/**
 * Pagination Model - PHP 8 optimized
 *
 * @package BdeLive\Models
 * @version 2.0.0
 */
class PaginationModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Get paginated events
     *
     * @param int $offset
     * @param int $limit
     * @return array<int, array{
     *      event_id: int,
     *      event_name: string,
     *      event_date: string,
     *      event_time: string,
     *      event_location: string,
     *      event_theme: string,
     *      description: string,
     *      max_capacity: int,
     *      created_at: string
     *  }>
     */
    public function getPaginatedData(int $offset, int $limit): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT event_id, event_name, event_date, event_time, event_location, 
                    event_theme, description, max_capacity, created_at 
             FROM EVENTS 
             ORDER BY event_date DESC 
             LIMIT :offset, :limit"
        );

        $stmt->execute([
            ':offset' => $offset,
            ':limit' => $limit,
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Get total number of events
     */
    public function getTotalItems(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM EVENTS");
        if ($stmt === false) {
            throw new PDOException('Failed to execute COUNT(*) query.');
        }

        return (int) $stmt->fetchColumn();
    }
}
