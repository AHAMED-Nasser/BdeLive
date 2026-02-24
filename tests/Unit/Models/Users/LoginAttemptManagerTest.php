<?php

declare(strict_types=1);

namespace App\Tests\Unit\Models\Users;

use PHPUnit\Framework\TestCase;
use App\Modules\Models\Users\LoginAttemptManager;
use App\Core\Database;
use PDO;
use PDOStatement;
use PDOException;
use ReflectionClass;

/**
 * Unit tests for LoginAttemptManager (IP + email tracking for anti-brute-force).
 */
class LoginAttemptManagerTest extends TestCase
{
    private LoginAttemptManager $manager;
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

        $this->manager = new LoginAttemptManager();
    }

    protected function tearDown(): void
    {
        $reflection = new ReflectionClass(Database::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null, null);
    }

    public function testCountRecentAttemptsReturnsZeroWhenNoAttempts(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $this->mockStmt->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('0');

        $this->mockStmt->expects($this->exactly(3))
            ->method('bindValue');

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('LOGIN_ATTEMPTS'))
            ->willReturn($this->mockStmt);

        $count = $this->manager->countRecentAttempts('192.168.1.1', 'user@example.com');

        $this->assertSame(0, $count);
    }

    public function testCountRecentAttemptsReturnsCorrectCount(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $this->mockStmt->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('5');

        $this->mockStmt->expects($this->exactly(3))
            ->method('bindValue');

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $count = $this->manager->countRecentAttempts('10.0.0.1', 'test@test.com', 15);

        $this->assertSame(5, $count);
    }

    public function testCountRecentAttemptsUsesCustomMinutesWindow(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $this->mockStmt->expects($this->exactly(3))
            ->method('bindValue');
        $this->mockStmt->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('2');

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $count = $this->manager->countRecentAttempts('127.0.0.1', 'a@b.c', 30);

        $this->assertSame(2, $count);
    }

    public function testRecordFailedAttemptCallsInsertWithIpAndEmail(): void
    {
        $this->mockStmt->expects($this->exactly(2))
            ->method('bindValue');
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('INSERT INTO LOGIN_ATTEMPTS'))
            ->willReturn($this->mockStmt);

        $this->manager->recordFailedAttempt('192.168.1.10', 'fail@example.com');
    }

    public function testClearAttemptsCallsDeleteWithIpAndEmail(): void
    {
        $this->mockStmt->expects($this->exactly(2))
            ->method('bindValue');
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('DELETE FROM LOGIN_ATTEMPTS'))
            ->willReturn($this->mockStmt);

        $this->manager->clearAttempts('10.0.0.5', 'success@example.com');
    }

    public function testCountRecentAttemptsThrowsPdoExceptionOnFailure(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willThrowException(new PDOException('DB error'));

        $this->mockStmt->expects($this->exactly(3))
            ->method('bindValue');

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $this->expectException(PDOException::class);
        $this->expectExceptionMessage('DB error');

        $this->manager->countRecentAttempts('1.2.3.4', 'x@y.z');
    }

    public function testRecordFailedAttemptThrowsPdoExceptionOnFailure(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willThrowException(new PDOException('Insert failed'));

        $this->mockStmt->expects($this->exactly(2))
            ->method('bindValue');

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $this->expectException(PDOException::class);
        $this->expectExceptionMessage('Insert failed');

        $this->manager->recordFailedAttempt('1.1.1.1', 'a@b.com');
    }

    public function testClearAttemptsThrowsPdoExceptionOnFailure(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willThrowException(new PDOException('Delete failed'));

        $this->mockStmt->expects($this->exactly(2))
            ->method('bindValue');

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $this->expectException(PDOException::class);
        $this->expectExceptionMessage('Delete failed');

        $this->manager->clearAttempts('2.2.2.2', 'c@d.com');
    }
}
