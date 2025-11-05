<?php

/**
 * Require user authentication
 *
 * Checks if a user session is active and if the user is logged in.
 * Redirects to the login page with an error message if the user is not authenticated.
 *
 * @return void
 * @throws void Exits execution if user is not authenticated
 */
function requireLogin(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        header('Location: index.php?page=login&error=login_required');
        exit();
    }

    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php?page=login&error=login_required');
        exit();
    }
}

/**
 * Require events privileges
 *
 * Checks if a user session is active, if the user is logged in, and if the user
 * has events status ('BDE'). Redirects to login page if not authenticated, or
 * returns 403 Forbidden if authenticated but not events.
 *
 * @return void
 * @throws void Exits execution if user is not authenticated or not events
 */
function requireAdmin(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        header('Location: index.php?page=login&error=login_required');
        exit();
    }

    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php?page=login&error=login_required');
        exit();
    }

    if ($_SESSION['user_status'] !== 'BDE') {
        http_response_code(403);
        header('Location: index.php?page=home&error=access_denied');
        exit();
    }
}
