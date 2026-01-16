<?php

declare(strict_types=1);

namespace App\Tests\Unit\Functions;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../app/include/csrf.php';

/**
 * Test suite for CSRF functions
 *
 * @package App\Tests\Unit\Functions
 */
class CsrfFunctionsTest extends TestCase
{
    /**
     * Set up test environment
     *
     * @return void
     */
    protected function setUp(): void
    {
        // Start session for CSRF tests
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // Clear session data
        $_SESSION = [];
    }

    /**
     * Clean up after tests
     *
     * @return void
     */
    protected function tearDown(): void
    {
        // Clear session data
        $_SESSION = [];
    }

    /**
     * Test that generateCsrfToken generates a valid token
     *
     * @return void
     */
    public function testGenerateCsrfTokenGeneratesValidToken(): void
    {
        $token = generateCsrfToken();

        $this->assertNotEmpty($token);
        $this->assertEquals(64, strlen($token)); // 32 bytes = 64 hex characters
        $this->assertArrayHasKey('csrf_token', $_SESSION);
        $this->assertEquals($token, $_SESSION['csrf_token']);
    }

    /**
     * Test that generateCsrfToken stores token time
     *
     * @return void
     */
    public function testGenerateCsrfTokenStoresTokenTime(): void
    {
        $token = generateCsrfToken();

        $this->assertArrayHasKey('csrf_token_time', $_SESSION);
        $this->assertIsInt($_SESSION['csrf_token_time']);
    }

    /**
     * Test that validateCsrfToken validates correct token
     *
     * @return void
     */
    public function testValidateCsrfTokenValidatesCorrectToken(): void
    {
        $token = generateCsrfToken();
        $isValid = validateCsrfToken($token);

        $this->assertTrue($isValid);
    }

    /**
     * Test that validateCsrfToken rejects incorrect token
     *
     * @return void
     */
    public function testValidateCsrfTokenRejectsIncorrectToken(): void
    {
        generateCsrfToken();
        $isValid = validateCsrfToken('invalid_token');

        $this->assertFalse($isValid);
    }

    public function testValidateCsrfTokenRejectsEmptyToken(): void
    {
        generateCsrfToken();
        $isValid = validateCsrfToken('');

        $this->assertFalse($isValid);
    }

    public function testValidateCsrfTokenRejectsExpiredToken(): void
    {
        $token = generateCsrfToken();
        $_SESSION['csrf_token_time'] = time() - 3601;

        $isValid = validateCsrfToken($token);

        $this->assertFalse($isValid);
    }

    /**
     * Test that validateCsrfToken returns false when no token exists
     *
     * @return void
     */
    public function testValidateCsrfTokenReturnsFalseWhenNoTokenExists(): void
    {
        $isValid = validateCsrfToken('some_token');

        $this->assertFalse($isValid);
    }

    /**
     * Test that csrfField returns valid HTML
     *
     * @return void
     */
    public function testCsrfFieldReturnsValidHTML(): void
    {
        $field = csrfField();

        $this->assertStringContainsString('<input', $field);
        $this->assertStringContainsString('type="hidden"', $field);
        $this->assertStringContainsString('name="csrf_token"', $field);
        $this->assertStringContainsString('value="', $field);
    }

    /**
     * Test that csrfField uses existing token if available
     *
     * @return void
     */
    public function testCsrfFieldUsesExistingToken(): void
    {
        $token = generateCsrfToken();
        $field = csrfField();

        $this->assertStringContainsString(htmlspecialchars($token), $field);
    }

    /**
     * Test that csrfField generates new token if none exists
     *
     * @return void
     */
    public function testCsrfFieldGeneratesNewTokenIfNoneExists(): void
    {
        $_SESSION = [];
        $field = csrfField();

        $this->assertNotEmpty($field);
        $this->assertArrayHasKey('csrf_token', $_SESSION);
    }
}
