<?php

declare(strict_types=1);

namespace App\Modules\Repositories;

use App\Modules\Entities\Event;
use App\Modules\Factories\EventFactory;
use App\Modules\Repositories\Interfaces\EventRepositoryInterface;
use App\Modules\Helpers\SlugGenerator;
use PDO;
use PDOException;
use DateTime;

/**
 * EventRepository - Data Mapper for Event entities
 *
 * This repository implements the Repository Pattern and Data Mapper Pattern,
 * providing a clean separation between the domain layer (Event entities)
 * and the database layer (PDO).
 *
 * Implements EventRepositoryInterface following the Dependency Inversion Principle (SOLID).
 * Uses EventFactory to transform PDO arrays into Event entities.
 *
 * @package BdeLive\Repositories
 * @author BdeLive - Group 8
 * @version 2.0.0
 */
class EventRepository implements EventRepositoryInterface
{
    /**
     * PDO database connection instance
     *
     * @var PDO
     */
    private PDO $pdo;

    /**
     * Constructor - Dependency Injection of PDO connection
     *
     * Following best practices (CM4 Slide 22), the PDO connection
     * is injected via constructor to facilitate testing and respect
     * the Dependency Inversion Principle.
     *
     * @param PDO $pdo Database connection instance
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // =========================================================================
    // READ OPERATIONS - Interface implementation
    // =========================================================================

    /**
     * Find an event by its unique identifier
     *
     * @param int $id Event ID
     * @return Event|null Event entity or null if not found
     */
    public function findById(int $id): ?Event
    {
        try {
            $sql = "SELECT * FROM EVENTS WHERE event_id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);

            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            return $data ? EventFactory::createFromDatabase($data) : null;
        } catch (PDOException $e) {
            error_log('EventRepository::findById - ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Find an event by its SEO-friendly slug
     *
     * @param string $slug URL-friendly slug
     * @return Event|null Event entity or null if not found
     */
    public function findBySlug(string $slug): ?Event
    {
        try {
            $sql = "SELECT * FROM EVENTS WHERE slug = :slug";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':slug' => $slug]);

            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            return $data ? EventFactory::createFromDatabase($data) : null;
        } catch (PDOException $e) {
            error_log('EventRepository::findBySlug - ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Retrieve all events
     *
     * @return array<int, Event> Array of Event entities
     */
    public function findAll(): array
    {
        try {
            $sql = 'SELECT event_id, event_name, slug, event_date, event_time, description FROM EVENTS';
            $stmt = $this->pdo->query($sql);

            if ($stmt === false) {
                return [];
            }

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return EventFactory::createCollectionFromDatabase($results);
        } catch (PDOException $e) {
            error_log('EventRepository::findAll - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Retrieve paginated events
     *
     * @param int $offset Starting offset
     * @param int $limit Number of events to retrieve
     * @return array<int, Event> Array of Event entities
     */
    public function findPaginated(int $offset, int $limit): array
    {
        try {
            $sql = 'SELECT event_id, event_name, slug, event_date, event_time, event_location, description, images
                    FROM EVENTS
                    ORDER BY event_date DESC, event_time DESC
                    LIMIT :offset, :limit';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return EventFactory::createCollectionFromDatabase($results);
        } catch (PDOException $e) {
            error_log('EventRepository::findPaginated - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Retrieve upcoming events (future events only)
     *
     * @param int $limit Maximum number of events to retrieve
     * @return array<int, Event> Array of upcoming Event entities
     */
    public function findLatestEvents(int $limit): array
    {
        try {
            $sql = 'SELECT event_id, event_name, event_date, event_time, event_location, description, images
                    FROM EVENTS
                    WHERE event_date >= CURDATE()
                    ORDER BY event_date ASC, event_time ASC
                    LIMIT :limit';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return EventFactory::createCollectionFromDatabase($results);
        } catch (PDOException $e) {
            error_log('EventRepository::findLatestEvents - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Find events for homepage carousel: upcoming first, then recent past as fallback
     *
     * Ensures the carousel is never empty when there are no future events:
     * - Prefer events with event_date >= today (ordered by date ascending).
     * - If fewer than limit, complete with most recent past events (ordered by date descending).
     *
     * @param int $limit Maximum number of events to return (default: 5)
     * @return array<int, Event> Array of Event entities
     */
    public function findEventsForHomepage(int $limit = 5): array
    {
        try {
            $sql = 'SELECT event_id, event_name, slug, event_date, event_time, event_location, description, images
                    FROM EVENTS
                    ORDER BY
                        (event_date >= CURDATE()) DESC,
                        CASE WHEN event_date >= CURDATE() THEN event_date END ASC,
                        CASE WHEN event_date < CURDATE() THEN event_date END DESC,
                        event_time DESC
                    LIMIT :limit';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return EventFactory::createCollectionFromDatabase($results);
        } catch (PDOException $e) {
            error_log('EventRepository::findEventsForHomepage - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Count total number of events
     *
     * @return int Total event count
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

    // =========================================================================
    // WRITE OPERATIONS - Interface implementation
    // =========================================================================

    /**
     * Save an event (insert or update)
     *
     * If the event has no ID (null), it will be inserted.
     * If the event has an ID, it will be updated.
     *
     * This method implements the "smart save" pattern recommended
     * for clean controller code.
     *
     * @param Event $event Event entity to save
     * @return bool True if save succeeded, false otherwise
     */
    public function save(Event $event): bool
    {
        if ($event->getId() === null) {
            return $this->insert($event);
        }

        return $this->update($event);
    }

    /**
     * Delete an event by its ID
     *
     * @param int $id Event ID to delete
     * @return bool True if deletion succeeded, false otherwise
     */
    public function delete(int $id): bool
    {
        try {
            $query = "DELETE FROM EVENTS WHERE event_id = :event_id";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':event_id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('EventRepository::delete - ' . $e->getMessage());
            return false;
        }
    }

    // =========================================================================
    // PRIVATE HELPER METHODS - Internal use only
    // =========================================================================

    /**
     * Insert a new event into the database
     *
     * @param Event $event Event entity to insert
     * @return bool True if insertion succeeded, false otherwise
     */
    private function insert(Event $event): bool
    {
        // Generate unique slug from event name for SEO-friendly URLs
        $slug = SlugGenerator::generateUnique($event->getName(), function ($slug) {
            return $this->slugExists($slug);
        });

        try {
            $query = "INSERT INTO EVENTS (event_name, slug, event_date, event_time, event_location, " .
                "event_theme, status_participating, description, images, is_group_event, team_size) " .
                "VALUES (:event_name, :slug, :event_date, :event_time, :event_location, " .
                ":event_theme, :status_participating, :description, :images, :is_group_event, :team_size)";

            $stmt = $this->pdo->prepare($query);

            return $stmt->execute([
                ':event_name' => $event->getName(),
                ':slug' => $slug,
                ':event_date' => $event->getDate(),
                ':event_time' => $event->getTime(),
                ':event_location' => $event->getLocation(),
                ':event_theme' => $event->getTheme(),
                ':status_participating' => $event->getStatusParticipating(),
                ':description' => $event->getDescription(),
                ':images' => $event->getImages(),
                ':is_group_event' => $event->isGroupEvent() ? 1 : 0,
                ':team_size' => $event->getTeamSize()
            ]);
        } catch (PDOException $e) {
            error_log('EventRepository::insert - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update an existing event in the database
     *
     * @param Event $event Event entity to update
     * @return bool True if update succeeded, false otherwise
     */
    private function update(Event $event): bool
    {
        $eventId = $event->getId();
        if ($eventId === null) {
            error_log('EventRepository::update - Cannot update event without ID');
            return false;
        }

        // Regenerate slug from event name, ensuring uniqueness (excluding current event)
        $slug = SlugGenerator::generateUnique($event->getName(), function ($testSlug) use ($eventId) {
            return $this->slugExistsExcludingId($testSlug, $eventId);
        });

        try {
            $sql = "UPDATE EVENTS SET
                event_name = :event_name,
                slug = :slug,
                event_date = :event_date,
                event_time = :event_time,
                event_location = :event_location,
                event_theme = :event_theme,
                status_participating = :status_participating,
                description = :description,
                images = :images,
                is_group_event = :is_group_event,
                team_size = :team_size
                WHERE event_id = :event_id";

            $stmt = $this->pdo->prepare($sql);

            return $stmt->execute([
                ':event_id' => $eventId,
                ':event_name' => $event->getName(),
                ':slug' => $slug,
                ':event_date' => $event->getDate(),
                ':event_time' => $event->getTime(),
                ':event_location' => $event->getLocation(),
                ':event_theme' => $event->getTheme(),
                ':status_participating' => $event->getStatusParticipating(),
                ':description' => $event->getDescription(),
                ':images' => $event->getImages(),
                ':is_group_event' => $event->isGroupEvent() ? 1 : 0,
                ':team_size' => $event->getTeamSize()
            ]);
        } catch (PDOException $e) {
            error_log('EventRepository::update - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if a slug already exists in the database
     *
     * @param string $slug The slug to check
     * @return bool True if exists, false otherwise
     */
    private function slugExists(string $slug): bool
    {
        try {
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM EVENTS WHERE slug = :slug');
            $stmt->execute([':slug' => $slug]);
            return (int) $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('EventRepository::slugExists - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if a slug exists excluding a specific event ID
     *
     * @param string $slug The slug to check
     * @param int $excludeId Event ID to exclude from check
     * @return bool True if exists, false otherwise
     */
    private function slugExistsExcludingId(string $slug, int $excludeId): bool
    {
        try {
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM EVENTS WHERE slug = :slug AND event_id != :id');
            $stmt->execute([':slug' => $slug, ':id' => $excludeId]);
            return (int) $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('EventRepository::slugExistsExcludingId - ' . $e->getMessage());
            return false;
        }
    }

    // =========================================================================
    // ADDITIONAL UTILITY METHODS (not in interface)
    // =========================================================================

    /**
     * Update only the images associated with an event
     *
     * Useful for image upload operations without modifying other data.
     *
     * @param int $eventId Event unique identifier
     * @param string $imageJson JSON string of image URLs
     * @return bool True if update succeeded, false otherwise
     */
    public function updateEventImages(int $eventId, string $imageJson): bool
    {
        try {
            $sql = 'UPDATE EVENTS SET images = :images WHERE event_id = :id';
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':images' => $imageJson,
                ':id' => $eventId
            ]);
        } catch (PDOException $e) {
            error_log('EventRepository::updateEventImages - ' . $e->getMessage());
            return false;
        }
    }
}
