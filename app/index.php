<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuration sécurisée des cookies de session AVANT tout appel à session_start()
// Détection automatique de l'environnement
$isProduction = isset($_SERVER['HTTP_HOST']) && 
    strpos($_SERVER['HTTP_HOST'], 'alwaysdata.net') !== false;

// Configuration stricte des cookies de session
session_set_cookie_params([
    'lifetime' => 1800,                      // 30 minutes
    'path' => '/',
    'domain' => $isProduction ? 'bdelivesae.alwaysdata.net' : '',
    'secure' => $isProduction,               // HTTPS uniquement en production
    'httponly' => true,                      // Inaccessible en JavaScript
    'samesite' => 'Strict',                  // Protection CSRF renforcée
]);

// Démarrage de la session avec la configuration sécurisée
session_start();

// En-têtes de sécurité HTTP
header("X-Frame-Options: SAMEORIGIN");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");

require_once __DIR__ . '/rooter.php';
