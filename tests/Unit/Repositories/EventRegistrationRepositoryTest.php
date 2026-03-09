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

    /**
     * Verify that unregisterUserFromFutureEvents() removes only future-event
     * registrations and leaves past-event registrations untouched.
     *
     * Scenario:
     *   - User 42 is registered for event 1 (past) and event 2 (future).
     *   - After calling unregisterUserFromFutureEvents(42):
     *       * 1 row is affected (future event only).
     *       * isUserRegistered(1, 42) → true  (past registration intact).
     *       * isUserRegistered(2, 42) → false (future registration removed).
     *
     * Three prepare() calls are mocked in sequence:
     *   1. DELETE ... JOIN EVENTS  (unregisterUserFromFutureEvents)
     *   2. SELECT 1                (isUserRegistered — past event)
     *   3. SELECT 1                (isUserRegistered — future event)
     */
    public function testUnregisterUserFromFutureEvents(): void
    {
        $userId        = 42;
        $pastEventId   = 1;
        $futureEventId = 2;

        // Stmt 1 : DELETE er FROM EVENT_REGISTRATIONS er JOIN EVENTS e ...
        $mockDeleteStmt = $this->createMock(PDOStatement::class);
        $mockDeleteStmt->expects($this->once())
            ->method('execute')
            ->with([$userId])
            ->willReturn(true);
        $mockDeleteStmt->expects($this->once())
            ->method('rowCount')
            ->willReturn(1);

        // Stmt 2 : SELECT 1 — isUserRegistered(pastEventId, userId) → still registered
        $mockPastStmt = $this->createMock(PDOStatement::class);
        $mockPastStmt->expects($this->once())
            ->method('execute')
            ->with([$pastEventId, $userId]);
        $mockPastStmt->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(1);

        // Stmt 3 : SELECT 1 — isUserRegistered(futureEventId, userId) → no longer registered
        $mockFutureStmt = $this->createMock(PDOStatement::class);
        $mockFutureStmt->expects($this->once())
            ->method('execute')
            ->with([$futureEventId, $userId]);
        $mockFutureStmt->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(false);

        $this->mockPdo->expects($this->exactly(3))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($mockDeleteStmt, $mockPastStmt, $mockFutureStmt);

        $affectedRows     = $this->repository->unregisterUserFromFutureEvents($userId);
        $stillInPast      = $this->repository->isUserRegistered($pastEventId, $userId);
        $noLongerInFuture = $this->repository->isUserRegistered($futureEventId, $userId);

        $this->assertEquals(1, $affectedRows);
        $this->assertTrue($stillInPast);
        $this->assertFalse($noLongerInFuture);
    }
}
