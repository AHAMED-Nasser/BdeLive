<?php
/**
 * Application router
 *
 * Purpose
 * - Resolve the controller class from the `page` query parameter without a static map.
 * - Accepted formats for `page`: snake_case, kebab-case, camelCase.
 * - Resolution rule: StudlyCase(page) + "Controller" (e.g. "legal-terms" → "LegalTermsController").
 * - If the class does not exist: respond with HTTP 404.
 *
 * Convention
 * - Create controllers named <Feature>Controller inside modules/controllers/* (autoloaded paths).
 * - No router edits are required when adding a new controller that follows this convention.
 */

    require_once __DIR__ . '/modules/views/shared/include.inc.php';
    require_once __DIR__ . '/modules/views/shared/carousel.inc.php';
    require_once __DIR__ . '/include/autoload.php';

    $page = $_GET['page'] ?? 'home';

    /**
     * Keep only allowed characters for the page token and default to 'home' if empty.
     */
    $sanitizePage = static function (string $page): string {
        $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '', $page);
        return $sanitized !== '' ? $sanitized : 'home';
    };

    /**
     * Convert snake_case, kebab-case, or camelCase to StudlyCase.
     * Examples: 'legal-terms' → 'LegalTerms', 'forgot_password' → 'ForgotPassword', 'legalTerms' → 'LegalTerms'.
     */
    $toStudlyCase = static function (string $string): string {
        if (strpos($string, '-') !== false || strpos($string, '_') !== false) {
            $string = str_replace(['-', '_'], ' ', $string);
            $string = ucwords($string);
            return str_replace(' ', '', $string);
        }
        // camelCase → StudlyCase
        $string = preg_replace('/(^|[a-z])([A-Z])/', '$1 $2', $string);
        $string = ucwords($string);
        return str_replace(' ', '', $string);
    };

    $page = $sanitizePage($page);
    // StudlyCase + 'Controller' naming convention
    $controllerName = $toStudlyCase($page) . 'Controller';

    if (class_exists($controllerName)) {
        new $controllerName();
    } else {
        http_response_code(404);
        echo 'Page non trouvée';
    }

