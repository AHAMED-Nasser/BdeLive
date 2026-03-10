<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use PHPUnit\Framework\TestCase;
use App\Modules\Repositories\EventTeamInvitationRepository;
use App\Core\Database;
use PDO;
use PDOStatement;
use ReflectionClass;

/**
 * Unit tests for EventTeamInvitationRepository
 *
 * Tests the core functionality of team invitation management for group event registrations.
 * Coverage target: 70% of critical methods.
 */
class EventTeamInvitationRepositoryTest extends TestCase
{
    private EventTeamInvitationRepository $repository;
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

        $this->repository = new EventTeamInvitationRepository();
    }

    protected function tearDown(): void
    {
        $reflection = new ReflectionClass(Database::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null, null);
    }


    public function testCreateInvitationReturnsToken(): void
    {
        $teamId = 1;
        $email = 'test@example.com';

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('INSERT INTO EVENT_TEAM_INVITATIONS'))
            ->willReturn($this->mockStmt);

        $result = $this->repository->createInvitation($teamId, $email);

        $this->assertNotNull($result);
        $this->assertIsString($result);
        $this->assertEquals(64, strlen($result)); // 32 bytes = 64 hex characters
    }

    public function testCreateInvitationWithUserId(): void
    {
        $teamId = 1;
        $email = 'test@example.com';
        $userId = 10;

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) use ($userId) {
                return $params[':user_id'] === $userId;
            }))
            ->willReturn(true);

        $this->mockPdo->method('prepare')->willReturn($this->mockStmt);

        $result = $this->repository->createInvitation($teamId, $email, $userId);

        $this->assertNotNull($result);
    }

    public function testCreateInvitationReturnsNullOnFailure(): void
    {
        $teamId = 1;
        $email = 'test@example.com';

        $this->mockStmt->method('execute')
            ->willThrowException(new \PDOException('Insert failed'));

        $this->mockPdo->method('prepare')->willReturn($this->mockStmt);

        $result = $this->repository->createInvitation($teamId, $email);

        $this->assertNull($result);
    }


    public function testFindByTokenReturnsInvitationData(): void
    {
        $token = 'abc123def456';
        $expectedData = [
            'invitation_id' => 1,
            'team_id' => 1,
            'email' => 'test@example.com',
            'validation_status' => 'pending',
            'event_id' => 1,
            'team_number' => 1,
            'event_name' => 'Test Event'
        ];

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([':token' => $token])
            ->willReturn(true);

        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expectedData);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('validation_token = :token'))
            ->willReturn($this->mockStmt);

        $result = $this->repository->findByToken($token);

        $this->assertEquals($expectedData, $result);
    }

    public function testFindByTokenReturnsNullWhenNotFound(): void
    {
        $token = 'nonexistent_token';

        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetch')->willReturn(false);

        $this->mockPdo->method('prepare')->willReturn($this->mockStmt);

        $result = $this->repository->findByToken($token);

        $this->assertNull($result);
    }

    public function testFindByTokenReturnsNullOnException(): void
    {
        $token = 'abc123';

        $this->mockStmt->method('execute')
            ->willThrowException(new \PDOException('Query failed'));

        $this->mockPdo->method('prepare')->willReturn($this->mockStmt);

        $result = $this->repository->findByToken($token);

        $this->assertNull($result);
    }


    public function testUpdateValidationStatusReturnsTrue(): void
    {
        $invitationId = 1;
        $status = 'confirmed';

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) use ($invitationId, $status) {
                return $params[':status'] === $status && $params[':id'] === $invitationId;
            }))
            ->willReturn(true);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('validation_status = :status'))
            ->willReturn($this->mockStmt);

        $result = $this->repository->updateValidationStatus($invitationId, $status);

        $this->assertTrue($result);
    }

    public function testUpdateValidationStatusWithUserId(): void
    {
        $invitationId = 1;
        $status = 'confirmed';
        $userId = 10;

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) use ($userId) {
                return isset($params[':user_id']) && $params[':user_id'] === $userId;
            }))
            ->willReturn(true);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('user_id = :user_id'))
            ->willReturn($this->mockStmt);

        $result = $this->repository->updateValidationStatus($invitationId, $status, $userId);

        $this->assertTrue($result);
    }

    public function testUpdateValidationStatusReturnsFalseOnFailure(): void
    {
        $invitationId = 1;
        $status = 'confirmed';

        $this->mockStmt->method('execute')->willReturn(false);
        $this->mockPdo->method('prepare')->willReturn($this->mockStmt);

        $result = $this->repository->updateValidationStatus($invitationId, $status);

        $this->assertFalse($result);
    }

    public function testUpdateValidationStatusWithDifferentStatuses(): void
    {
        $invitationId = 1;
        $statuses = ['pending', 'confirmed', 'declined'];

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

            $repository = new EventTeamInvitationRepository();
            $result = $repository->updateValidationStatus($invitationId, $status);

            $this->assertTrue($result, "Failed for status: $status");
        }
    }


    public function testAreAllInvitationsConfirmedReturnsTrueWhenAllConfirmed(): void
    {
        $teamId = 1;

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([':team_id' => $teamId])
            ->willReturn(true);

        $this->mockStmt->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(0); // 0 non-confirmed invitations means all are confirmed

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains("validation_status != 'confirmed'"))
            ->willReturn($this->mockStmt);

        $result = $this->repository->areAllInvitationsConfirmed($teamId);

        $this->assertTrue($result);
    }

    public function testAreAllInvitationsConfirmedReturnsFalseWhenNotAllConfirmed(): void
    {
        $teamId = 1;

        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetchColumn')->willReturn(2); // 2 non-confirmed

        $this->mockPdo->method('prepare')->willReturn($this->mockStmt);

        $result = $this->repository->areAllInvitationsConfirmed($teamId);

        $this->assertFalse($result);
    }

    public function testAreAllInvitationsConfirmedReturnsFalseOnException(): void
    {
        $teamId = 1;

        $this->mockStmt->method('execute')
            ->willThrowException(new \PDOException('Query failed'));

        $this->mockPdo->method('prepare')->willReturn($this->mockStmt);

        $result = $this->repository->areAllInvitationsConfirmed($teamId);

        $this->assertFalse($result);
    }


    public function testGetInvitationsByTeamReturnsInvitations(): void
    {
        $teamId = 1;
        $expectedInvitations = [
            ['invitation_id' => 1, 'email' => 'user1@example.com', 'validation_status' => 'confirmed'],
            ['invitation_id' => 2, 'email' => 'user2@example.com', 'validation_status' => 'pending']
        ];

        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetchAll')->willReturn($expectedInvitations);

        $this->mockPdo->method('prepare')->willReturn($this->mockStmt);

        $result = $this->repository->getInvitationsByTeam($teamId);

        $this->assertEquals($expectedInvitations, $result);
    }

    public function testGetInvitationsByTeamReturnsEmptyArrayWhenNoInvitations(): void
    {
        $teamId = 999;

        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetchAll')->willReturn([]);

        $this->mockPdo->method('prepare')->willReturn($this->mockStmt);

        $result = $this->repository->getInvitationsByTeam($teamId);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }


    public function testIsEmailInvitedReturnsTrueWhenInvited(): void
    {
        $teamId = 1;
        $email = 'test@example.com';

        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetchColumn')->willReturn(1);

        $this->mockPdo->method('prepare')->willReturn($this->mockStmt);

        $result = $this->repository->isEmailInvited($teamId, $email);

        $this->assertTrue($result);
    }

    public function testIsEmailInvitedReturnsFalseWhenNotInvited(): void
    {
        $teamId = 1;
        $email = 'notinvited@example.com';

        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetchColumn')->willReturn(0);

        $this->mockPdo->method('prepare')->willReturn($this->mockStmt);

        $result = $this->repository->isEmailInvited($teamId, $email);

        $this->assertFalse($result);
    }


    public function testCountPendingInvitationsReturnsCount(): void
    {
        $teamId = 1;
        $expectedCount = 3;

        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetchColumn')->willReturn($expectedCount);

        $this->mockPdo->method('prepare')->willReturn($this->mockStmt);

        $result = $this->repository->countPendingInvitations($teamId);

        $this->assertEquals($expectedCount, $result);
    }

    public function testCountPendingInvitationsReturnsZeroWhenNoPending(): void
    {
        $teamId = 1;

        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockStmt->method('fetchColumn')->willReturn(0);

        $this->mockPdo->method('prepare')->willReturn($this->mockStmt);

        $result = $this->repository->countPendingInvitations($teamId);

        $this->assertEquals(0, $result);
    }
}
