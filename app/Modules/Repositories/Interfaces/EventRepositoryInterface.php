<?php

declare(strict_types=1);

namespace App\Modules\Repositories\Interfaces;

use App\Modules\Entities\Event;

/**
 * EventRepositoryInterface - Contract for Event data persistence
 *
 * Defines the standard operations for Event repository following
 * the Dependency Inversion Principle (SOLID).
 *
 * This interface allows controllers to depend on abstractions rather than
 * concrete implementations, making the code more flexible and testable.
 *
 * @package BdeLive\Repositories\Interfaces
 * @author BdeLive - Group 8
 * @version 1.0.0
 */
interface EventRepositoryInterface
{
    /**
     * Find an event by its unique identifier
     *
     * @param int $id Event ID
     * @return Event|null Event entity or null if not found
     */
    public function findById(int $id): ?Event;

    /**
     * Find an event by its SEO-friendly slug
     *
     * @param string $slug URL-friendly slug
     * @return Event|null Event entity or null if not found
     */
    public function findBySlug(string $slug): ?Event;

    /**
     * Retrieve all events
     *
     * @return array<int, Event> Array of Event entities
     */
    public function findAll(): array;

    /**
     * Retrieve paginated events
     *
     * @param int $offset Starting offset
     * @param int $limit Number of events to retrieve
     * @return array<int, Event> Array of Event entities
     */
    public function findPaginated(int $offset, int $limit): array;

    /**
     * Retrieve upcoming events (future events only)
     *
     * @param int $limit Maximum number of events to retrieve
     * @return array<int, Event> Array of upcoming Event entities
     */
    public function findLatestEvents(int $limit): array;

    /**
     * Retrieve events for homepage: upcoming first, then recent past
     *
     * @param int $limit Maximum number of events to retrieve
     * @return array<int, Event> Array of Event entities
     */
    public function findEventsForHomepage(int $limit = 5): array;

    /**
     * Save an event (insert or update)
     *
     * If the event has no ID (null), it will be inserted.
     * If the event has an ID, it will be updated.
     *
     * @param Event $event Event entity to save
     * @return bool True if save succeeded, false otherwise
     */
    public function save(Event $event): bool;

    /**
     * Delete an event by its ID
     *
     * @param int $id Event ID to delete
     * @return bool True if deletion succeeded, false otherwise
     */
    public function delete(int $id): bool;

    /**
     * Count total number of events
     *
     * @return int Total event count
     */
    public function count(): int;
}
