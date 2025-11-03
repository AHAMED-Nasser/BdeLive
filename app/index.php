<?php

// Configuration sécurisée des cookies de session
// Détection automatique de l'environnement
$isProduction = isset($_SERVER['HTTP_HOST']) &&
    strpos($_SERVER['HTTP_HOST'], 'alwaysdata.net') !== false;

session_set_cookie_params([
    'lifetime' => 1800,                      // 30 minutes
    'path' => '/',
    'domain' => $isProduction ? 'bdelivesae.alwaysdata.net' : '',
    'secure' => $isProduction,               // HTTPS uniquement en production
    'httponly' => true,                      // Inaccessible en JavaScript
    'samesite' => 'Lax',                     // Protection CSRF
]);

session_start();

// En-têtes de sécurité HTTP
header("X-Frame-Options: SAMEORIGIN");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once __DIR__ . '/../vendor/autoload.php'; // autoload des dépendances Cloudinary via Composer
require_once __DIR__ . '/rooter.php';
require_once __DIR__ . '/modules/views/shared/include.inc.php';
