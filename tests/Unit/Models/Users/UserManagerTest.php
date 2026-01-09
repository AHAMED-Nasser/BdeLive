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

    public function testGenerateVerificationTokenReturnsValidToken(): void
    {
        $token = $this->userManager->generateVerificationToken();

        $this->assertIsString($token);
        $this->assertEquals(64, strlen($token));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token);
    }

    public function testGenerateVerificationTokenGeneratesUniqueTokens(): void
    {
        $token1 = $this->userManager->generateVerificationToken();
        $token2 = $this->userManager->generateVerificationToken();

        $this->assertNotEquals($token1, $token2);
    }

    public function testCreateUserWithVerificationCreatesUserWithToken(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) {
                return isset($params['last_name']) &&
                       isset($params['first_name']) &&
                       isset($params['user_status']) &&
                       isset($params['email']) &&
                       isset($params['password']) &&
                       isset($params['verification_token']) &&
                       isset($params['token_expires_at']) &&
                       strlen($params['verification_token']) === 64;
            }))
            ->willReturn(true);
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);
        
        $this->mockPdo->expects($this->once())
            ->method('lastInsertId')
            ->willReturn('42');

        $result = $this->userManager->createUserWithVerification(
            'Doe',
            'John',
            'BUT 1',
            'john.doe@example.com',
            'password123'
        );
        
        $this->assertIsArray($result);
        $this->assertEquals(42, $result['user_id']);
        $this->assertIsString($result['token']);
        $this->assertEquals(64, strlen($result['token']));
    }

    public function testCreateUserWithVerificationReturnsFalseOnFailure(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(false);
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->userManager->createUserWithVerification(
            'Doe',
            'John',
            'BUT 1',
            'john.doe@example.com',
            'password123'
        );
        
        $this->assertFalse($result);
    }

    public function testVerifyEmailTokenReturnsSuccessForValidToken(): void
    {
        $futureDate = date('Y-m-d H:i:s', strtotime('+24 hours'));
        $mockUser = [
            'user_id' => 123,
            'is_verified' => 0,
            'token_expires_at' => $futureDate,
        ];

        $mockUpdateStmt = $this->createMock(PDOStatement::class);
        $mockUpdateStmt->expects($this->once())
            ->method('execute')
            ->with(['user_id' => 123])
            ->willReturn(true);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['token' => 'validtoken123'])
            ->willReturn(true);
        
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn($mockUser);
        
        $this->mockPdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($this->mockStmt, $mockUpdateStmt);

        $result = $this->userManager->verifyEmailToken('validtoken123');
        
        $this->assertTrue($result['success']);
        $this->assertEquals(123, $result['user_id']);
    }

    public function testVerifyEmailTokenReturnsFailureForInvalidToken(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['token' => 'invalidtoken'])
            ->willReturn(true);
        
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn(false);
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->userManager->verifyEmailToken('invalidtoken');
        
        $this->assertFalse($result['success']);
        $this->assertEquals('Token de vérification invalide', $result['message']);
    }

    public function testVerifyEmailTokenReturnsFailureForAlreadyVerifiedEmail(): void
    {
        $mockUser = [
            'user_id' => 123,
            'is_verified' => 1,
            'token_expires_at' => null,
        ];

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['token' => 'alreadyverifiedtoken'])
            ->willReturn(true);
        
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn($mockUser);
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->userManager->verifyEmailToken('alreadyverifiedtoken');
        
        $this->assertFalse($result['success']);
        $this->assertEquals('Cet email a déjà été vérifié', $result['message']);
    }

    public function testVerifyEmailTokenReturnsExpiredForExpiredToken(): void
    {
        $pastDate = date('Y-m-d H:i:s', strtotime('-1 hour'));
        $mockUser = [
            'user_id' => 123,
            'is_verified' => 0,
            'token_expires_at' => $pastDate,
        ];

        $mockDeleteStmt = $this->createMock(PDOStatement::class);
        $mockDeleteStmt->expects($this->once())
            ->method('execute')
            ->with(['user_id' => 123])
            ->willReturn(true);

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['token' => 'expiredtoken'])
            ->willReturn(true);
        
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn($mockUser);
        
        $this->mockPdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($this->mockStmt, $mockDeleteStmt);

        $result = $this->userManager->verifyEmailToken('expiredtoken');
        
        $this->assertFalse($result['success']);
        $this->assertEquals('expired', $result['message']);
    }

    public function testIsEmailVerifiedReturnsTrueForVerifiedUser(): void
    {
        $mockUser = [
            'is_verified' => 1
        ];

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['user_id' => 123])
            ->willReturn(true);
        
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn($mockUser);
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->userManager->isEmailVerified(123);
        
        $this->assertTrue($result);
    }

    public function testIsEmailVerifiedReturnsFalseForUnverifiedUser(): void
    {
        $mockUser = [
            'is_verified' => 0
        ];

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['user_id' => 123])
            ->willReturn(true);
        
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn($mockUser);
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->userManager->isEmailVerified(123);
        
        $this->assertFalse($result);
    }

    public function testIsEmailVerifiedReturnsFalseForNonExistentUser(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['user_id' => 999])
            ->willReturn(true);
        
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn(false);
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->userManager->isEmailVerified(999);
        
        $this->assertFalse($result);
    }

    public function testResendVerificationTokenGeneratesNewToken(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) {
                return isset($params['token']) &&
                       isset($params['user_id']) &&
                       isset($params['token_expires_at']) &&
                       strlen($params['token']) === 64 &&
                       $params['user_id'] === 123;
            }))
            ->willReturn(true);
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->userManager->resendVerificationToken(123);
        
        $this->assertIsString($result);
        $this->assertEquals(64, strlen($result));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $result);
    }

    public function testResendVerificationTokenReturnsFalseOnFailure(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(false);
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->userManager->resendVerificationToken(123);
        
        $this->assertFalse($result);
    }

    public function testFindUserByEmailIncludesIsVerified(): void
    {
        $expectedUser = [
            'user_id' => 1,
            'last_name' => 'Doe',
            'first_name' => 'John',
            'user_status' => 'BUT 1',
            'email' => 'john.doe@example.com',
            'password' => '$2y$10$hashedpassword',
            'is_verified' => 1
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
        $this->assertArrayHasKey('is_verified', $result);
    }

    // ==================== Tests pour getUsers() ====================

    public function testGetUsersReturnsActiveUsersWithoutFilters(): void
    {
        $expectedUsers = [
            ['user_id' => 1, 'last_name' => 'AHAMED', 'first_name' => 'Nasser', 'email' => 'nasser@test.com', 'role' => 'admin', 'is_blocked' => 0],
            ['user_id' => 2, 'last_name' => 'HELALI', 'first_name' => 'Amin', 'email' => 'amin@test.com', 'role' => 'user', 'is_blocked' => 0],
        ];

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        
        $this->mockStmt->expects($this->once())
            ->method('fetchAll')
            ->willReturn($expectedUsers);
        
        $this->mockStmt->expects($this->exactly(3))
            ->method('bindValue');
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->userManager->getUsers(10, 0, false, 'all', '');
        
        $this->assertCount(2, $result);
        $this->assertEquals($expectedUsers, $result);
    }

    public function testGetUsersFiltersAdminsOnly(): void
    {
        $expectedUsers = [
            ['user_id' => 1, 'last_name' => 'AHAMED', 'first_name' => 'Nasser', 'email' => 'nasser@test.com', 'role' => 'admin', 'is_blocked' => 0],
        ];

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        
        $this->mockStmt->expects($this->once())
            ->method('fetchAll')
            ->willReturn($expectedUsers);
        
        $this->mockStmt->expects($this->exactly(4))
            ->method('bindValue');
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('AND role = :role'))
            ->willReturn($this->mockStmt);

        $result = $this->userManager->getUsers(10, 0, false, 'admin', '');
        
        $this->assertCount(1, $result);
        $this->assertEquals('admin', $result[0]['role']);
    }

    public function testGetUsersFiltersUsersOnly(): void
    {
        $expectedUsers = [
            ['user_id' => 2, 'last_name' => 'HELALI', 'first_name' => 'Amin', 'email' => 'amin@test.com', 'role' => 'user', 'is_blocked' => 0],
        ];

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        
        $this->mockStmt->expects($this->once())
            ->method('fetchAll')
            ->willReturn($expectedUsers);
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('AND role = :role'))
            ->willReturn($this->mockStmt);

        $result = $this->userManager->getUsers(10, 0, false, 'user', '');
        
        $this->assertCount(1, $result);
        $this->assertEquals('user', $result[0]['role']);
    }

    public function testGetUsersSearchesByLastName(): void
    {
        $expectedUsers = [
            ['user_id' => 1, 'last_name' => 'AHAMED', 'first_name' => 'Nasser', 'email' => 'nasser@test.com', 'role' => 'admin', 'is_blocked' => 0],
        ];

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        
        $this->mockStmt->expects($this->once())
            ->method('fetchAll')
            ->willReturn($expectedUsers);
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('AND (last_name LIKE :search1 OR first_name LIKE :search2 OR email LIKE :search3)'))
            ->willReturn($this->mockStmt);

        $result = $this->userManager->getUsers(10, 0, false, 'all', 'AHAMED');
        
        $this->assertCount(1, $result);
        $this->assertStringContainsString('AHAMED', $result[0]['last_name']);
    }

    public function testGetUsersSearchesByEmail(): void
    {
        $expectedUsers = [
            ['user_id' => 3, 'last_name' => 'Test', 'first_name' => 'User', 'email' => 'bonjour@test.com', 'role' => 'user', 'is_blocked' => 0],
        ];

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        
        $this->mockStmt->expects($this->once())
            ->method('fetchAll')
            ->willReturn($expectedUsers);
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->userManager->getUsers(10, 0, false, 'all', 'bonjour');
        
        $this->assertCount(1, $result);
        $this->assertStringContainsString('bonjour', $result[0]['email']);
    }

    public function testGetUsersCombinesFiltersCorrectly(): void
    {
        $expectedUsers = [
            ['user_id' => 1, 'last_name' => 'AHAMED', 'first_name' => 'Nasser', 'email' => 'nasser@test.com', 'role' => 'admin', 'is_blocked' => 0],
        ];

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        
        $this->mockStmt->expects($this->once())
            ->method('fetchAll')
            ->willReturn($expectedUsers);
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->logicalAnd(
                $this->stringContains('AND role = :role'),
                $this->stringContains('AND (last_name LIKE :search1')
            ))
            ->willReturn($this->mockStmt);

        $result = $this->userManager->getUsers(10, 0, false, 'admin', 'AHAMED');
        
        $this->assertCount(1, $result);
        $this->assertEquals('admin', $result[0]['role']);
        $this->assertStringContainsString('AHAMED', $result[0]['last_name']);
    }

    public function testGetUsersHandlesSpecialCharactersInSearch(): void
    {
        $expectedUsers = [
            ['user_id' => 4, 'last_name' => "O'Brien", 'first_name' => 'John', 'email' => 'john@test.com', 'role' => 'user', 'is_blocked' => 0],
        ];

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        
        $this->mockStmt->expects($this->once())
            ->method('fetchAll')
            ->willReturn($expectedUsers);
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        // Test avec apostrophe (devrait être échappé par PDO)
        $result = $this->userManager->getUsers(10, 0, false, 'all', "O'Brien");
        
        $this->assertCount(1, $result);
    }

    public function testGetUsersRespectsBlockedFilter(): void
    {
        $expectedUsers = [
            ['user_id' => 5, 'last_name' => 'Blocked', 'first_name' => 'User', 'email' => 'blocked@test.com', 'role' => 'user', 'is_blocked' => 1],
        ];

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        
        $this->mockStmt->expects($this->once())
            ->method('fetchAll')
            ->willReturn($expectedUsers);
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->userManager->getUsers(10, 0, true, 'all', '');
        
        $this->assertCount(1, $result);
        $this->assertEquals(1, $result[0]['is_blocked']);
    }

    // ==================== Tests pour countUsers() ====================

    public function testCountUsersReturnsCorrectCount(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        
        $this->mockStmt->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(5);
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->userManager->countUsers(false, 'all', '');
        
        $this->assertEquals(5, $result);
    }

    public function testCountUsersWithRoleFilter(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        
        $this->mockStmt->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(2);
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('AND role = :role'))
            ->willReturn($this->mockStmt);

        $result = $this->userManager->countUsers(false, 'admin', '');
        
        $this->assertEquals(2, $result);
    }

    public function testCountUsersWithSearch(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        
        $this->mockStmt->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(1);
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('AND (last_name LIKE :search1'))
            ->willReturn($this->mockStmt);

        $result = $this->userManager->countUsers(false, 'all', 'AHAMED');
        
        $this->assertEquals(1, $result);
    }

    public function testCountUsersWithCombinedFilters(): void
    {
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        
        $this->mockStmt->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(1);
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->logicalAnd(
                $this->stringContains('AND role = :role'),
                $this->stringContains('AND (last_name LIKE :search1')
            ))
            ->willReturn($this->mockStmt);

        $result = $this->userManager->countUsers(false, 'admin', 'AHAMED');
        
        $this->assertEquals(1, $result);
    }

    // ==================== Tests de sécurité SQL ====================

    public function testGetUsersProtectsAgainstSQLInjection(): void
    {
        // Tentative d'injection SQL
        $maliciousInput = "'; DROP TABLE USERS; --";
        
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        
        $this->mockStmt->expects($this->once())
            ->method('fetchAll')
            ->willReturn([]);
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        // Ne devrait pas lever d'exception car les paramètres sont bindés
        $result = $this->userManager->getUsers(10, 0, false, 'all', $maliciousInput);
        
        $this->assertIsArray($result);
    }

    public function testCountUsersProtectsAgainstSQLInjection(): void
    {
        // Tentative d'injection SQL
        $maliciousInput = "' OR '1'='1";
        
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        
        $this->mockStmt->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(0);
        
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        // Ne devrait pas lever d'exception car les paramètres sont bindés
        $result = $this->userManager->countUsers(false, 'all', $maliciousInput);
        
        $this->assertIsInt($result);
    }
}
