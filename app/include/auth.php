<?php

function requireLogin(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php?page=login&error=login_required');
        exit();
    }
}

function requireAdmin(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
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
