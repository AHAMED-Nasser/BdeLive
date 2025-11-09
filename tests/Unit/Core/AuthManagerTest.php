<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use App\Core\Auth\AuthManager;
use App\Core\Session\SessionManager;
use App\Core\Exception\AuthenticationException;
use App\Core\Exception\AuthorizationException;

/**
 * Unit tests for AuthManager
 */
class AuthManagerTest extends TestCase
{
    private AuthManager $auth;
    private SessionManager $session;

    protected function setUp(): void
    {
        $this->session = new SessionManager();
        $this->auth = new AuthManager($this->session);
    }

    protected function tearDown(): void
    {
        // Clean up session after each test
        if ($this->session->isStarted()) {
            $this->session->clear();
        }
    }

    public function testIsAuthenticatedReturnsFalseByDefault(): void
    {
        $this->assertFalse($this->auth->isAuthenticated());
    }

    public function testLoginStoresUserData(): void
    {
        $this->auth->login(123, 'BUT 2', 'test@example.com', 'John', 'Doe');

        $this->assertTrue($this->auth->isAuthenticated());
        $this->assertEquals(123, $this->auth->getUserId());
        $this->assertEquals('BUT 2', $this->auth->getUserStatus());
        $this->assertEquals('test@example.com', $this->auth->getUserEmail());
        $this->assertEquals('John', $this->auth->getUserFirstName());
        $this->assertEquals('Doe', $this->auth->getUserLastName());
    }

    public function testLoginWithoutNames(): void
    {
        $this->auth->login(456, 'Personnel Enseignant', 'teacher@example.com');

        $this->assertTrue($this->auth->isAuthenticated());
        $this->assertEquals(456, $this->auth->getUserId());
        $this->assertNull($this->auth->getUserFirstName());
        $this->assertNull($this->auth->getUserLastName());
    }

    public function testLogoutRemovesUserData(): void
    {
        $this->auth->login(123, 'BUT 1', 'test@example.com', 'Jane', 'Smith');
        $this->assertTrue($this->auth->isAuthenticated());

        $this->auth->logout();

        $this->assertFalse($this->auth->isAuthenticated());
        $this->assertNull($this->auth->getUserId());
        $this->assertNull($this->auth->getUserStatus());
        $this->assertNull($this->auth->getUserEmail());
    }

    public function testIsAdminReturnsTrueForBDE(): void
    {
        $this->auth->login(1, 'BDE', 'admin@example.com');
        $this->assertTrue($this->auth->isAdmin());
    }

    public function testIsAdminReturnsFalseForNonBDE(): void
    {
        $this->auth->login(2, 'BUT 1', 'student@example.com');
        $this->assertFalse($this->auth->isAdmin());
    }

    public function testIsAdminReturnsFalseWhenNotAuthenticated(): void
    {
        $this->assertFalse($this->auth->isAdmin());
    }

    public function testGetUserReturnsArrayWhenAuthenticated(): void
    {
        $this->auth->login(123, 'BUT 3', 'user@example.com', 'Bob', 'Martin');

        $user = $this->auth->getUser();

        $this->assertIsArray($user);
        $this->assertEquals(123, $user['user_id']);
        $this->assertEquals('user@example.com', $user['email']);
        $this->assertEquals('BUT 3', $user['user_status']);
        $this->assertEquals('Bob', $user['first_name']);
        $this->assertEquals('Martin', $user['last_name']);
        $this->assertFalse($user['is_admin']);
    }

    public function testGetUserReturnsNullWhenNotAuthenticated(): void
    {
        $user = $this->auth->getUser();
        $this->assertNull($user);
    }

    public function testGetUserIncludesIsAdminFlag(): void
    {
        $this->auth->login(1, 'BDE', 'bde@example.com', 'Admin', 'User');

        $user = $this->auth->getUser();

        $this->assertTrue($user['is_admin']);
    }

    public function testRequireAuthenticationThrowsWhenNotAuthenticated(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('You must be logged in to access this page');

        $this->auth->requireAuthentication();
    }

    public function testRequireAuthenticationDoesNotThrowWhenAuthenticated(): void
    {
        $this->auth->login(123, 'BUT 1', 'test@example.com');

        // Should not throw
        $this->auth->requireAuthentication();

        $this->assertTrue(true); // Assert we reach here
    }

    public function testRequireAdminThrowsWhenNotAuthenticated(): void
    {
        $this->expectException(AuthenticationException::class);

        $this->auth->requireAdmin();
    }

    public function testRequireAdminThrowsWhenNotAdmin(): void
    {
        $this->auth->login(123, 'BUT 2', 'student@example.com');

        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage('You do not have the necessary permissions to access this page');

        $this->auth->requireAdmin();
    }

    public function testRequireAdminDoesNotThrowForBDE(): void
    {
        $this->auth->login(1, 'BDE', 'admin@example.com');

        // Should not throw
        $this->auth->requireAdmin();

        $this->assertTrue(true); // Assert we reach here
    }

    public function testGetUserIdReturnsNullWhenNotAuthenticated(): void
    {
        $this->assertNull($this->auth->getUserId());
    }

    public function testGetUserStatusReturnsNullWhenNotAuthenticated(): void
    {
        $this->assertNull($this->auth->getUserStatus());
    }

    public function testGetUserEmailReturnsNullWhenNotAuthenticated(): void
    {
        $this->assertNull($this->auth->getUserEmail());
    }
}
