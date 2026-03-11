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

    // --- Tests for manual modification of registrants (individual) ---

    public function testRegisterUsersReturnsCountWhenSuccessful(): void
    {
        $eventId = 10;
        $userIds = [101, 102, 103];

        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->expects($this->exactly(3))
            ->method('execute')
            ->willReturn(true);
        $mockStmt->expects($this->exactly(3))
            ->method('rowCount')
            ->willReturnOnConsecutiveCalls(1, 1, 0); // 3e déjà inscrit (INSERT IGNORE)

        $this->mockPdo->expects($this->exactly(3))
            ->method('prepare')
            ->with($this->stringContains('INSERT IGNORE INTO EVENT_REGISTRATIONS'))
            ->willReturn($mockStmt);

        $result = $this->repository->registerUsers($eventId, $userIds);

        $this->assertEquals(2, $result);
    }

    public function testRegisterUsersReturnsZeroWhenEmpty(): void
    {
        $eventId = 10;
        $userIds = [];

        $result = $this->repository->registerUsers($eventId, $userIds);

        $this->assertEquals(0, $result);
    }

    public function testRegisterUsersReturnsZeroOnPdoException(): void
    {
        $eventId = 10;
        $userIds = [101];

        $this->mockStmt->method('execute')->willThrowException(new \PDOException('DB error'));
        $this->mockPdo->method('prepare')->willReturn($this->mockStmt);

        $result = $this->repository->registerUsers($eventId, $userIds);

        $this->assertEquals(0, $result);
    }

    public function testUnregisterUsersReturnsCountWhenSuccessful(): void
    {
        $eventId = 20;
        $userIds = [201, 202, 203];
        $deletedCount = 3;

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) use ($eventId, $userIds) {
                return $params[0] === $eventId && count($params) === 4;
            }))
            ->willReturn(true);
        $this->mockStmt->expects($this->once())
            ->method('rowCount')
            ->willReturn($deletedCount);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('DELETE FROM EVENT_REGISTRATIONS'))
            ->willReturn($this->mockStmt);

        $result = $this->repository->unregisterUsers($eventId, $userIds);

        $this->assertEquals($deletedCount, $result);
    }

    public function testUnregisterUsersReturnsZeroWhenEmpty(): void
    {
        $eventId = 20;
        $userIds = [];

        $result = $this->repository->unregisterUsers($eventId, $userIds);

        $this->assertEquals(0, $result);
    }

    public function testUnregisterUsersReturnsZeroOnPdoException(): void
    {
        $eventId = 20;
        $userIds = [201];

        $this->mockStmt->method('execute')->willThrowException(new \PDOException('Delete failed'));
        $this->mockPdo->method('prepare')->willReturn($this->mockStmt);

        $result = $this->repository->unregisterUsers($eventId, $userIds);

        $this->assertEquals(0, $result);
    }

    // --- Tests for manual modification of registrants (group) ---

    public function testAddUserToGroupReturnsTrue(): void
    {
        $eventId = 30;
        $userId = 301;
        $teamId = 5;

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) use ($eventId, $userId, $teamId) {
                return $params[':event_id'] === $eventId
                    && $params[':user_id'] === $userId
                    && $params[':team_id'] === $teamId
                    && $params[':status'] === 'Confirmé';
            }))
            ->willReturn(true);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('INSERT IGNORE INTO EVENT_REGISTRATIONS'))
            ->willReturn($this->mockStmt);

        $result = $this->repository->addUserToGroup($eventId, $userId, $teamId);

        $this->assertTrue($result);
    }

    public function testAddUserToGroupReturnsFalseOnPdoException(): void
    {
        $eventId = 30;
        $userId = 301;
        $teamId = 5;

        $this->mockStmt->method('execute')->willThrowException(new \PDOException('Insert failed'));
        $this->mockPdo->method('prepare')->willReturn($this->mockStmt);

        $result = $this->repository->addUserToGroup($eventId, $userId, $teamId);

        $this->assertFalse($result);
    }

    public function testRemoveUserFromGroupReturnsTrue(): void
    {
        $eventId = 40;
        $userId = 401;

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([':event_id' => $eventId, ':user_id' => $userId])
            ->willReturn(true);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('DELETE FROM EVENT_REGISTRATIONS'))
            ->willReturn($this->mockStmt);

        $result = $this->repository->removeUserFromGroup($eventId, $userId);

        $this->assertTrue($result);
    }

    public function testRemoveUserFromGroupReturnsFalseOnPdoException(): void
    {
        $eventId = 40;
        $userId = 401;

        $this->mockStmt->method('execute')->willThrowException(new \PDOException('Delete failed'));
        $this->mockPdo->method('prepare')->willReturn($this->mockStmt);

        $result = $this->repository->removeUserFromGroup($eventId, $userId);

        $this->assertFalse($result);
    }

    public function testChangeUserTeamReturnsTrue(): void
    {
        $eventId = 50;
        $userId = 501;
        $newTeamId = 7;

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) use ($eventId, $userId, $newTeamId) {
                return $params[':event_id'] === $eventId
                    && $params[':user_id'] === $userId
                    && $params[':team_id'] === $newTeamId;
            }))
            ->willReturn(true);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('UPDATE EVENT_REGISTRATIONS SET team_id'))
            ->willReturn($this->mockStmt);

        $result = $this->repository->changeUserTeam($eventId, $userId, $newTeamId);

        $this->assertTrue($result);
    }

    public function testChangeUserTeamReturnsFalseOnPdoException(): void
    {
        $eventId = 50;
        $userId = 501;
        $newTeamId = 7;

        $this->mockStmt->method('execute')->willThrowException(new \PDOException('Update failed'));
        $this->mockPdo->method('prepare')->willReturn($this->mockStmt);

        $result = $this->repository->changeUserTeam($eventId, $userId, $newTeamId);

        $this->assertFalse($result);
    }

    public function testDeleteGroupRegistrantsReturnsCount(): void
    {
        $eventId = 60;
        $teamId = 8;
        $deletedCount = 4;

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([':event_id' => $eventId, ':team_id' => $teamId])
            ->willReturn(true);
        $this->mockStmt->expects($this->once())
            ->method('rowCount')
            ->willReturn($deletedCount);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('DELETE FROM EVENT_REGISTRATIONS'))
            ->willReturn($this->mockStmt);

        $result = $this->repository->deleteGroupRegistrants($eventId, $teamId);

        $this->assertEquals($deletedCount, $result);
    }

    public function testDeleteGroupRegistrantsReturnsZeroOnPdoException(): void
    {
        $eventId = 60;
        $teamId = 8;

        $this->mockStmt->method('execute')->willThrowException(new \PDOException('Delete failed'));
        $this->mockPdo->method('prepare')->willReturn($this->mockStmt);

        $result = $this->repository->deleteGroupRegistrants($eventId, $teamId);

        $this->assertEquals(0, $result);
    }

    public function testRegisterUserWithTeamReturnsTrue(): void
    {
        $eventId = 70;
        $userId = 701;
        $teamId = 9;

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([$eventId, $userId, 'Confirmé', $teamId])
            ->willReturn(true);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('INSERT INTO EVENT_REGISTRATIONS'))
            ->willReturn($this->mockStmt);

        $result = $this->repository->registerUserWithTeam($eventId, $userId, $teamId);

        $this->assertTrue($result);
    }

    public function testGetTeamIdByUserAndEventReturnsTeamId(): void
    {
        $userId = 801;
        $eventId = 80;
        $teamId = 10;

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([$userId, $eventId])
            ->willReturn(true);
        $this->mockStmt->expects($this->once())
            ->method('fetchColumn')
            ->willReturn((string) $teamId);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT team_id FROM EVENT_REGISTRATIONS'))
            ->willReturn($this->mockStmt);

        $result = $this->repository->getTeamIdByUserAndEvent($userId, $eventId);

        $this->assertEquals($teamId, $result);
    }

    public function testGetTeamIdByUserAndEventReturnsNullWhenNotInTeam(): void
    {
        $userId = 801;
        $eventId = 80;

        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetchColumn')->willReturn(false);

        $this->mockPdo->method('prepare')->willReturn($this->mockStmt);

        $result = $this->repository->getTeamIdByUserAndEvent($userId, $eventId);

        $this->assertNull($result);
    }
}
