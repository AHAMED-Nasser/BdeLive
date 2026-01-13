<?php

declare(strict_types=1);

namespace App\Core\Security;

use App\Core\Session\SessionManager;
use App\Core\Exception\CsrfException;

/**
 * CsrfProtection - CSRF Token Management
 *
 * Replaces procedural functions from app/include/csrf.php
 * Handles generation and validation of CSRF tokens in an OOP manner.
 *
 * CSRF (Cross-Site Request Forgery) protection prevents unauthorized
 * commands from being transmitted from a user the web application trusts.
 *
 * Features:
 * - Cryptographically secure token generation
 * - Time-based token expiration (1 hour)
 * - Timing-safe token comparison
 * - HTML field generation for forms
 *
 * @package App\Core\Security
 * @version 1.0.0
 */
class CsrfProtection
{
    private const TOKEN_KEY = 'csrf_token';
    private const TOKEN_TIME_KEY = 'csrf_token_time';
    private const TOKEN_LIFETIME = 3600;

    public function __construct(
        private SessionManager $session
    ) {
    }

    /**
     * Generate a new CSRF token
     *
     * Creates a cryptographically secure random token and stores it
     * in the session with a timestamp.
     *
     * @return string The generated token (64 hex characters)
     */
    public function generateToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->session->set(self::TOKEN_KEY, $token);
        $this->session->set(self::TOKEN_TIME_KEY, time());
        return $token;
    }

    /**
     * Validate a CSRF token
     *
     * Checks if the provided token matches the session token and
     * hasn't expired (1 hour lifetime).
     * Uses timing-safe comparison to prevent timing attacks.
     *
     * @param string $token The token to validate
     * @return bool True if valid, false otherwise
     */
    public function validateToken(string $token): bool
    {
        // Empty token is always invalid
        if (empty($token)) {
            return false;
        }

        if (!$this->session->has(self::TOKEN_KEY)) {
            return false;
        }

        $sessionToken = $this->session->get(self::TOKEN_KEY);

        if (empty($sessionToken)) {
            return false;
        }

        if (!hash_equals((string) $sessionToken, $token)) {
            return false;
        }

        $tokenTime = $this->session->get(self::TOKEN_TIME_KEY, 0);
        if ((time() - (int) $tokenTime) > self::TOKEN_LIFETIME) {
            return false;
        }

        return true;
    }

    /**
     * Validate a CSRF token and throw exception if invalid
     *
     * Convenience method for strict validation in controllers.
     *
     * @param string $token The token to validate
     * @return void
     * @throws CsrfException If token is invalid or expired
     */
    public function requireValidToken(string $token): void
    {
        if (!$this->validateToken($token)) {
            throw new CsrfException('Invalid or expired CSRF token');
        }
    }

    /**
     * Get the current CSRF token (or generate a new one)
     *
     * Returns the existing token from session, or generates a new one
     * if none exists.
     *
     * @return string The current CSRF token
     */
    public function getToken(): string
    {
        if (!$this->session->has(self::TOKEN_KEY)) {
            return $this->generateToken();
        }

        return (string) $this->session->get(self::TOKEN_KEY);
    }

    /**
     * Generate an HTML hidden input field with the CSRF token
     *
     * Convenience method for forms. Use this in all POST forms:
     * <form method="POST">
     *     <?= $csrf->getTokenField() ?>
     *     ...
     * </form>
     *
     * @return string HTML input tag with CSRF token
     */
    public function getTokenField(): string
    {
        $token = $this->getToken();
        return '<input type="hidden" name="csrf_token" value="' .
               htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Invalidate the current CSRF token
     *
     * Removes the token from session, forcing generation of a new one.
     * Useful after logout or when rotating tokens for extra security.
     *
     * @return void
     */
    public function invalidateToken(): void
    {
        $this->session->remove(self::TOKEN_KEY);
        $this->session->remove(self::TOKEN_TIME_KEY);
    }
}
