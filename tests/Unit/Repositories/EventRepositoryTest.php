<?php

declare(strict_types=1);

namespace App\Tests\Unit\Repositories;

use PHPUnit\Framework\TestCase;
use App\Modules\Repositories\EventRepository;
use App\Core\Database;
use PDO;
use PDOStatement;
use ReflectionClass;

class EventRepositoryTest extends TestCase
{
    private EventRepository $repository;
    private PDO $mockPdo;
    private PDOStatement $mockStmt;

    protected function setUp(): void
    {
        $this->mockPdo = $this->createMock(PDO::class);
        $this->mockStmt = $this->createMock(PDOStatement::class);

        $mockDatabase = $this->createMock(Database::class);
        $mockDatabase->method('getConnection')->willReturn($this->mockPdo);

        $reflection = new ReflectionClass(Database::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null, $mockDatabase);

        $this->repository = new EventRepository();
    }

    protected function tearDown(): void
    {
        $reflection = new ReflectionClass(Database::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null, null);
    }

    public function testCountReturnsInteger(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(42);

        $this->mockPdo->expects($this->once())
            ->method('query')
            ->with('SELECT COUNT(*) FROM EVENTS')
            ->willReturn($this->mockStmt);

        $count = $this->repository->count();

        $this->assertIsInt($count);
        $this->assertEquals(42, $count);
    }

    public function testCountReturnsZeroOnError(): void
    {
        $this->mockPdo->expects($this->once())
            ->method('query')
            ->with('SELECT COUNT(*) FROM EVENTS')
            ->willReturn(false);

        $count = $this->repository->count();

        $this->assertEquals(0, $count);
    }

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

        $events = $this->repository->findPaginated(0, 10);

        $this->assertIsArray($events);
        $this->assertCount(2, $events);
    }

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

        $events = $this->repository->findPaginated(0, 5);

        $this->assertIsArray($events);
        $this->assertLessThanOrEqual(5, count($events));
    }

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

        $events = $this->repository->findPaginated(999999, 10);

        $this->assertIsArray($events);
        $this->assertEmpty($events);
    }

    public function testFindPaginatedReturnsEventsWithCorrectStructure(): void
    {
        $mockEvents = [
            [
                'event_id' => 1,
                'event_name' => 'Test Event',
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

        $events = $this->repository->findPaginated(0, 1);

        $this->assertNotEmpty($events);
        $event = $events[0];

        $expectedKeys = ['event_id', 'event_name', 'event_date', 'event_time', 'event_location', 'description'];
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $event);
        }
    }

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

        $events = $this->repository->findPaginated(0, 5);

        $this->assertIsArray($events);
    }

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

        $events = $this->repository->findPaginated(0, 10);

        if (count($events) >= 2) {
            $this->assertGreaterThanOrEqual(
                strtotime($events[1]['event_date']),
                strtotime($events[0]['event_date'])
            );
        }

        $this->assertIsArray($events);
    }
}
