<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use App\Core\Security\CsrfProtection;
use App\Core\Session\SessionManager;

/**
 * Tests unitaires pour CsrfProtection
 */
class CsrfProtectionTest extends TestCase
{
    private CsrfProtection $csrf;
    private SessionManager $session;

    protected function setUp(): void
    {
        $this->session = new SessionManager();
        $this->csrf = new CsrfProtection($this->session);
    }

    public function testGenerateTokenReturnsString(): void
    {
        $token = $this->csrf->generateToken();

        $this->assertIsString($token);
        $this->assertEquals(64, strlen($token)); // 32 bytes = 64 hex chars
    }

    public function testGenerateTokenStoresInSession(): void
    {
        $token = $this->csrf->generateToken();

        $this->assertTrue($this->session->has('csrf_token'));
        $this->assertEquals($token, $this->session->get('csrf_token'));
    }

    public function testGetTokenReturnsExistingToken(): void
    {
        $token1 = $this->csrf->generateToken();
        $token2 = $this->csrf->getToken();

        $this->assertEquals($token1, $token2);
    }

    public function testGetTokenGeneratesNewIfNotExists(): void
    {
        $token = $this->csrf->getToken();

        $this->assertIsString($token);
        $this->assertEquals(64, strlen($token));
    }

    public function testValidateTokenWithValidToken(): void
    {
        $token = $this->csrf->generateToken();

        $this->assertTrue($this->csrf->validateToken($token));
    }

    public function testValidateTokenWithInvalidToken(): void
    {
        $this->csrf->generateToken();

        $this->assertFalse($this->csrf->validateToken('invalid_token'));
    }

    public function testValidateTokenWithNoTokenInSession(): void
    {
        $this->assertFalse($this->csrf->validateToken('any_token'));
    }

    public function testGetTokenFieldReturnsHtmlInput(): void
    {
        $html = $this->csrf->getTokenField();

        $this->assertStringContainsString('<input', $html);
        $this->assertStringContainsString('type="hidden"', $html);
        $this->assertStringContainsString('name="csrf_token"', $html);
        $this->assertStringContainsString('value="', $html);
    }

    public function testInvalidateTokenRemovesToken(): void
    {
        $this->csrf->generateToken();
        $this->assertTrue($this->session->has('csrf_token'));

        $this->csrf->invalidateToken();
        $this->assertFalse($this->session->has('csrf_token'));
    }

    public function testTokensAreDifferentAcrossGenerations(): void
    {
        $token1 = $this->csrf->generateToken();
        $this->csrf->invalidateToken();
        $token2 = $this->csrf->generateToken();

        $this->assertNotEquals($token1, $token2);
    }
}
