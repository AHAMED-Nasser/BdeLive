<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use App\Core\Http\Response;

/**
 * Unit tests for Response
 */
class ResponseTest extends TestCase
{
    private Response $response;

    protected function setUp(): void
    {
        $this->response = new Response();
    }

    public function testDefaultStatusCodeIs200(): void
    {
        $this->assertEquals(200, $this->response->getStatusCode());
    }

    public function testSetStatusCodeUpdatesCode(): void
    {
        $this->response->setStatusCode(404);
        $this->assertEquals(404, $this->response->getStatusCode());
    }

    public function testSetStatusCodeReturnsInstanceForChaining(): void
    {
        $result = $this->response->setStatusCode(201);
        $this->assertSame($this->response, $result);
    }

    public function testSetHeaderStoresHeader(): void
    {
        $this->response->setHeader('Content-Type', 'application/json');
        
        $headers = $this->response->getHeaders();
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertEquals('application/json', $headers['Content-Type']);
    }

    public function testSetHeaderReturnsInstanceForChaining(): void
    {
        $result = $this->response->setHeader('X-Custom', 'value');
        $this->assertSame($this->response, $result);
    }

    public function testGetHeadersReturnsEmptyArrayByDefault(): void
    {
        $headers = $this->response->getHeaders();
        $this->assertIsArray($headers);
        $this->assertEmpty($headers);
    }

    public function testSetContentTypeSetsCorrectHeader(): void
    {
        $this->response->setContentType('text/html');
        
        $headers = $this->response->getHeaders();
        $this->assertEquals('text/html; charset=UTF-8', $headers['Content-Type']);
    }

    public function testSetContentTypeWithCustomCharset(): void
    {
        $this->response->setContentType('text/plain', 'ISO-8859-1');
        
        $headers = $this->response->getHeaders();
        $this->assertEquals('text/plain; charset=ISO-8859-1', $headers['Content-Type']);
    }

    public function testSetContentTypeReturnsInstanceForChaining(): void
    {
        $result = $this->response->setContentType('application/xml');
        $this->assertSame($this->response, $result);
    }

    public function testMethodChaining(): void
    {
        $result = $this->response
            ->setStatusCode(201)
            ->setHeader('X-Custom', 'value')
            ->setContentType('application/json');

        $this->assertSame($this->response, $result);
        $this->assertEquals(201, $this->response->getStatusCode());
        
        $headers = $this->response->getHeaders();
        $this->assertEquals('value', $headers['X-Custom']);
        $this->assertEquals('application/json; charset=UTF-8', $headers['Content-Type']);
    }

    public function testMultipleHeadersCanBeSet(): void
    {
        $this->response
            ->setHeader('X-Header-1', 'value1')
            ->setHeader('X-Header-2', 'value2')
            ->setHeader('X-Header-3', 'value3');

        $headers = $this->response->getHeaders();
        
        $this->assertCount(3, $headers);
        $this->assertEquals('value1', $headers['X-Header-1']);
        $this->assertEquals('value2', $headers['X-Header-2']);
        $this->assertEquals('value3', $headers['X-Header-3']);
    }

    public function testSendDoesNotThrowWhenHeadersNotSent(): void
    {
        // This test verifies that send() can be called without errors
        // In CLI mode, headers_sent() returns false, so send() should work
        
        // We can't test the actual header() calls in PHPUnit (CLI mode)
        // but we can verify the method doesn't throw exceptions
        
        $this->response->setStatusCode(200);
        $this->response->setHeader('X-Test', 'value');
        
        // Should not throw
        $this->response->send();
        
        $this->assertTrue(true); // Assert we reach here
    }

    public function testStatusCodeCanBeSetToVariousHttpCodes(): void
    {
        $codes = [200, 201, 204, 301, 302, 400, 401, 403, 404, 500, 503];

        foreach ($codes as $code) {
            $this->response->setStatusCode($code);
            $this->assertEquals($code, $this->response->getStatusCode());
        }
    }
}
