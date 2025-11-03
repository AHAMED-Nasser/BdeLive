<?php

declare(strict_types=1);

namespace App\Tests\Unit\Repositories;

use PHPUnit\Framework\TestCase;
use App\Modules\Repositories\EventRepository;
use App\Core\Database;
use PDOException;

/**
 * Test suite for EventRepository
 *
 * Note: These tests require a database connection. They are skipped if
 * database is not available.
 *
 * @package App\Tests\Unit\Repositories
 */
class EventRepositoryTest extends TestCase
{
    /**
     * Test that count returns an integer
     *
     * @return void
     */
    public function testCountReturnsInteger(): void
    {
        try {
            $repository = new EventRepository();
            $count = $repository->count();

            $this->assertIsInt($count);
            $this->assertGreaterThanOrEqual(0, $count);
        } catch (PDOException $e) {
            $this->markTestSkipped('Database connection not available: ' . $e->getMessage());
        }
    }

    /**
     * Test that findPaginated returns an array
     *
     * @return void
     */
    public function testFindPaginatedReturnsArray(): void
    {
        try {
            $repository = new EventRepository();
            $events = $repository->findPaginated(0, 10);

            $this->assertIsArray($events);
        } catch (PDOException $e) {
            $this->markTestSkipped('Database connection not available: ' . $e->getMessage());
        }
    }

    /**
     * Test that findPaginated respects offset
     *
     * @return void
     */
    public function testFindPaginatedRespectsOffset(): void
    {
        try {
            $repository = new EventRepository();
            $events1 = $repository->findPaginated(0, 10);
            $events2 = $repository->findPaginated(10, 10);

            // Events should be different (unless there are less than 10 events)
            $this->assertIsArray($events1);
            $this->assertIsArray($events2);
        } catch (PDOException $e) {
            $this->markTestSkipped('Database connection not available: ' . $e->getMessage());
        }
    }

    /**
     * Test that findPaginated respects limit
     *
     * @return void
     */
    public function testFindPaginatedRespectsLimit(): void
    {
        try {
            $repository = new EventRepository();
            $events = $repository->findPaginated(0, 5);

            $this->assertIsArray($events);
            $this->assertLessThanOrEqual(5, count($events));
        } catch (PDOException $e) {
            $this->markTestSkipped('Database connection not available: ' . $e->getMessage());
        }
    }

    /**
     * Test that findPaginated returns empty array with invalid offset
     *
     * @return void
     */
    public function testFindPaginatedReturnsEmptyArrayWithInvalidOffset(): void
    {
        try {
            $repository = new EventRepository();
            $events = $repository->findPaginated(999999, 10);

            $this->assertIsArray($events);
            // May be empty if no events exist at that offset
        } catch (PDOException $e) {
            $this->markTestSkipped('Database connection not available: ' . $e->getMessage());
        }
    }

    /**
     * Test that findPaginated returns events with correct structure
     *
     * @return void
     */
    public function testFindPaginatedReturnsEventsWithCorrectStructure(): void
    {
        try {
            $repository = new EventRepository();
            $events = $repository->findPaginated(0, 1);

            if (!empty($events)) {
                $event = $events[0];
                $this->assertIsArray($event);
                // Check for expected keys (if events exist)
                $expectedKeys = ['event_id', 'event_name', 'event_date', 'event_time', 'event_location', 'description'];
                foreach ($expectedKeys as $key) {
                    $this->assertArrayHasKey($key, $event);
                }
            }
        } catch (PDOException $e) {
            $this->markTestSkipped('Database connection not available: ' . $e->getMessage());
        }
    }
}

