<?php

/**
 * Generate a CSRF token and store it in session
 * @return string The generated token
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
 * @param string $token The token to validate
 * @return bool True if valid, false otherwise
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
 * @return string HTML input field
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
