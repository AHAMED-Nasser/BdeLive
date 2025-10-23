<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuration sécurisée des cookies de session
session_set_cookie_params([
    'lifetime' => 1800,                      // 30 minutes
    'path' => '/',
    'domain' => 'bdelivesae.alwaysdata.net',
    'secure' => true,                        // HTTPS uniquement
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

require_once __DIR__ . '/rooter.php';
