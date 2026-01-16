<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use PHPUnit\Framework\TestCase;
use App\Modules\Repositories\EventTeamRepository;
use App\Core\Database;
use PDO;
use PDOStatement;
use ReflectionClass;

/**
 * Unit tests for EventTeamRepository
 *
 * Tests the core functionality of team management for group event registrations.
 * Coverage target: 70% of critical methods.
 */
class EventTeamRepositoryTest extends TestCase
{
    private EventTeamRepository $repository;
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

        $this->repository = new EventTeamRepository();
    }

    protected function tearDown(): void
    {
        $reflection = new ReflectionClass(Database::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null, null);
    }

    // ========== createTeam Tests ==========

    public function testCreateTeamReturnsTeamId(): void
    {
        $eventId = 1;
        $creatorUserId = 10;
        $expectedTeamId = 5;

        // Mock for getNextTeamNumber
        $mockStmtNumber = $this->createMock(PDOStatement::class);
        $mockStmtNumber->method('execute')->willReturn(true);
        $mockStmtNumber->method('fetchColumn')->willReturn(1);

        // Mock for INSERT
        $mockStmtInsert = $this->createMock(PDOStatement::class);
        $mockStmtInsert->method('execute')->willReturn(true);

        $this->mockPdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($mockStmtNumber, $mockStmtInsert);

        $this->mockPdo->method('lastInsertId')->willReturn((string) $expectedTeamId);

        $result = $this->repository->createTeam($eventId, $creatorUserId);

        $this->assertEquals($expectedTeamId, $result);
    }

    public function testCreateTeamReturnsNullOnFailure(): void
    {
        $eventId = 1;
        $creatorUserId = 10;

        // Mock for getNextTeamNumber
        $mockStmtNumber = $this->createMock(PDOStatement::class);
        $mockStmtNumber->method('execute')->willReturn(true);
        $mockStmtNumber->method('fetchColumn')->willReturn(1);

        // Mock for INSERT that throws exception
        $mockStmtInsert = $this->createMock(PDOStatement::class);
        $mockStmtInsert->method('execute')->willThrowException(new \PDOException('Insert failed'));

        $this->mockPdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($mockStmtNumber, $mockStmtInsert);

        $result = $this->repository->createTeam($eventId, $creatorUserId);

        $this->assertNull($result);
    }

    // ========== findById Tests ==========

    public function testFindByIdReturnsTeamData(): void
    {
        $teamId = 5;
        $expectedData = [
            'team_id' => 5,
            'event_id' => 1,
            'team_number' => 1,
            'creator_user_id' => 10,
            'status' => 'pending'
        ];

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([':team_id' => $teamId])
            ->willReturn(true);

        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expectedData);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with('SELECT * FROM EVENT_TEAMS WHERE team_id = :team_id')
            ->willReturn($this->mockStmt);

        $result = $this->repository->findById($teamId);

        $this->assertEquals($expectedData, $result);
    }

    public function testFindByIdReturnsNullWhenNotFound(): void
    {
        $teamId = 999;

        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetch')->willReturn(false);

        $this->mockPdo->method('prepare')->willReturn($this->mockStmt);

        $result = $this->repository->findById($teamId);

        $this->assertNull($result);
    }

    // ========== updateStatus Tests ==========

    public function testUpdateStatusReturnsTrue(): void
    {
        $teamId = 5;
        $status = 'confirmed';

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([':team_id' => $teamId, ':status' => $status])
            ->willReturn(true);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with('UPDATE EVENT_TEAMS SET status = :status WHERE team_id = :team_id')
            ->willReturn($this->mockStmt);

        $result = $this->repository->updateStatus($teamId, $status);

        $this->assertTrue($result);
    }

    public function testUpdateStatusReturnsFalseOnFailure(): void
    {
        $teamId = 5;
        $status = 'confirmed';

        $this->mockStmt->method('execute')->willReturn(false);
        $this->mockPdo->method('prepare')->willReturn($this->mockStmt);

        $result = $this->repository->updateStatus($teamId, $status);

        $this->assertFalse($result);
    }

    public function testUpdateStatusWithDifferentStatuses(): void
    {
        $teamId = 5;
        $statuses = ['pending', 'confirmed', 'cancelled'];

        foreach ($statuses as $status) {
            $mockStmt = $this->createMock(PDOStatement::class);
            $mockStmt->expects($this->once())
                ->method('execute')
                ->with($this->callback(function ($params) use ($status) {
                    return $params[':status'] === $status;
                }))
                ->willReturn(true);

            $mockPdo = $this->createMock(PDO::class);
            $mockPdo->method('prepare')->willReturn($mockStmt);

            $mockDatabase = $this->createMock(Database::class);
            $mockDatabase->method('getConnection')->willReturn($mockPdo);

            $reflection = new ReflectionClass(Database::class);
            $instanceProperty = $reflection->getProperty('instance');
            $instanceProperty->setAccessible(true);
            $instanceProperty->setValue(null, $mockDatabase);

            $repository = new EventTeamRepository();
            $result = $repository->updateStatus($teamId, $status);

            $this->assertTrue($result, "Failed for status: $status");
        }
    }

    // ========== deleteTeam Tests ==========

    public function testDeleteTeamReturnsTrue(): void
    {
        $teamId = 5;

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([':team_id' => $teamId])
            ->willReturn(true);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with('DELETE FROM EVENT_TEAMS WHERE team_id = :team_id')
            ->willReturn($this->mockStmt);

        $result = $this->repository->deleteTeam($teamId);

        $this->assertTrue($result);
    }

    public function testDeleteTeamReturnsFalseOnFailure(): void
    {
        $teamId = 5;

        $this->mockStmt->method('execute')->willReturn(false);
        $this->mockPdo->method('prepare')->willReturn($this->mockStmt);

        $result = $this->repository->deleteTeam($teamId);

        $this->assertFalse($result);
    }

    // ========== deleteTeamsByEvent Tests ==========

    public function testDeleteTeamsByEventReturnsCount(): void
    {
        $eventId = 1;
        $deletedCount = 3;

        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('rowCount')->willReturn($deletedCount);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with('DELETE FROM EVENT_TEAMS WHERE event_id = :event_id')
            ->willReturn($this->mockStmt);

        $result = $this->repository->deleteTeamsByEvent($eventId);

        $this->assertEquals($deletedCount, $result);
    }

    public function testDeleteTeamsByEventReturnsZeroOnFailure(): void
    {
        $eventId = 1;

        $this->mockStmt->method('execute')->willThrowException(new \PDOException('Delete failed'));
        $this->mockPdo->method('prepare')->willReturn($this->mockStmt);

        $result = $this->repository->deleteTeamsByEvent($eventId);

        $this->assertEquals(0, $result);
    }

    // ========== getTeamsByEvent Tests ==========

    public function testGetTeamsByEventReturnsTeams(): void
    {
        $eventId = 1;
        $expectedTeams = [
            ['team_id' => 1, 'team_number' => 1, 'status' => 'confirmed'],
            ['team_id' => 2, 'team_number' => 2, 'status' => 'pending']
        ];

        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetchAll')->willReturn($expectedTeams);

        $this->mockPdo->method('prepare')->willReturn($this->mockStmt);

        $result = $this->repository->getTeamsByEvent($eventId);

        $this->assertEquals($expectedTeams, $result);
    }

    public function testGetTeamsByEventWithStatusFilter(): void
    {
        $eventId = 1;
        $status = 'confirmed';
        $expectedTeams = [
            ['team_id' => 1, 'team_number' => 1, 'status' => 'confirmed']
        ];

        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetchAll')->willReturn($expectedTeams);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('status = :status'))
            ->willReturn($this->mockStmt);

        $result = $this->repository->getTeamsByEvent($eventId, $status);

        $this->assertEquals($expectedTeams, $result);
    }

    public function testGetTeamsByEventReturnsEmptyArrayWhenNoTeams(): void
    {
        $eventId = 999;

        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetchAll')->willReturn([]);

        $this->mockPdo->method('prepare')->willReturn($this->mockStmt);

        $result = $this->repository->getTeamsByEvent($eventId);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    // ========== isUserInAnyTeam Tests ==========

    public function testIsUserInAnyTeamReturnsTrueWhenInTeam(): void
    {
        $eventId = 1;
        $userId = 10;

        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetchColumn')->willReturn(1);

        $this->mockPdo->method('prepare')->willReturn($this->mockStmt);

        $result = $this->repository->isUserInAnyTeam($eventId, $userId);

        $this->assertTrue($result);
    }

    public function testIsUserInAnyTeamReturnsFalseWhenNotInTeam(): void
    {
        $eventId = 1;
        $userId = 999;

        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetchColumn')->willReturn(0);

        $this->mockPdo->method('prepare')->willReturn($this->mockStmt);

        $result = $this->repository->isUserInAnyTeam($eventId, $userId);

        $this->assertFalse($result);
    }
}
