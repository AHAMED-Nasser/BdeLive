<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Secure session cookies configuration
// Automatic environment detection
$isProduction = isset($_SERVER['HTTP_HOST']) &&
    strpos($_SERVER['HTTP_HOST'], 'alwaysdata.net') !== false;

session_set_cookie_params([
    'lifetime' => 1800,                      // 30 minutes
    'path' => '/',
    'domain' => $isProduction ? 'bdelivesae.alwaysdata.net' : '',
    'secure' => $isProduction,               // HTTPS only in production
    'httponly' => true,                      // Inaccessible by JavaScript
    'samesite' => 'Lax',                     // CSRF protection
]);

session_start();

// HTTP security headers
header("X-Frame-Options: SAMEORIGIN");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");

// Composer autoload (PSR-4)
require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/rooter.php';
