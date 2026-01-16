<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Pwd;

use PHPUnit\Framework\TestCase;
use App\Modules\Models\Pwd\PasswordReset;
use App\Core\Database;
use PDO;
use PDOStatement;
use PDOException;
use ReflectionClass;

/**
 * Unit tests for PasswordReset model
 */
class PasswordResetTest extends TestCase
{
    private PasswordReset $passwordReset;
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

        $this->passwordReset = new PasswordReset();
    }

    protected function tearDown(): void
    {
        $reflection = new ReflectionClass(Database::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null, null);
    }

    public function testGetUserByEmailReturnsUserData(): void
    {
        $mockUser = [
            'user_id' => 123,
            'last_name' => 'Doe',
            'first_name' => 'John',
            'email' => 'john.doe@example.com'
        ];

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['john.doe@example.com'])
            ->willReturn(true);

        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn($mockUser);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with('SELECT user_id, last_name, first_name, email FROM USERS WHERE email = ?')
            ->willReturn($this->mockStmt);

        $result = $this->passwordReset->getUserByEmail('john.doe@example.com');

        $this->assertEquals($mockUser, $result);
    }

    public function testGetUserByEmailReturnsFalseForNonExistentUser(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn(false);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->passwordReset->getUserByEmail('nonexistent@example.com');

        $this->assertFalse($result);
    }

    public function testGetUserByEmailReturnsFalseOnException(): void
    {
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->will($this->throwException(new PDOException('Database error')));

        $result = $this->passwordReset->getUserByEmail('test@example.com');

        $this->assertFalse($result);
    }

    public function testCreateTokenReturnsString(): void
    {
        $userId = 123;

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) use ($userId) {
                return count($params) === 3 &&
                       $params[0] === $userId &&
                       is_string($params[1]) &&
                       strlen($params[1]) === 64 &&
                       is_string($params[2]);
            }))
            ->willReturn(true);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with('INSERT INTO PASSWORD_RESET_TOKEN (user_id, token, expires_at) VALUES (?, ?, ?)')
            ->willReturn($this->mockStmt);

        $token = $this->passwordReset->createToken($userId);

        $this->assertIsString($token);
        $this->assertEquals(64, strlen($token));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token);
    }

    public function testCreateTokenGeneratesUniqueTokens(): void
    {
        $this->mockStmt->method('execute')->willReturn(true);
        $this->mockPdo->method('prepare')->willReturn($this->mockStmt);

        $token1 = $this->passwordReset->createToken(123);
        $token2 = $this->passwordReset->createToken(123);

        $this->assertNotEquals($token1, $token2);
    }

    public function testCreateTokenReturnsFalseOnException(): void
    {
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->will($this->throwException(new PDOException('Insert failed')));

        $result = $this->passwordReset->createToken(123);

        $this->assertFalse($result);
    }

    public function testVerifyTokenReturnsValidForCorrectToken(): void
    {
        $futureTime = date('Y-m-d H:i:s', strtotime('+2 hours'));
        $mockTokenData = [
            'id' => 1,
            'user_id' => 123,
            'expires_at' => $futureTime,
            'is_used' => 0
        ];

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['validtoken123'])
            ->willReturn(true);

        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn($mockTokenData);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with('SELECT id, user_id, expires_at, is_used FROM PASSWORD_RESET_TOKEN WHERE token = ?')
            ->willReturn($this->mockStmt);

        $result = $this->passwordReset->verifyToken('validtoken123');

        $this->assertTrue($result['valid']);
        $this->assertEquals(123, $result['user_id']);
        $this->assertEquals(1, $result['token_id']);
    }

    public function testVerifyTokenReturnsInvalidForNonExistentToken(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn(false);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->passwordReset->verifyToken('invalidtoken');

        $this->assertFalse($result['valid']);
        $this->assertEquals('Invalid code', $result['message']);
    }

    public function testVerifyTokenReturnsInvalidForUsedToken(): void
    {
        $mockTokenData = [
            'id' => 1,
            'user_id' => 123,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+2 hours')),
            'is_used' => 1
        ];

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn($mockTokenData);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->passwordReset->verifyToken('usedtoken');

        $this->assertFalse($result['valid']);
        $this->assertEquals('This code has already been used', $result['message']);
    }

    public function testVerifyTokenReturnsInvalidForExpiredToken(): void
    {
        $pastTime = date('Y-m-d H:i:s', strtotime('-1 hour'));
        $mockTokenData = [
            'id' => 1,
            'user_id' => 123,
            'expires_at' => $pastTime,
            'is_used' => 0
        ];

        $mockDeleteStmt = $this->createMock(PDOStatement::class);
        $mockDeleteStmt->expects($this->once())
            ->method('execute')
            ->with([1]);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn($mockTokenData);

        $this->mockPdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($this->mockStmt, $mockDeleteStmt);

        $result = $this->passwordReset->verifyToken('expiredtoken');

        $this->assertFalse($result['valid']);
        $this->assertEquals('This code has expired', $result['message']);
    }
}
