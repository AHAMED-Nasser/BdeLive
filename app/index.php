<?php

declare(strict_types=1);

// Load configuration
require_once __DIR__ . '/config/config.php';

// Set timezone to France (Europe/Paris)
date_default_timezone_set('Europe/Paris');

use App\Core\Application;
use App\Core\Exception\AuthenticationException;
use App\Core\Exception\AuthorizationException;
use App\Core\Exception\CsrfException;

// Configuration sécurisée des cookies de session
// Détection automatique de l'environnement
$isProduction = isset($_SERVER['HTTP_HOST']) &&
    strpos($_SERVER['HTTP_HOST'], 'alwaysdata.net') !== false;

// Ne pas définir de domaine explicite pour permettre le fonctionnement
// sur tous les sous-domaines et éviter les problèmes de cookies
// Le domaine vide permet à PHP d'utiliser automatiquement le domaine de la requête
session_set_cookie_params([
    'lifetime' => 1800,                      // 30 minutes
    'path' => '/',
    'domain' => '',                          // Domaine vide = domaine automatique
    'secure' => $isProduction,               // HTTPS only in production
    'httponly' => true,                      // Inaccessible by JavaScript
    'samesite' => 'Lax',                     // CSRF protection
]);

// HTTP security headers
header("X-Frame-Options: SAMEORIGIN");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");

// Content Security Policy (CSP) - Protection contre XSS
$cspDirectives = [
    "default-src 'self'",
    "script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com " .
        "https://cdn.jsdelivr.net https://www.googletagmanager.com",
    "style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com " .
        "https://cdn.jsdelivr.net https://fonts.googleapis.com",
    "img-src 'self' data: https: http:",
    "font-src 'self' https://cdnjs.cloudflare.com https://fonts.gstatic.com",
    "connect-src 'self'",
    "frame-ancestors 'self'",
    "base-uri 'self'",
    "form-action 'self'"
];
header("Content-Security-Policy: " . implode("; ", $cspDirectives));

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Composer autoload (PSR-4)
require_once __DIR__ . '/../vendor/autoload.php';

// Initialiser l'application (démarre la session automatiquement)
$app = Application::getInstance();
$app->boot();

// Charger les includes nécessaires pour la compatibilité
require_once __DIR__ . '/Modules/views/shared/include.inc.php';

// Helpers temporaires pour compatibilité avec ancien code
// Ces fonctions seront supprimées après migration complète
require_once __DIR__ . '/include/legacy_helpers.php';

// Gestion centralisée des exceptions (principe SOLID: séparation Auth/HTTP)
try {
    // Charger et exécuter le routeur
    require_once __DIR__ . '/rooter.php';
} catch (AuthenticationException $e) {
    // Utilisateur non authentifié → rediriger vers login
    $app->session()->flash('error', $e->getMessage());
    $app->response()->redirect('index.php?page=login');
} catch (AuthorizationException $e) {
    // Utilisateur n'a pas les permissions → 403 + redirection home
    $app->session()->flash('error', $e->getMessage());
    $app->response()->setStatusCode(403)->redirect('index.php?page=home');
} catch (CsrfException $e) {
    // Token CSRF invalide → rediriger avec erreur
    $app->session()->flash('error', $e->getMessage());
    $referer = $app->request()->server('HTTP_REFERER', 'index.php?page=home');
    $app->response()->redirect($referer);
} catch (\Exception $e) {
    // Erreur serveur générique → afficher page d'erreur
    http_response_code(500);
    error_log('Application Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

    if ($isProduction) {
        echo '<h1>Erreur serveur</h1><p>Une erreur est survenue. Veuillez réessayer ultérieurement.</p>';
    } else {
        echo '<h1>Erreur serveur (dev mode)</h1>';
        echo '<pre>' . $e->getMessage() . "\n\n" . $e->getTraceAsString() . '</pre>';
    }
}
