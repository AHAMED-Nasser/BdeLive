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
 * - Time-based token expiration (4 hours with sliding window)
 * - Timing-safe token comparison
 * - HTML field generation for forms
 * - Automatic token refresh on valid usage
 *
 * @author BDELIVE - Groupe 8
 * @package App\Core\Security
 * @version 2.0.0
 */
class CsrfProtection
{
    private const TOKEN_KEY = 'csrf_token';
    private const TOKEN_TIME_KEY = 'csrf_token_time';
    private const TOKEN_LIFETIME = 14400; // 4 hours (increased from 1 hour to prevent false positives)

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
     * hasn't expired (4 hour lifetime with sliding window).
     * Uses timing-safe comparison to prevent timing attacks.
     * Automatically refreshes the token timestamp on successful validation.
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

        // Sliding window: refresh token timestamp on successful validation
        // This extends the lifetime for active users without regenerating the token
        $this->refreshToken();

        return true;
    }

    /**
     * Refresh the CSRF token timestamp
     *
     * Updates the token timestamp to the current time without regenerating
     * the token itself. This implements a "sliding window" expiration strategy
     * where active users don't experience token expiration.
     *
     * @return void
     */
    public function refreshToken(): void
    {
        if ($this->session->has(self::TOKEN_KEY)) {
            $this->session->set(self::TOKEN_TIME_KEY, time());
        }
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
