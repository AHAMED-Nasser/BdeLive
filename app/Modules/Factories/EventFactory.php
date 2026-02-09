<?php

declare(strict_types=1);

namespace App\Modules\Factories;

use App\Modules\Entities\Event;

/**
 * EventFactory - Transforms raw database data into Event entities
 *
 * This factory class acts as a translator between the database layer
 * (PDO arrays with column names) and the domain layer (Event entities).
 *
 * Responsibilities:
 * - Hydrate Event entities from PDO result arrays
 * - Handle type conversions (string to int, int to bool, etc.)
 * - Provide clean, type-safe entities to controllers
 *
 * Design Pattern: Factory Pattern
 * Related to: Data Mapper Pattern
 *
 * @package BdeLive\Factories
 * @author BdeLive - Group 8
 * @version 1.0.0
 */
class EventFactory
{
    /**
     * Create an Event entity from database row data
     *
     * Transforms a raw PDO array (from EVENTS table) into a clean,
     * type-safe Event entity. Handles all type conversions and
     * ensures data integrity.
     *
     * Database columns mapping:
     * - event_id → id (int|null)
     * - event_name → name (string)
     * - slug → slug (string)
     * - event_date → date (string, Y-m-d format)
     * - event_time → time (string, H:i format)
     * - event_location → location (string)
     * - event_theme → theme (string)
     * - status_participating → statusParticipating (string)
     * - description → description (string)
     * - images → images (string, JSON)
     * - is_group_event → isGroupEvent (bool)
     * - team_size → teamSize (int)
     *
     * @param array<string, mixed> $data Raw database row from EVENTS table
     * @return Event Hydrated Event entity
     */
    public static function createFromDatabase(array $data): Event
    {
        return new Event(
            id: isset($data['event_id']) ? (int) $data['event_id'] : null,
            name: (string) ($data['event_name'] ?? ''),
            slug: (string) ($data['slug'] ?? ''),
            date: (string) ($data['event_date'] ?? ''),
            time: (string) ($data['event_time'] ?? ''),
            location: (string) ($data['event_location'] ?? ''),
            theme: (string) ($data['event_theme'] ?? ''),
            statusParticipating: (string) ($data['status_participating'] ?? ''),
            description: (string) ($data['description'] ?? ''),
            images: (string) ($data['images'] ?? ''),
            isGroupEvent: !empty($data['is_group_event']) && (int) $data['is_group_event'] === 1,
            teamSize: (int) ($data['team_size'] ?? 1)
        );
    }

    /**
     * Create multiple Event entities from database result set
     *
     * Batch version of createFromDatabase for paginated results.
     * Useful when retrieving multiple events from findAll() or findPaginated().
     *
     * @param array<int, array<string, mixed>> $dataSet Array of database rows
     * @return array<int, Event> Array of hydrated Event entities
     */
    public static function createCollectionFromDatabase(array $dataSet): array
    {
        $events = [];
        foreach ($dataSet as $data) {
            $events[] = self::createFromDatabase($data);
        }
        return $events;
    }

    /**
     * Convert an Event entity back to database-ready array
     *
     * Useful for UPDATE and INSERT operations. Transforms the entity
     * back into an array format compatible with PDO prepared statements.
     *
     * Note: This method excludes the ID for INSERT operations (handled separately).
     *
     * @param Event $event Event entity to convert
     * @return array<string, mixed> Database-ready associative array
     */
    public static function toDatabase(Event $event): array
    {
        return [
            'event_name' => $event->getName(),
            'slug' => $event->getSlug(),
            'event_date' => $event->getDate(),
            'event_time' => $event->getTime(),
            'event_location' => $event->getLocation(),
            'event_theme' => $event->getTheme(),
            'status_participating' => $event->getStatusParticipating(),
            'description' => $event->getDescription(),
            'images' => $event->getImages(),
            'is_group_event' => $event->isGroupEvent() ? 1 : 0,
            'team_size' => $event->getTeamSize()
        ];
    }
}
