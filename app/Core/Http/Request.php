<?php

declare(strict_types=1);

namespace App\Core\Http;

/**
 * Request - HTTP Superglobals Wrapper
 *
 * Encapsulates $_GET, $_POST, $_FILES, $_SERVER and $_COOKIE
 * to prevent direct superglobal access and facilitate testing.
 *
 * Features:
 * - Type-safe access to request data
 * - Default values support
 * - File upload handling
 * - HTTP method detection
 * - URL/host information
 *
 * @package App\Core\Http
 * @version 1.0.0
 */
class Request
{
    /**
     * @param array<string, mixed> $query GET parameters
     * @param array<string, mixed> $request POST parameters
     * @param array<string, mixed> $files Uploaded files
     * @param array<string, mixed> $server Server variables
     * @param array<string, mixed> $cookies Cookies
     */
    private function __construct(
        private array $query,
        private array $request,
        private array $files,
        private array $server,
        private array $cookies
    ) {
    }

    /**
     * Create a Request instance from PHP superglobals
     * 
     * @return self New Request with current superglobal values
     */
    public static function createFromGlobals(): self
    {
        return new self(
            $_GET,
            $_POST,
            $_FILES,
            $_SERVER,
            $_COOKIE
        );
    }

    /**
     * Create a custom Request instance (useful for testing)
     *
     * @param array<string, mixed> $query GET parameters
     * @param array<string, mixed> $request POST parameters
     * @param array<string, mixed> $files Uploaded files
     * @param array<string, mixed> $server Server variables
     * @param array<string, mixed> $cookies Cookies
     * @return self New Request with provided values
     */
    public static function create(
        array $query = [],
        array $request = [],
        array $files = [],
        array $server = [],
        array $cookies = []
    ): self {
        return new self($query, $request, $files, $server, $cookies);
    }

    /**
     * Get a GET parameter
     * 
     * @param string $key Parameter name
     * @param mixed $default Default value if not set
     * @return mixed Parameter value or default
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    /**
     * Get a POST parameter
     * 
     * @param string $key Parameter name
     * @param mixed $default Default value if not set
     * @return mixed Parameter value or default
     */
    public function post(string $key, mixed $default = null): mixed
    {
        return $this->request[$key] ?? $default;
    }

    /**
     * Get an uploaded file
     * 
     * @param string $key File input name
     * @return array<string, mixed>|null File data or null if not found
     */
    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    /**
     * Get a server variable
     * 
     * @param string $key Server variable name
     * @param mixed $default Default value if not set
     * @return mixed Server variable value or default
     */
    public function server(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }

    /**
     * Get a cookie value
     * 
     * @param string $key Cookie name
     * @param mixed $default Default value if not set
     * @return mixed Cookie value or default
     */
    public function cookie(string $key, mixed $default = null): mixed
    {
        return $this->cookies[$key] ?? $default;
    }

    /**
     * Get the HTTP method
     * 
     * @return string HTTP method in uppercase (GET, POST, etc.)
     */
    public function method(): string
    {
        return strtoupper((string) $this->server('REQUEST_METHOD', 'GET'));
    }

    /**
     * Check if this is a POST request
     * 
     * @return bool True if POST
     */
    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    /**
     * Check if this is a GET request
     * 
     * @return bool True if GET
     */
    public function isGet(): bool
    {
        return $this->method() === 'GET';
    }

    /**
     * Get all parameters (GET + POST merged)
     *
     * @return array<string, mixed> All request parameters
     */
    public function all(): array
    {
        return array_merge($this->query, $this->request);
    }

    /**
     * Check if a parameter exists (in GET or POST)
     * 
     * @param string $key Parameter name
     * @return bool True if parameter exists
     */
    public function has(string $key): bool
    {
        return isset($this->query[$key]) || isset($this->request[$key]);
    }

    /**
     * Get the request URI
     * 
     * @return string Request URI (e.g., /index.php?page=home)
     */
    public function uri(): string
    {
        return (string) $this->server('REQUEST_URI', '/');
    }

    /**
     * Get the host name
     * 
     * @return string Host name (e.g., localhost, example.com)
     */
    public function host(): string
    {
        return (string) $this->server('HTTP_HOST', 'localhost');
    }

    /**
     * Check if the request is over HTTPS
     * 
     * @return bool True if HTTPS
     */
    public function isSecure(): bool
    {
        $https = $this->server('HTTPS');
        return $https !== null && $https !== 'off';
    }
}
