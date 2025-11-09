<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use PHPUnit\Framework\TestCase;
use App\Modules\Repositories\EventRegistrationRepository;
use App\Core\Database;
use PDO;
use PDOStatement;
use ReflectionClass;

/**
 * Unit tests for EventRegistrationRepository
 */
class EventRegistrationRepositoryTest extends TestCase
{
    private EventRegistrationRepository $repository;
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
        
        $this->repository = new EventRegistrationRepository();
    }

    protected function tearDown(): void
    {
        $reflection = new ReflectionClass(Database::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null, null);
    }

    public function testIsUserRegisteredReturnsTrueWhenRegistered(): void
    {
        $eventId = 10;
        $userId = 123;

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([$eventId, $userId])
            ->willReturn(true);

        $this->mockStmt->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(1);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with('SELECT 1 FROM EVENT_REGISTRATIONS WHERE event_id = ? AND user_id = ?')
            ->willReturn($this->mockStmt);

        $result = $this->repository->isUserRegistered($eventId, $userId);

        $this->assertTrue($result);
    }

    public function testIsUserRegisteredReturnsFalseWhenNotRegistered(): void
    {
        $eventId = 10;
        $userId = 456;

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([$eventId, $userId])
            ->willReturn(true);

        $this->mockStmt->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(false);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->repository->isUserRegistered($eventId, $userId);

        $this->assertFalse($result);
    }

    public function testRegisterUserReturnsTrue(): void
    {
        $eventId = 20;
        $userId = 789;

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([$eventId, $userId, 'Confirmé'])
            ->willReturn(true);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with('INSERT INTO EVENT_REGISTRATIONS (event_id, user_id, registration_status) VALUES (?, ?, ?)')
            ->willReturn($this->mockStmt);

        $result = $this->repository->registerUser($eventId, $userId);

        $this->assertTrue($result);
    }

    public function testRegisterUserReturnsFalseOnFailure(): void
    {
        $eventId = 20;
        $userId = 789;

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(false);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->repository->registerUser($eventId, $userId);

        $this->assertFalse($result);
    }

    public function testRegisterUserSetsStatusToConfirme(): void
    {
        $eventId = 30;
        $userId = 111;

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) {
                return $params[2] === 'Confirmé';
            }))
            ->willReturn(true);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->repository->registerUser($eventId, $userId);

        $this->assertTrue($result);
    }

    public function testUnregisterUserReturnsTrue(): void
    {
        $eventId = 40;
        $userId = 222;

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([$eventId, $userId])
            ->willReturn(true);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with('DELETE FROM EVENT_REGISTRATIONS WHERE event_id = ? AND user_id = ?')
            ->willReturn($this->mockStmt);

        $result = $this->repository->unregisterUser($eventId, $userId);

        $this->assertTrue($result);
    }

    public function testUnregisterUserReturnsFalseOnFailure(): void
    {
        $eventId = 40;
        $userId = 222;

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(false);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->repository->unregisterUser($eventId, $userId);

        $this->assertFalse($result);
    }

    public function testIsUserRegisteredWithZeroIds(): void
    {
        $eventId = 0;
        $userId = 0;

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([$eventId, $userId]);

        $this->mockStmt->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(false);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->repository->isUserRegistered($eventId, $userId);

        $this->assertFalse($result);
    }
}
