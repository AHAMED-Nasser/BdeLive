<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuration sécurisée des cookies de session
session_set_cookie_params([
    'lifetime' => 1800,                      // 30 minutes
    'path' => '/',
    'domain' => 'bdelivesae.alwaysdata.net',
    'secure' => true,                        // Uniquement HTTPS
    'httponly' => true,                      // Inaccessible en JavaScript
    'samesite' => 'Lax',                      // Protection CSRF
]);

session_start();
require_once __DIR__ . '/rooter.php';
