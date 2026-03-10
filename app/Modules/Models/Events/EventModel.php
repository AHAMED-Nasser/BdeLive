<?php

declare(strict_types=1);

namespace App\Modules\Models\Events;

use PDO;
use PDOException;
use DateTime;
use App\Modules\Helpers\SlugGenerator;

/**
 * EventModel - Unified model for event management
 *
 * This model centralizes all operations on events (read and write)
 * following the Repository pattern taught in the course (CM4 Slide 22).
 *
 * Responsibilities:
 * - Read: Retrieve events with pagination, filters, etc.
 * - Write: Create, update and delete events
 *
 * @package BdeLive\Models\Events
 * @author BdeLive - Group 8
 * @version 2.0.0
 */
class EventModel
{
    private PDO $pdo;

    /**
     * Constructor - PDO dependency injection
     *
     * Following best practices (CM4 Slide 22), the PDO connection
     * is injected via the constructor to simplify testing and respect
     * the dependency inversion principle.
     *
     * @param PDO $pdo Database connection instance
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }



    /**
     * Count total number of events in the database
     *
     * Counts all events present in the EVENTS table.
     *
     * @return int Total number of events
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
            error_log('EventModel::count - ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Retrieve an event by its identifier
     *
     * @param int $id Unique event identifier
     * @return array<string, mixed>|null Event data or null if not found
     */
    public function findById(int $id): ?array
    {
        try {
            $sql = "SELECT * FROM EVENTS WHERE event_id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);

            $event = $stmt->fetch(PDO::FETCH_ASSOC);

            return $event ?: null;
        } catch (PDOException $e) {
            error_log('EventModel::findById - ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Retrieve an event by its SEO-friendly slug
     *
     * Searches for an event using its URL-friendly slug identifier.
     * This method is used for SEO-optimized URLs instead of numeric IDs.
     *
     * @param string $slug URL-friendly slug (e.g., "mon-evenement-special")
     * @return array<string, mixed>|null Event data or null if not found
     */
    public function findBySlug(string $slug): ?array
    {
        try {
            $sql = "SELECT * FROM EVENTS WHERE slug = :slug";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':slug' => $slug]);

            $event = $stmt->fetch(PDO::FETCH_ASSOC);
            return $event ?: null;
        } catch (PDOException $e) {
            error_log('EventModel::findBySlug - ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if a slug already exists in the database
     *
     * Used to ensure slug uniqueness when creating new events.
     * If a slug exists, SlugGenerator will append a number (e.g., "event-2").
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
            error_log('EventModel::slugExists - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if a slug exists excluding a specific event ID
     *
     * Used during event updates to allow keeping the same slug
     * while preventing conflicts with other events.
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
            error_log('EventModel::slugExistsExcludingId - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieve a paginated list of events
     *
     * Retrieves events with pagination support.
     * Results are sorted by event date and time (descending).
     *
     * @param int $offset Offset calculated by the pagination system
     * @param int $limit Number of items per page
     * @return array<int, array<string, mixed>> Array of events for the requested page
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

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('EventModel::findPaginated - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Retrieve all events for calendar display
     *
     * @return array<int, array<string, mixed>> Array of all events
     */
    public function findAll(): array
    {
        try {
            $sql = 'SELECT event_id, event_name, slug, event_date, event_time, description FROM EVENTS';
            $stmt = $this->pdo->query($sql);

            if ($stmt === false) {
                return [];
            }

            /** @var array<int, array<string, mixed>> $results */
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $results;
        } catch (PDOException $e) {
            error_log('EventModel::findAll - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Retrieve upcoming events
     *
     * Retrieves upcoming events sorted by date (ascending) to display
     * the next events first. Returns only events with a date greater
     * than or equal to today.
     *
     * @param int $limit Maximum number of events to retrieve
     * @return array<int, array<string, mixed>> Array of upcoming events
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

            /** @var array<int, array<string, mixed>> $results */
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $results;
        } catch (PDOException $e) {
            error_log('EventModel::findLatestEvents - ' . $e->getMessage());
            return [];
        }
    }


    /**
     * Insert a new event into the database
     *
     * Creates a new event record with all provided information,
     * including Cloudinary images and group registration options.
     *
     * @param string $eventName Event name/title
     * @param DateTime $eventDate Event date
     * @param DateTime $eventTime Event time
     * @param string $eventLocation Event location/venue
     * @param string $eventTheme Event theme/category
     * @param string $statusParticipating Allowed participant statuses (comma-separated)
     * @param string $description Event description
     * @param string $images JSON string of Cloudinary image URLs
     * @param bool $isGroupEvent Whether this is a group event
     * @param int $teamSize Maximum team size (for group events)
     * @return bool True on success, false on failure
     */
    public function insertEvent(
        string $eventName,
        DateTime $eventDate,
        DateTime $eventTime,
        string $eventLocation,
        string $eventTheme,
        string $statusParticipating,
        string $description,
        string $images = '',
        bool $isGroupEvent = false,
        int $teamSize = 1
    ): bool {
        // Generate a unique slug from event name for SEO-friendly URLs
        $slug = SlugGenerator::generateUnique($eventName, function ($slug) {
            return $this->slugExists($slug);
        });

        try {
            $query = "INSERT INTO EVENTS (event_name, slug, event_date, event_time, event_location, " .
                "event_theme, status_participating, description, images, is_group_event, team_size) " .
                "VALUES (:event_name, :slug, :event_date, :event_time, :event_location, " .
                ":event_theme, :status_participating, :description, :images, :is_group_event, :team_size)";
            $stmt = $this->pdo->prepare($query);
            return $stmt->execute([
                ':event_name' => $eventName,
                ':slug' => $slug,
                ':event_date' => $eventDate->format('Y-m-d'),
                ':event_time' => $eventTime->format('H:i'),
                ':event_location' => $eventLocation,
                ':event_theme' => $eventTheme,
                ':status_participating' => $statusParticipating,
                ':description' => $description,
                ':images' => $images,
                ':is_group_event' => $isGroupEvent ? 1 : 0,
                ':team_size' => $teamSize
            ]);
        } catch (PDOException $e) {
            error_log('EventModel::insertEvent - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update an existing event in the database
     *
     * Updates all information of an existing event identified by its ID.
     * All fields are updated, including images and group registration settings.
     *
     * @param int $eventId Identifier of the event to update
     * @param string $eventName New event name/title
     * @param DateTime $eventDate New event date
     * @param DateTime $eventTime New event time
     * @param string $eventLocation New event location
     * @param string $eventTheme New event theme
     * @param string $statusParticipating New allowed participant statuses
     * @param string $description New event description
     * @param string $images New JSON string of image URLs
     * @param bool $isGroupEvent Whether this is a group event
     * @param int $teamSize Maximum team size
     * @return bool True on success, false on failure
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
        string $images,
        bool $isGroupEvent = false,
        int $teamSize = 1
    ): bool {
        // Regenerate slug from event name, ensuring uniqueness (excluding current event)
        $slug = SlugGenerator::generateUnique($eventName, function ($testSlug) use ($eventId) {
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
                ':event_name' => $eventName,
                ':slug' => $slug,
                ':event_date' => $eventDate->format('Y-m-d'),
                ':event_time' => $eventTime->format('H:i'),
                ':event_location' => $eventLocation,
                ':event_theme' => $eventTheme,
                ':status_participating' => $statusParticipating,
                ':description' => $description,
                ':images' => $images,
                ':is_group_event' => $isGroupEvent ? 1 : 0,
                ':team_size' => $teamSize
            ]);
        } catch (PDOException $e) {
            error_log('EventModel::updateEvent - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update only images associated with an event
     *
     * Updates the images field of an event without changing other information.
     * Useful after uploading additional images.
     *
     * @param int $eventId Unique event identifier
     * @param string $imageJson JSON string of image URLs
     * @return bool True on success, false on failure
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
            error_log('EventModel::updateEventImages - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete an event from the database
     *
     * Permanently deletes an event identified by its ID.
     * This operation is irreversible.
     *
     * @param int $eventId Identifier of the event to delete
     * @return bool True on success, false on failure
     */
    public function deleteEvent(int $eventId): bool
    {
        try {
            $query = "DELETE FROM EVENTS WHERE event_id = :event_id";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('EventModel::deleteEvent - ' . $e->getMessage());
            return false;
        }
    }
}
