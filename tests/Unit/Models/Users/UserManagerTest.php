<?php

declare(strict_types=1);

namespace App\Tests\Unit\Models\Users;

use PHPUnit\Framework\TestCase;
use App\Modules\Models\Users\UserManager;
use App\Core\Database;
use PDO;
use PDOStatement;
use PDOException;
use ReflectionClass;

class UserManagerTest extends TestCase
{
    private UserManager $userManager;
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
        
        $this->userManager = new UserManager();
    }

    protected function tearDown(): void
    {
        $reflection = new ReflectionClass(Database::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null, null);
    }

    public function testHashPasswordReturnsValidHash(): void
    {
        $password = 'testPassword123';
        $hash = $this->userManager->hashPassword($password);

        $this->assertNotEmpty($hash);
        $this->assertNotEquals($password, $hash);
        $this->assertTrue(strlen($hash) >= 60);
    }

    public function testHashPasswordCreatesDifferentHashes(): void
    {
        $password = 'testPassword123';
        $hash1 = $this->userManager->hashPassword($password);
        $hash2 = $this->userManager->hashPassword($password);

        $this->assertNotEquals($hash1, $hash2);
    }

    public function testVerifyPasswordValidatesCorrectPassword(): void
    {
        $password = 'testPassword123';
        $hash = $this->userManager->hashPassword($password);

        $this->assertTrue($this->userManager->verifyPassword($password, $hash));
    }

    public function testVerifyPasswordRejectsIncorrectPassword(): void
    {
        $password = 'testPassword123';
        $wrongPassword = 'wrongPassword456';
        $hash = $this->userManager->hashPassword($password);

        $this->assertFalse($this->userManager->verifyPassword($wrongPassword, $hash));
    }

    public function testEmailExistsReturnsTrueForExistingEmail(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['email' => 'existing@example.com'])
            ->willReturn(true);
        
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn(['count' => 1]);
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->userManager->emailExists('existing@example.com');
        $this->assertTrue($result);
    }

    public function testEmailExistsReturnsFalseForNonExistingEmail(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['email' => 'nonexistent@example.com'])
            ->willReturn(true);
        
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn(['count' => 0]);
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->userManager->emailExists('nonexistent@example.com');
        $this->assertFalse($result);
    }

    public function testFindUserByEmailReturnsUserDataForExistingEmail(): void
    {
        $expectedUser = [
            'user_id' => 1,
            'last_name' => 'Doe',
            'first_name' => 'John',
            'user_status' => 'BUT 1',
            'email' => 'john.doe@example.com',
            'password' => '$2y$10$hashedpassword'
        ];

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['email' => 'john.doe@example.com'])
            ->willReturn(true);
        
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn($expectedUser);
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->userManager->findUserByEmail('john.doe@example.com');
        $this->assertEquals($expectedUser, $result);
    }

    public function testFindUserByEmailReturnsFalseForNonExistingEmail(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['email' => 'nonexistent@example.com'])
            ->willReturn(true);
        
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn(false);
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->userManager->findUserByEmail('nonexistent@example.com');
        $this->assertFalse($result);
    }

    public function testCreateUserCreatesNewUser(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);
        
        $this->mockPdo->expects($this->once())
            ->method('lastInsertId')
            ->willReturn('42');

        $result = $this->userManager->createUser(
            'Doe',
            'John',
            'BUT 1',
            'john.doe@example.com',
            'password123'
        );
        
        $this->assertEquals(42, $result);
    }

    public function testUpdateUserUpdatesUserInformation(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->userManager->updateUser(
            1,
            'Smith',
            'Jane',
            'BUT 2',
            'jane.smith@example.com'
        );
        
        $this->assertTrue($result);
    }

    public function testUpdatePasswordUpdatesUserPassword(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->userManager->updatePassword(1, 'newPassword123');
        $this->assertTrue($result);
    }

    public function testDeleteUserRemovesUser(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['user_id' => 1])
            ->willReturn(true);
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->userManager->deleteUser(1);
        $this->assertTrue($result);
    }

    public function testUpdateFirstNameUpdatesUserFirstName(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['newFirstName' => 'Jane', 'user_id' => 1])
            ->willReturn(true);
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $this->userManager->updateFirstName(1, 'Jane');
        $this->assertTrue(true);
    }

    public function testUpdateLastNameUpdatesUserLastName(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['newLastName' => 'Smith', 'user_id' => 1])
            ->willReturn(true);
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $this->userManager->updateLastName(1, 'Smith');
        $this->assertTrue(true);
    }
}
