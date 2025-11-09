<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use App\Core\Application;
use App\Core\Session\SessionManager;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Security\CsrfProtection;
use App\Core\Auth\AuthManager;
use ReflectionClass;

/**
 * Unit tests for Application singleton
 */
class ApplicationTest extends TestCase
{
    protected function setUp(): void
    {
        // Reset singleton instance before each test
        $reflection = new ReflectionClass(Application::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);
    }

    protected function tearDown(): void
    {
        // Reset singleton after each test
        $reflection = new ReflectionClass(Application::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);
    }

    public function testGetInstanceReturnsSameInstance(): void
    {
        $app1 = Application::getInstance();
        $app2 = Application::getInstance();

        $this->assertSame($app1, $app2, 'getInstance should return the same instance');
    }

    public function testGetInstanceReturnsApplicationInstance(): void
    {
        $app = Application::getInstance();
        $this->assertInstanceOf(Application::class, $app);
    }

    public function testSessionReturnsSessionManager(): void
    {
        $app = Application::getInstance();
        $session = $app->session();

        $this->assertInstanceOf(SessionManager::class, $session);
    }

    public function testRequestReturnsRequest(): void
    {
        $app = Application::getInstance();
        $request = $app->request();

        $this->assertInstanceOf(Request::class, $request);
    }

    public function testResponseReturnsResponse(): void
    {
        $app = Application::getInstance();
        $response = $app->response();

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testCsrfReturnsCsrfProtection(): void
    {
        $app = Application::getInstance();
        $csrf = $app->csrf();

        $this->assertInstanceOf(CsrfProtection::class, $csrf);
    }

    public function testAuthReturnsAuthManager(): void
    {
        $app = Application::getInstance();
        $auth = $app->auth();

        $this->assertInstanceOf(AuthManager::class, $auth);
    }

    public function testBootStartsSession(): void
    {
        $app = Application::getInstance();
        
        // Session should start without errors
        $app->boot();
        
        $session = $app->session();
        $this->assertTrue($session->isStarted());
    }

    public function testServicesAreSingletonWithinApplication(): void
    {
        $app = Application::getInstance();

        // Call session() twice
        $session1 = $app->session();
        $session2 = $app->session();

        $this->assertSame($session1, $session2, 'Services should be singletons within Application');
    }

    public function testCannotCloneApplication(): void
    {
        $app = Application::getInstance();

        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Cannot clone singleton Application');

        $reflection = new ReflectionClass($app);
        $cloneMethod = $reflection->getMethod('__clone');
        $cloneMethod->setAccessible(true);
        $cloneMethod->invoke($app);
    }

    public function testCannotUnserializeApplication(): void
    {
        $app = Application::getInstance();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot unserialize singleton Application');

        $app->__wakeup();
    }
}
