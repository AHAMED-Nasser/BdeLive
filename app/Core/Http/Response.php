<?php

declare(strict_types=1);

namespace App\Core\Http;

/**
 * Response - HTTP Response Management
 *
 * Encapsulates HTTP headers, status codes and redirections
 * to avoid direct use of header() and http_response_code().
 *
 * Features:
 * - HTTP status code management
 * - Custom headers
 * - Redirections (302, 301, etc.)
 * - JSON responses
 * - Content-Type management
 *
 * @package App\Core\Http
 * @version 1.0.0
 */
class Response
{
    private int $statusCode = 200;

    /** @var array<string, string> */
    private array $headers = [];

    /**
     * Set the HTTP status code
     *
     * @param int $code HTTP status code (200, 404, 500, etc.)
     * @return self For method chaining
     */
    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    /**
     * Get the HTTP status code
     *
     * @return int Current status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Set an HTTP header
     *
     * @param string $name Header name (e.g., Content-Type, Location)
     * @param string $value Header value
     * @return self For method chaining
     */
    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Get all HTTP headers
     *
     * @return array<string, string> All configured headers
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Redirect to a URL
     *
     * Sends Location header and terminates script execution.
     *
     * @param string $url Destination URL
     * @param int $statusCode Status code (302 = temporary, 301 = permanent)
     * @return never Terminates execution
     */
    public function redirect(string $url, int $statusCode = 302): never
    {
        $this->setStatusCode($statusCode);
        $this->setHeader('Location', $url);
        $this->send();
        exit();
    }

    /**
     * Send HTTP headers
     *
     * Sends all configured headers. Safe to call multiple times
     * (checks if headers already sent).
     *
     * @return void
     */
    public function send(): void
    {
        if (headers_sent()) {
            return;
        }

        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
    }

    /**
     * Set the Content-Type header
     *
     * @param string $contentType MIME type (e.g., text/html, application/json)
     * @param string $charset Character encoding (default: UTF-8)
     * @return self For method chaining
     */
    public function setContentType(string $contentType, string $charset = 'UTF-8'): self
    {
        return $this->setHeader('Content-Type', "$contentType; charset=$charset");
    }

    /**
     * Send a JSON response
     *
     * Sets Content-Type to application/json, encodes data and terminates.
     *
     * @param mixed $data Data to encode as JSON
     * @param int $statusCode HTTP status code
     * @return never Terminates execution
     */
    public function json(mixed $data, int $statusCode = 200): never
    {
        $this->setStatusCode($statusCode);
        $this->setContentType('application/json');
        $this->send();

        echo json_encode($data, JSON_THROW_ON_ERROR);
        exit();
    }
}
