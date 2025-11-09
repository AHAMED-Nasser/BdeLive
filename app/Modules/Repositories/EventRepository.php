<?php

declare(strict_types=1);

namespace App\Modules\Repositories;

use PDO;
use PDOException;
use App\Core\Database;

/**
 * Event Repository - Access to event data
 *
 * Provides methods for retrieving event data from the database.
 * Based on the EVENTS table structure from the SQL dump.
 *
 * @package BdeLive\Repositories
 * @version 1.0.0
 */
class EventRepository
{
    /**
     * PDO database connection instance
     *
     * @var PDO
     */
    private PDO $pdo;

    /**
     * Constructor - Initialize the PDO connection
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
     * Count the total number of events in the database
     *
     * Counts all events in the EVENTS table.
     *
     * @return int Total number of events
     * @throws PDOException If database query fails
     */
    public function count(): int
    {
        try {
            $stmt = $this->pdo->query('SELECT COUNT(*) FROM EVENTS');
            if ($stmt === false) {
                return 0;
            }
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('EventRepository::count - ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Retrieve a paginated list of events
     *
     * Fetches events from the database with pagination support.
     * Results are ordered by event date and time in descending order.
     *
     * @param int $offset The offset calculated by Pagination->getOffset()
     * @param int $limit The limit (items per page)
     * @return array<string, mixed> Array of events for the page, each containing:
     *                              - event_id: Event identifier
     *                              - event_name: Event name
     *                              - event_date: Event date
     *                              - event_time: Event time
     *                              - event_location: Event location
     *                              - description: Event description
     * @throws PDOException If database query fails
     */
    public function findPaginated(int $offset, int $limit): array
    {
        try {
            // SQL based on the EVENTS table structure
            $sql = 'SELECT event_id, event_name, event_date, event_time, event_location, description, images
                    FROM EVENTS
                    ORDER BY event_date DESC, event_time DESC
                    LIMIT :offset, :limit';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('EventRepository::findPaginated - ' . $e->getMessage());
            return [];
        }
    }
}

\class_alias(__NAMESPACE__ . '\\EventRepository', 'EventRepository');
