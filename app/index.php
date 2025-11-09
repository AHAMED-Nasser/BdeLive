<?php

// Configuration sécurisée des cookies de session
// Détection automatique de l'environnement
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

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

//// Composer autoload (PSR-4) - Optional, falls back to custom autoloader if not available
//if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
//    require_once __DIR__ . '/../vendor/autoload.php';
//}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/rooter.php';
require_once __DIR__ . '/Modules/views/shared/include.inc.php';
