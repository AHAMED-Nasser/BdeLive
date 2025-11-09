<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Admin;

use PHPUnit\Framework\TestCase;
use App\Modules\Models\Admin\EventCreationModel;
use App\Core\Database;
use PDO;
use PDOStatement;
use PDOException;
use ReflectionClass;
use DateTime;

/**
 * Unit tests for EventCreationModel
 */
class EventCreationModelTest extends TestCase
{
    private EventCreationModel $model;
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
        
        $this->model = new EventCreationModel();
    }

    protected function tearDown(): void
    {
        $reflection = new ReflectionClass(Database::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null, null);
    }

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

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([
                ':event_name' => $eventName,
                ':event_date' => '2024-12-25',
                ':event_time' => '18:00',
                ':event_location' => $eventLocation,
                ':event_theme' => $eventTheme,
                ':status_participating' => $statusParticipating,
                ':description' => $description,
                ':images' => $images
            ])
            ->willReturn(true);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->model->insertEvent(
            $eventName,
            $eventDate,
            $eventTime,
            $eventLocation,
            $eventTheme,
            $statusParticipating,
            $description,
            $images
        );

        $this->assertTrue($result);
    }

    public function testInsertEventWithoutImages(): void
    {
        $eventName = 'Simple Event';
        $eventDate = new DateTime('2024-11-15');
        $eventTime = new DateTime('14:30');
        $eventLocation = 'Lyon';
        $eventTheme = 'Workshop';
        $statusParticipating = 'BUT 3';
        $description = 'Simple event description';

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) {
                return $params[':images'] === '';
            }))
            ->willReturn(true);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

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

    public function testDeleteEventReturnsFalseOnException(): void
    {
        $eventId = 123;

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->will($this->throwException(new PDOException('Database error')));

        $result = $this->model->deleteEvent($eventId);
        $this->assertFalse($result);
    }

    public function testInsertEventFormatsDateTimeCorrectly(): void
    {
        $eventDate = new DateTime('2025-01-01');
        $eventTime = new DateTime('23:59');

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) {
                return $params[':event_date'] === '2025-01-01' &&
                       $params[':event_time'] === '23:59';
            }))
            ->willReturn(true);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

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
}
