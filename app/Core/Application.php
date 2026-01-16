<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Session\SessionManager;
use App\Core\Security\CsrfProtection;
use App\Core\Auth\AuthManager;

/**
 * Application - Main Container (Singleton)
 *
 * This class is the main entry point of the application.
 * It initializes and provides access to all core services.
 *
 * Pattern: Singleton (Service Locator)
 *
 * This class implements the Singleton pattern to ensure a single instance
 * manages all core services throughout the application lifecycle.
 * It provides centralized access to:
 * - Session management
 * - HTTP request/response handling
 * - CSRF protection
 * - Authentication/authorization
 *
 * @author BDELIVE - Groupe 8
 * @package App\Core
 * @version 3.6.8
 */
class Application
{
    private static ?Application $instance = null;

    private SessionManager $session;
    private Request $request;
    private Response $response;
    private CsrfProtection $csrf;
    private AuthManager $auth;

    /**
     * Private constructor (Singleton pattern)
     *
     * Initializes all core services in dependency order:
     * 1. SessionManager - for session handling
     * 2. Request - HTTP request encapsulation
     * 3. Response - HTTP response management
     * 4. CsrfProtection - CSRF token handling (depends on SessionManager)
     * 5. AuthManager - Authentication/authorization (depends on SessionManager)
     */
    private function __construct()
    {
        $this->session = new SessionManager();
        $this->request = Request::createFromGlobals();
        $this->response = new Response();
        $this->csrf = new CsrfProtection($this->session);
        $this->auth = new AuthManager($this->session);
    }

    /**
     * Get the singleton instance of the application
     *
     * Creates the instance on first call, then returns the same instance.
     *
     * @return self The unique Application instance
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Boot the application
     *
     * Starts the session and prepares the environment for request handling.
     * Must be called once at application startup before processing requests.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->session->start();
    }

    /**
     * Get the session manager instance
     *
     * Provides access to session operations like get/set/flash messages.
     *
     * @return SessionManager The session manager for handling $_SESSION
     */
    public function session(): SessionManager
    {
        return $this->session;
    }

    /**
     * Get the HTTP request instance
     *
     * Provides access to GET/POST/SERVER data in an OOP manner.
     *
     * @return Request The current HTTP request with encapsulated superglobals
     */
    public function request(): Request
    {
        return $this->request;
    }

    /**
     * Get the HTTP response instance
     *
     * Provides methods for redirects, headers, and status codes.
     *
     * @return Response The response handler for HTTP output
     */
    public function response(): Response
    {
        return $this->response;
    }

    /**
     * Get the CSRF protection service
     *
     * Provides token generation, validation, and HTML field generation.
     *
     * @return CsrfProtection The CSRF protection service
     */
    public function csrf(): CsrfProtection
    {
        return $this->csrf;
    }

    /**
     * Get the authentication manager
     *
     * Handles user login, logout, and permission checks.
     *
     * @return AuthManager The authentication manager
     */
    public function auth(): AuthManager
    {
        return $this->auth;
    }

    /**
     * Prevent cloning (Singleton pattern)
     *
     * @throws \Error Always throws to prevent cloning
     */
    private function __clone(): void
    {
        throw new \Error('Cannot clone singleton Application');
    }

    /**
     * Prevent unserialization (Singleton pattern)
     *
     * @throws \Exception Always throws to prevent unserialization
     */
    public function __wakeup(): void
    {
        throw new \Exception('Cannot unserialize singleton Application');
    }
}
