<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use App\Core\Session\SessionManager;

/**
 * Tests unitaires pour SessionManager
 */
class SessionManagerTest extends TestCase
{
    private SessionManager $session;

    protected function setUp(): void
    {
        $this->session = new SessionManager();
    }

    public function testSetAndGet(): void
    {
        $this->session->set('test_key', 'test_value');
        $this->assertEquals('test_value', $this->session->get('test_key'));
    }

    public function testGetWithDefault(): void
    {
        $result = $this->session->get('nonexistent', 'default_value');
        $this->assertEquals('default_value', $result);
    }

    public function testHas(): void
    {
        $this->session->set('exists', 'value');
        $this->assertTrue($this->session->has('exists'));
        $this->assertFalse($this->session->has('not_exists'));
    }

    public function testRemove(): void
    {
        $this->session->set('to_remove', 'value');
        $this->assertTrue($this->session->has('to_remove'));
        
        $this->session->remove('to_remove');
        $this->assertFalse($this->session->has('to_remove'));
    }

    public function testFlash(): void
    {
        $this->session->flash('success', 'Test message');
        $message = $this->session->getFlash('success');
        
        $this->assertEquals('Test message', $message);
        
        // Flash message should be consumed after first retrieval
        $this->assertNull($this->session->getFlash('success'));
    }

    public function testFlashWithMultipleTypes(): void
    {
        $this->session->flash('success', 'Success message');
        $this->session->flash('error', 'Error message');
        $this->session->flash('warning', 'Warning message');
        
        $this->assertEquals('Success message', $this->session->getFlash('success'));
        $this->assertEquals('Error message', $this->session->getFlash('error'));
        $this->assertEquals('Warning message', $this->session->getFlash('warning'));
    }

    public function testGetFlashReturnsNullForNonexistent(): void
    {
        $result = $this->session->getFlash('nonexistent');
        $this->assertNull($result);
    }
}
