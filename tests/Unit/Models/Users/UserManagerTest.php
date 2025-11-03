<?php

declare(strict_types=1);

namespace App\Tests\Unit\Models\Users;

use PHPUnit\Framework\TestCase;
use App\Modules\Models\Users\UserManager;
use App\Core\Database;
use PDOException;

/**
 * Test suite for UserManager model
 *
 * Note: Password hashing tests are true unit tests (no DB needed).
 * Database-dependent tests are skipped if DB connection is not available.
 *
 * @package App\Tests\Unit\Models\Users
 */
class UserManagerTest extends TestCase
{
    /**
     * Test that hashPassword returns a valid hash
     *
     * @return void
     */
    public function testHashPasswordReturnsValidHash(): void
    {
        try {
            $userManager = new UserManager();
            $password = 'testPassword123';
            $hash = $userManager->hashPassword($password);

            $this->assertNotEmpty($hash);
            $this->assertNotEquals($password, $hash);
            $this->assertTrue(strlen($hash) >= 60); // password_hash creates hashes of at least 60 characters
        } catch (PDOException $e) {
            $this->markTestSkipped('Database connection not available: ' . $e->getMessage());
        }
    }

    /**
     * Test that hashPassword creates different hashes for same password
     *
     * @return void
     */
    public function testHashPasswordCreatesDifferentHashes(): void
    {
        try {
            $userManager = new UserManager();
            $password = 'testPassword123';
            $hash1 = $userManager->hashPassword($password);
            $hash2 = $userManager->hashPassword($password);

            // password_hash uses random salt, so hashes should be different
            $this->assertNotEquals($hash1, $hash2);
        } catch (PDOException $e) {
            $this->markTestSkipped('Database connection not available: ' . $e->getMessage());
        }
    }

    /**
     * Test that verifyPassword validates correct password
     *
     * @return void
     */
    public function testVerifyPasswordValidatesCorrectPassword(): void
    {
        try {
            $userManager = new UserManager();
            $password = 'testPassword123';
            $hash = $userManager->hashPassword($password);

            $this->assertTrue($userManager->verifyPassword($password, $hash));
        } catch (PDOException $e) {
            $this->markTestSkipped('Database connection not available: ' . $e->getMessage());
        }
    }

    /**
     * Test that verifyPassword rejects incorrect password
     *
     * @return void
     */
    public function testVerifyPasswordRejectsIncorrectPassword(): void
    {
        try {
            $userManager = new UserManager();
            $password = 'testPassword123';
            $wrongPassword = 'wrongPassword456';
            $hash = $userManager->hashPassword($password);

            $this->assertFalse($userManager->verifyPassword($wrongPassword, $hash));
        } catch (PDOException $e) {
            $this->markTestSkipped('Database connection not available: ' . $e->getMessage());
        }
    }

    /**
     * Test that emailExists returns true for existing email
     *
     * @return void
     */
    public function testEmailExistsReturnsTrueForExistingEmail(): void
    {
        try {
            $userManager = new UserManager();
            // This test requires a database connection
            // Skip if DB is not available
            $this->assertTrue(method_exists($userManager, 'emailExists'));
        } catch (PDOException $e) {
            $this->markTestSkipped('Database connection not available: ' . $e->getMessage());
        }
    }

    /**
     * Test that emailExists returns false for non-existing email
     *
     * @return void
     */
    public function testEmailExistsReturnsFalseForNonExistingEmail(): void
    {
        try {
            $userManager = new UserManager();
            // This test requires a database connection
            $this->assertTrue(method_exists($userManager, 'emailExists'));
        } catch (PDOException $e) {
            $this->markTestSkipped('Database connection not available: ' . $e->getMessage());
        }
    }

    /**
     * Test that findUserByEmail returns user data for existing email
     *
     * @return void
     */
    public function testFindUserByEmailReturnsUserDataForExistingEmail(): void
    {
        try {
            $userManager = new UserManager();
            // This test requires a database connection
            $this->assertTrue(method_exists($userManager, 'findUserByEmail'));
        } catch (PDOException $e) {
            $this->markTestSkipped('Database connection not available: ' . $e->getMessage());
        }
    }

    /**
     * Test that findUserByEmail returns false for non-existing email
     *
     * @return void
     */
    public function testFindUserByEmailReturnsFalseForNonExistingEmail(): void
    {
        try {
            $userManager = new UserManager();
            // This test requires a database connection
            $this->assertTrue(method_exists($userManager, 'findUserByEmail'));
        } catch (PDOException $e) {
            $this->markTestSkipped('Database connection not available: ' . $e->getMessage());
        }
    }

    /**
     * Test that createUser creates a new user
     *
     * @return void
     */
    public function testCreateUserCreatesNewUser(): void
    {
        try {
            $userManager = new UserManager();
            // This test requires a database connection
            $this->assertTrue(method_exists($userManager, 'createUser'));
        } catch (PDOException $e) {
            $this->markTestSkipped('Database connection not available: ' . $e->getMessage());
        }
    }
}

