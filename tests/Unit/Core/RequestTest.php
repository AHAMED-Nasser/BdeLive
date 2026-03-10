<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use App\Core\Http\Request;

/**
 * Unit tests for Request
 */
class RequestTest extends TestCase
{
    private Request $request;

    protected function setUp(): void
    {
        // Simulate request data
        $_GET = ['page' => 'home', 'id' => '42'];
        $_POST = ['email' => 'test@example.com', 'password' => 'secret'];
        $_SERVER = [
            'REQUEST_METHOD' => 'POST',
            'HTTP_HOST' => 'localhost',
            'REQUEST_URI' => '/index.php?page=home',
        ];

        $this->request = Request::createFromGlobals();
    }

    protected function tearDown(): void
    {
        $_GET = [];
        $_POST = [];
        $_SERVER = [];
    }

    public function testGetReturnsQueryParameter(): void
    {
        $this->assertEquals('home', $this->request->get('page'));
        $this->assertEquals('42', $this->request->get('id'));
    }

    public function testGetReturnsDefaultForNonexistent(): void
    {
        $this->assertEquals('default', $this->request->get('nonexistent', 'default'));
        $this->assertNull($this->request->get('nonexistent'));
    }

    public function testPostReturnsPostData(): void
    {
        $this->assertEquals('test@example.com', $this->request->post('email'));
        $this->assertEquals('secret', $this->request->post('password'));
    }

    public function testPostReturnsDefaultForNonexistent(): void
    {
        $this->assertEquals('default', $this->request->post('nonexistent', 'default'));
        $this->assertNull($this->request->post('nonexistent'));
    }

    public function testIsPost(): void
    {
        $this->assertTrue($this->request->isPost());
    }

    public function testIsGet(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $request = Request::createFromGlobals();

        $this->assertTrue($request->isGet());
        $this->assertFalse($request->isPost());
    }

    public function testGetMethod(): void
    {
        $this->assertEquals('POST', $this->request->method());
    }

    public function testServerReturnsServerVariable(): void
    {
        $this->assertEquals('localhost', $this->request->server('HTTP_HOST'));
        $this->assertEquals('/index.php?page=home', $this->request->server('REQUEST_URI'));
    }

    public function testServerReturnsDefaultForNonexistent(): void
    {
        $this->assertEquals('default', $this->request->server('NONEXISTENT', 'default'));
        $this->assertNull($this->request->server('NONEXISTENT'));
    }

    public function testHas(): void
    {
        $this->assertTrue($this->request->has('page'));
        $this->assertTrue($this->request->has('email'));
        $this->assertFalse($this->request->has('nonexistent'));
    }
}
