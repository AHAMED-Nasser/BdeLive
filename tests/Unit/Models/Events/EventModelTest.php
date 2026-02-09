<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Events;

use PHPUnit\Framework\TestCase;
use App\Modules\Models\Events\EventModel;
use PDO;
use PDOStatement;
use PDOException;
use DateTime;

/**
 * Unit tests for EventModel
 * 
 * Tests the unified EventModel class that handles both read and write operations
 * for events using dependency injection with PDO mocking.
 */
class EventModelTest extends TestCase
{
    private EventModel $model;
    private PDO $mockPdo;
    private PDOStatement $mockStmt;

    /**
     * Set up test fixtures with PDO mocking
     * 
     * Uses direct PDO injection instead of Database singleton pattern
     * for better testability and isolation.
     */
    protected function setUp(): void
    {
        $this->mockPdo = $this->createMock(PDO::class);
        $this->mockStmt = $this->createMock(PDOStatement::class);
        
        // Direct injection - no Database singleton needed
        $this->model = new EventModel($this->mockPdo);
    }

    // =========================================================================
    // READ OPERATIONS TESTS
    // =========================================================================

    /**
     * Test that count() returns an integer
     */
    public function testCountReturnsInteger(): void
    {
        // Mock the PDO query to return a count
        $this->mockStmt->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(42);

        $this->mockPdo->expects($this->once())
            ->method('query')
            ->with('SELECT COUNT(*) FROM EVENTS')
            ->willReturn($this->mockStmt);

        $count = $this->model->count();

        $this->assertIsInt($count);
        $this->assertEquals(42, $count);
    }

    /**
     * Test that count() returns 0 when query fails
     */
    public function testCountReturnsZeroOnError(): void
    {
        $this->mockPdo->expects($this->once())
            ->method('query')
            ->with('SELECT COUNT(*) FROM EVENTS')
            ->willReturn(false);

        $count = $this->model->count();

        $this->assertEquals(0, $count);
    }

    /**
     * Test that findById() returns an event when found
     */
    public function testFindByIdReturnsEvent(): void
    {
        $eventId = 1;
        $expectedEvent = [
            'event_id' => 1,
            'event_name' => 'Test Event',
            'event_date' => '2025-12-01',
            'event_time' => '10:00:00',
            'event_location' => 'Test Location',
            'description' => 'Test Description'
        ];

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([':id' => $eventId])
            ->willReturn(true);

        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expectedEvent);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with('SELECT * FROM EVENTS WHERE event_id = :id')
            ->willReturn($this->mockStmt);

        $result = $this->model->findById($eventId);

        $this->assertEquals($expectedEvent, $result);
    }

    /**
     * Test that findById() returns null when event not found
     */
    public function testFindByIdReturnsNullWhenNotFound(): void
    {
        $eventId = 999;

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn(false);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->model->findById($eventId);

        $this->assertNull($result);
    }

    /**
     * Test that findBySlug() returns an event when found
     */
    public function testFindBySlugReturnsEvent(): void
    {
        $slug = 'mon-evenement-special';
        $expectedEvent = [
            'event_id' => 1,
            'event_name' => 'Mon Événement Spécial',
            'slug' => $slug,
            'event_date' => '2025-12-01',
            'event_time' => '10:00:00',
            'event_location' => 'Paris',
            'description' => 'Test Description'
        ];

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([':slug' => $slug])
            ->willReturn(true);

        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expectedEvent);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with('SELECT * FROM EVENTS WHERE slug = :slug')
            ->willReturn($this->mockStmt);

        $result = $this->model->findBySlug($slug);

        $this->assertEquals($expectedEvent, $result);
    }

    /**
     * Test that findBySlug() returns null when event not found
     */
    public function testFindBySlugReturnsNullWhenNotFound(): void
    {
        $slug = 'slug-inexistant';

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([':slug' => $slug])
            ->willReturn(true);

        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn(false);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with('SELECT * FROM EVENTS WHERE slug = :slug')
            ->willReturn($this->mockStmt);

        $result = $this->model->findBySlug($slug);

        $this->assertNull($result);
    }

    /**
     * Test that findPaginated() returns an array of events
     */
    public function testFindPaginatedReturnsArray(): void
    {
        $mockEvents = [
            [
                'event_id' => 1,
                'event_name' => 'Test Event 1',
                'event_date' => '2025-12-01',
                'event_time' => '10:00:00',
                'event_location' => 'Location 1',
                'description' => 'Description 1'
            ],
            [
                'event_id' => 2,
                'event_name' => 'Test Event 2',
                'event_date' => '2025-12-02',
                'event_time' => '14:00:00',
                'event_location' => 'Location 2',
                'description' => 'Description 2'
            ]
        ];

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->mockStmt->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($mockEvents);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $events = $this->model->findPaginated(0, 10);

        $this->assertIsArray($events);
        $this->assertCount(2, $events);
    }

    /**
     * Test that findPaginated() respects the limit parameter
     */
    public function testFindPaginatedRespectsLimit(): void
    {
        $mockEvents = [
            [
                'event_id' => 1,
                'event_name' => 'Test Event 1',
                'event_date' => '2025-12-01',
                'event_time' => '10:00:00',
                'event_location' => 'Location 1',
                'description' => 'Description 1'
            ]
        ];

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->mockStmt->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($mockEvents);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $events = $this->model->findPaginated(0, 5);

        $this->assertIsArray($events);
        $this->assertLessThanOrEqual(5, count($events));
    }

    /**
     * Test that findPaginated() returns empty array with invalid offset
     */
    public function testFindPaginatedReturnsEmptyArrayWithInvalidOffset(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->mockStmt->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn([]);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $events = $this->model->findPaginated(999999, 10);

        $this->assertIsArray($events);
        $this->assertEmpty($events);
    }

    /**
     * Test that findPaginated() returns events with correct structure
     */
    public function testFindPaginatedReturnsEventsWithCorrectStructure(): void
    {
        $mockEvents = [
            [
                'event_id' => 1,
                'event_name' => 'Test Event',
                'slug' => 'test-event',
                'event_date' => '2025-12-01',
                'event_time' => '10:00:00',
                'event_location' => 'Test Location',
                'description' => 'Test Description'
            ]
        ];

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->mockStmt->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($mockEvents);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $events = $this->model->findPaginated(0, 1);

        $this->assertNotEmpty($events);
        $event = $events[0];

        $expectedKeys = ['event_id', 'event_name', 'slug', 'event_date', 'event_time', 'event_location', 'description'];
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $event);
        }
    }

    /**
     * Test that findPaginated() works with zero offset
     */
    public function testFindPaginatedWithZeroOffset(): void
    {
        $mockEvents = [];

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->mockStmt->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($mockEvents);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $events = $this->model->findPaginated(0, 5);

        $this->assertIsArray($events);
    }

    /**
     * Test that findPaginated() returns events ordered by date descending
     */
    public function testFindPaginatedOrderedByDateDescending(): void
    {
        $mockEvents = [
            [
                'event_id' => 1,
                'event_name' => 'Recent Event',
                'event_date' => '2025-12-02',
                'event_time' => '14:00:00',
                'event_location' => 'Location 1',
                'description' => 'Description 1'
            ],
            [
                'event_id' => 2,
                'event_name' => 'Older Event',
                'event_date' => '2025-12-01',
                'event_time' => '10:00:00',
                'event_location' => 'Location 2',
                'description' => 'Description 2'
            ]
        ];

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->mockStmt->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($mockEvents);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $events = $this->model->findPaginated(0, 10);

        if (count($events) >= 2) {
            $this->assertGreaterThanOrEqual(
                strtotime($events[1]['event_date']),
                strtotime($events[0]['event_date'])
            );
        }

        $this->assertIsArray($events);
    }

    /**
     * Test that findAll() returns all events
     */
    public function testFindAllReturnsAllEvents(): void
    {
        $mockEvents = [
            [
                'event_id' => 1,
                'event_name' => 'Event 1',
                'event_date' => '2025-12-01',
                'event_time' => '10:00:00',
                'description' => 'Description 1'
            ],
            [
                'event_id' => 2,
                'event_name' => 'Event 2',
                'event_date' => '2025-12-02',
                'event_time' => '14:00:00',
                'description' => 'Description 2'
            ]
        ];

        $this->mockStmt = $this->createMock(PDOStatement::class);
        $this->mockStmt->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($mockEvents);

        $this->mockPdo->expects($this->once())
            ->method('query')
            ->with('SELECT event_id, event_name, slug, event_date, event_time, description FROM EVENTS')
            ->willReturn($this->mockStmt);

        $result = $this->model->findAll();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    /**
     * Test that findAll() returns empty array when query fails
     */
    public function testFindAllReturnsEmptyArrayOnError(): void
    {
        $this->mockPdo->expects($this->once())
            ->method('query')
            ->willReturn(false);

        $result = $this->model->findAll();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test that findLatestEvents() returns upcoming events
     */
    public function testFindLatestEventsReturnsUpcomingEvents(): void
    {
        $mockEvents = [
            [
                'event_id' => 1,
                'event_name' => 'Upcoming Event',
                'event_date' => '2025-12-15',
                'event_time' => '10:00:00',
                'event_location' => 'Location 1',
                'description' => 'Description 1',
                'images' => '[]'
            ]
        ];

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->mockStmt->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($mockEvents);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->model->findLatestEvents(5);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
    }

    // =========================================================================
    // WRITE OPERATIONS TESTS
    // =========================================================================

    /**
     * Test that insertEvent() returns true on successful insertion
     *
     * Since slug migration, insertEvent() generates a slug via SlugGenerator
     * and checks uniqueness via slugExists(). Mock must handle both calls.
     */
    public function testInsertEventReturnsTrue(): void
    {
        $eventName = 'Test Event';
        $eventDate = new DateTime('2024-12-25');
        $eventTime = new DateTime('18:00');
        $eventLocation = 'Paris';
        $eventTheme = 'Conference';
        $statusParticipating = 'BUT 1,BUT 2';
        $description = 'Test description';
        $images = '["image1.jpg","image2.jpg"]';
        $isGroupEvent = false;
        $teamSize = 1;

        // Mock slug existence check (slug doesn't exist)
        $checkStmt = $this->createMock(PDOStatement::class);
        $checkStmt->method('execute')->willReturn(true);
        $checkStmt->method('fetchColumn')->willReturn(0);

        $insertStmt = $this->createMock(PDOStatement::class);
        $insertStmt->expects($this->once())
            ->method('execute')
            ->with([
                ':event_name' => $eventName,
                ':slug' => 'test-event',
                ':event_date' => '2024-12-25',
                ':event_time' => '18:00',
                ':event_location' => $eventLocation,
                ':event_theme' => $eventTheme,
                ':status_participating' => $statusParticipating,
                ':description' => $description,
                ':images' => $images,
                ':is_group_event' => 0,
                ':team_size' => 1
            ])
            ->willReturn(true);

        $this->mockPdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($checkStmt, $insertStmt);

        $result = $this->model->insertEvent(
            $eventName,
            $eventDate,
            $eventTime,
            $eventLocation,
            $eventTheme,
            $statusParticipating,
            $description,
            $images,
            $isGroupEvent,
            $teamSize
        );

        $this->assertTrue($result);
    }

    /**
     * Test that insertEvent() works without images
     *
     * Slug migration: mock slug existence check before insert.
     */
    public function testInsertEventWithoutImages(): void
    {
        $eventName = 'Simple Event';
        $eventDate = new DateTime('2024-11-15');
        $eventTime = new DateTime('14:30');
        $eventLocation = 'Lyon';
        $eventTheme = 'Workshop';
        $statusParticipating = 'BUT 3';
        $description = 'Simple event description';

        $checkStmt = $this->createMock(PDOStatement::class);
        $checkStmt->method('execute')->willReturn(true);
        $checkStmt->method('fetchColumn')->willReturn(0);

        $insertStmt = $this->createMock(PDOStatement::class);
        $insertStmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) {
                return $params[':images'] === '' &&
                       $params[':slug'] === 'simple-event';
            }))
            ->willReturn(true);

        $this->mockPdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($checkStmt, $insertStmt);

        $result = $this->model->insertEvent(
            $eventName,
            $eventDate,
            $eventTime,
            $eventLocation,
            $eventTheme,
            $statusParticipating,
            $description
        );

        $this->assertTrue($result);
    }

    /**
     * Test that insertEvent() generates unique slug when base slug exists
     *
     * When "Test Event" slug already exists, should use "test-event-2"
     */
    public function testInsertEventGeneratesUniqueSlugWhenExists(): void
    {
        $checkStmt1 = $this->createMock(PDOStatement::class);
        $checkStmt1->method('execute')->willReturn(true);
        $checkStmt1->method('fetchColumn')->willReturn(1); // test-event exists

        $checkStmt2 = $this->createMock(PDOStatement::class);
        $checkStmt2->method('execute')->willReturn(true);
        $checkStmt2->method('fetchColumn')->willReturn(0); // test-event-2 doesn't exist

        $insertStmt = $this->createMock(PDOStatement::class);
        $insertStmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) {
                return $params[':slug'] === 'test-event-2';
            }))
            ->willReturn(true);

        $this->mockPdo->expects($this->exactly(3))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($checkStmt1, $checkStmt2, $insertStmt);

        $result = $this->model->insertEvent(
            'Test Event',
            new DateTime('2025-01-01'),
            new DateTime('10:00'),
            'Paris',
            'Conference',
            'BDE',
            'Description'
        );

        $this->assertTrue($result);
    }

    /**
     * Test that insertEvent() formats DateTime correctly
     *
     * Slug migration: mock slug existence check before insert.
     */
    public function testInsertEventFormatsDateTimeCorrectly(): void
    {
        $eventDate = new DateTime('2025-01-01');
        $eventTime = new DateTime('23:59');

        $checkStmt = $this->createMock(PDOStatement::class);
        $checkStmt->method('execute')->willReturn(true);
        $checkStmt->method('fetchColumn')->willReturn(0);

        $insertStmt = $this->createMock(PDOStatement::class);
        $insertStmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) {
                return $params[':event_date'] === '2025-01-01' &&
                    $params[':event_time'] === '23:59' &&
                    $params[':slug'] === 'new-year-event';
            }))
            ->willReturn(true);

        $this->mockPdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($checkStmt, $insertStmt);

        $result = $this->model->insertEvent(
            'New Year Event',
            $eventDate,
            $eventTime,
            'City Center',
            'Party',
            'BDE',
            'Celebrate the new year!'
        );

        $this->assertTrue($result);
    }

    /**
     * Test that updateEvent() returns true on successful update
     *
     * Slug migration: updateEvent() regenerates slug via slugExistsExcludingId().
     * Mock must handle slug check call before update call.
     */
    public function testUpdateEventReturnsTrue(): void
    {
        $eventId = 1;
        $eventName = 'Updated Event';
        $eventDate = new DateTime('2025-01-15');
        $eventTime = new DateTime('16:00');
        $eventLocation = 'Updated Location';
        $eventTheme = 'Updated Theme';
        $statusParticipating = 'BUT 1,BUT 2,BUT 3';
        $description = 'Updated description';
        $images = '["updated.jpg"]';
        $isGroupEvent = true;
        $teamSize = 5;

        $checkStmt = $this->createMock(PDOStatement::class);
        $checkStmt->method('execute')->willReturn(true);
        $checkStmt->method('fetchColumn')->willReturn(0);

        $updateStmt = $this->createMock(PDOStatement::class);
        $updateStmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) use ($eventId, $eventName) {
                return $params[':event_id'] === $eventId &&
                    $params[':event_name'] === $eventName &&
                    $params[':slug'] === 'updated-event' &&
                    $params[':event_date'] === '2025-01-15' &&
                    $params[':event_time'] === '16:00';
            }))
            ->willReturn(true);

        $this->mockPdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($checkStmt, $updateStmt);

        $result = $this->model->updateEvent(
            $eventId,
            $eventName,
            $eventDate,
            $eventTime,
            $eventLocation,
            $eventTheme,
            $statusParticipating,
            $description,
            $images,
            $isGroupEvent,
            $teamSize
        );

        $this->assertTrue($result);
    }

    /**
     * Test that updateEventImages() returns true on successful update
     */
    public function testUpdateEventImagesReturnsTrue(): void
    {
        $eventId = 1;
        $imageJson = '["image1.jpg","image2.jpg"]';

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([
                ':images' => $imageJson,
                ':id' => $eventId
            ])
            ->willReturn(true);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with('UPDATE EVENTS SET images = :images WHERE event_id = :id')
            ->willReturn($this->mockStmt);

        $result = $this->model->updateEventImages($eventId, $imageJson);

        $this->assertTrue($result);
    }

    /**
     * Test that deleteEvent() returns true on successful deletion
     */
    public function testDeleteEventReturnsTrue(): void
    {
        $eventId = 42;

        $this->mockStmt->expects($this->once())
            ->method('bindParam')
            ->with(':event_id', $eventId, PDO::PARAM_INT);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with('DELETE FROM EVENTS WHERE event_id = :event_id')
            ->willReturn($this->mockStmt);

        $result = $this->model->deleteEvent($eventId);
        $this->assertTrue($result);
    }

    /**
     * Test that deleteEvent() returns false on execution failure
     */
    public function testDeleteEventReturnsFalseOnFailure(): void
    {
        $eventId = 99;

        $this->mockStmt->expects($this->once())
            ->method('bindParam');

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(false);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->model->deleteEvent($eventId);
        $this->assertFalse($result);
    }

    /**
     * Test that deleteEvent() returns false on PDO exception
     */
    public function testDeleteEventReturnsFalseOnException(): void
    {
        $eventId = 123;

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->will($this->throwException(new PDOException('Database error')));

        $result = $this->model->deleteEvent($eventId);
        $this->assertFalse($result);
    }
}
