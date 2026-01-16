<?php

/**
 * Generate a CSRF token and store it in session
 *
 * Generates a cryptographically secure random token (64 hex characters)
 * and stores it in the session along with a timestamp.
 *
 * @return string The generated token (64 hex characters), or empty string if session is not active
 */
function generateCsrfToken(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        return '';
    }

    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;
    $_SESSION['csrf_token_time'] = time();

    return $token;
}

/**
 * Validate CSRF token
 *
 * Validates that the provided token matches the token stored in the session
 * and that the token has not expired (1 hour expiration).
 *
 * @param string $token The token to validate
 * @return bool True if the token is valid and not expired, false otherwise
 */
function validateCsrfToken(string $token): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        return false;
    }

    // Check if token exists in session
    if (! isset($_SESSION['csrf_token'])) {
        return false;
    }

    // Check if token matches
    if (! hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }

    // Check if token is not expired (1 hour)
    if (isset($_SESSION['csrf_token_time']) && (time() - $_SESSION['csrf_token_time']) > 3600) {
        return false;
    }

    return true;
}

/**
 * Get CSRF token field HTML
 *
 * Generates an HTML hidden input field containing the CSRF token.
 * Uses an existing token if available, otherwise generates a new one.
 *
 * @return string HTML input field with CSRF token, or empty string if session is not active
 */
function csrfField(): string
{
    // Use existing token if available, otherwise generate a new one
    if (session_status() === PHP_SESSION_NONE) {
        return '';
    }

    if (!isset($_SESSION['csrf_token'])) {
        $token = generateCsrfToken();
    } else {
        $token = $_SESSION['csrf_token'];
    }

    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}
