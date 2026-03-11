<?php

declare(strict_types=1);

// Remove X-Powered-By header to avoid exposing PHP version (OWASP: Information Disclosure)
header_remove('X-Powered-By');

// Set timezone to France (Europe/Paris)
date_default_timezone_set('Europe/Paris');
use App\Core\Application;
use App\Core\Exception\AuthenticationException;
use App\Core\Exception\AuthorizationException;
use App\Core\Exception\CsrfException;
// Secure configuration of the session cookies (OWASP: Session Management)
// Must be called BEFORE session_start() to take effect
$isProduction = isset($_SERVER['HTTP_HOST']) &&
    strpos($_SERVER['HTTP_HOST'], 'alwaysdata.net') !== false;
$isSecure = $isProduction
    || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => $isProduction ? 'bdelivesae.alwaysdata.net' : '',
    'secure' => $isSecure,
    'httponly' => true,
    'samesite' => 'Lax',
]);
// HTTP security headers
header("X-Frame-Options: SAMEORIGIN");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=(), usb=()");
// CSP nonce for inline scripts (must be defined before include.inc.php)
$cspNonce = base64_encode(random_bytes(16));
if (!defined('CSP_NONCE')) {
    define('CSP_NONCE', $cspNonce);
}
// Content Security Policy (CSP) - Strict policy without unsafe-inline
$cspDirectives = [
    "default-src 'self'",
    "script-src 'self' 'nonce-" . $cspNonce . "' https://cdnjs.cloudflare.com " .
    "https://cdn.jsdelivr.net https://www.google.com https://www.gstatic.com",
    "style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://fonts.googleapis.com",
    "img-src 'self' data: https://res.cloudinary.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://fonts.googleapis.com https://fonts.gstatic.com",
    "font-src 'self' https://cdnjs.cloudflare.com https://fonts.gstatic.com",
    "connect-src 'self' https://www.google.com",
    "frame-src 'self' https://www.google.com https://www.recaptcha.net",
    "frame-ancestors 'self'",
    "base-uri 'self'",
    "form-action 'self'"
];
header("Content-Security-Policy: " . implode("; ", $cspDirectives));
if (!$isProduction) {
    error_reporting(E_ALL);
    ini_set('display_errors', (string) 1);
    ini_set('display_startup_errors', (string) 1);
} else {
    error_reporting(0);
    ini_set('display_errors', (string) 0);
}
// Composer autoload (PSR-4)
$projectRoot = dirname(__DIR__, 1);

if (file_exists($projectRoot . '/vendor/autoload.php')) {
    require_once $projectRoot . '/vendor/autoload.php';
}

// Secure loading of the .env
if (file_exists($projectRoot . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable($projectRoot);
    $dotenv->load();
} else {
    // If the file does not exist, display a clear message for the dev
    die("Erreur : Le fichier .env est introuvable à l'emplacement : " . $projectRoot);
}

// Define FROM_EMAIL for App\Config\Mailer (uses the constant in the constructor)
if (!defined('FROM_EMAIL') && !empty($_ENV['FROM_EMAIL'])) {
    define('FROM_EMAIL', (string) $_ENV['FROM_EMAIL']);
}

// Initialize the application (starts the session automatically)
$app = Application::getInstance();
$app->boot();
// Load the necessary includes for compatibility
require_once __DIR__ . '/Modules/views/shared/include.inc.php';
// Temporary helpers for compatibility with old code
// These functions will be removed after the complete migration
require_once __DIR__ . '/include/legacy_helpers.php';
// Centralized exception management (SOLID principle: separation of Auth/HTTP)
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

try {
    // Load and execute the router
    require_once __DIR__ . '/rooter.php';
} catch (AuthenticationException $e) {
    if ($isAjax) {
        http_response_code(401);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['error' => 'Non authentifié', 'redirect' => 'index.php?page=login']);
        exit();
    }
    // User not authenticated → redirect to login
    $app->session()->flash('error', $e->getMessage());
    $app->response()->redirect('index.php?page=login');
} catch (AuthorizationException $e) {
    if ($isAjax) {
        http_response_code(403);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['error' => 'Accès refusé', 'message' => $e->getMessage()]);
        exit();
    }
    // User does not have the permissions → 403 + redirect to home
    $app->session()->flash('error', $e->getMessage());
    $app->response()->setStatusCode(403)->redirect('index.php?page=home');
} catch (CsrfException $e) {
    if ($isAjax) {
        http_response_code(403);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['error' => 'Token CSRF invalide']);
        exit();
    }
    // Invalid CSRF token → redirect with error
    $app->session()->flash('error', $e->getMessage());
    $referer = $app->request()->server('HTTP_REFERER', 'index.php?page=home');
    $app->response()->redirect($referer);
} catch (\Exception $e) {
    // Generic server error → display error page
    http_response_code(500);
    error_log('Application Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    if ($isAjax) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['error' => 'Erreur serveur', 'message' => $isProduction ? 'Une erreur est survenue.' : $e->getMessage()]);
        exit();
    }
    if ($isProduction) {
        echo '<h1>Erreur serveur</h1><p>Une erreur est survenue. Veuillez réessayer ultérieurement.</p>';
    } else {
        echo '<h1>Erreur serveur (dev mode)</h1>';
        echo '<pre>' . $e->getMessage() . "\n\n" . $e->getTraceAsString() . '</pre>';
    }
}
