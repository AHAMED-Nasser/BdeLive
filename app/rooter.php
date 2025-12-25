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

require_once __DIR__ . '/Modules/views/shared/carousel.inc.php';
require_once __DIR__ . '/include/autoload.php';

// Ces fichiers sont maintenant gérés par legacy_helpers.php et Application
// require_once __DIR__ . '/include/auth.php';  // Remplacé par AuthManager
// require_once __DIR__ . '/include/csrf.php';  // Remplacé par CsrfProtection

$page = $_GET['page'] ?? 'home';

// #region agent log
file_put_contents('/home/g5kf55/PhpstormProjects/BdeLive/.cursor/debug.log', json_encode(['timestamp' => time() * 1000, 'location' => 'rooter.php:24', 'message' => 'Raw page parameter', 'data' => ['page' => $page, 'GET' => $_GET, 'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'N/A'], 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'A']) . "\n", FILE_APPEND);
// #endregion

/**
 * Keep only allowed characters for the page token and default to 'home' if empty.
 */
$sanitizePage = static function (string $page): string {
    $sanitized = (string) preg_replace('/[^a-zA-Z0-9_-]/', '', $page);

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

        return str_replace(' ', '', $string) !== '' ? str_replace(' ', '', $string) : 'Home';
    }
    // camelCase → StudlyCase
    $parts = preg_replace('/(^|[a-z])([A-Z])/', '$1 $2', $string);
    $result = $parts !== null ? ucwords($parts) : ucwords($string);
    $final = str_replace(' ', '', $result);

    return $final !== '' ? $final : 'Home';
};

$page = $sanitizePage($page);
// StudlyCase + 'Controller' naming convention
$shortName = $toStudlyCase($page) . 'Controller';

// #region agent log
file_put_contents('/home/g5kf55/PhpstormProjects/BdeLive/.cursor/debug.log', json_encode(['timestamp' => time() * 1000, 'location' => 'rooter.php:54', 'message' => 'After sanitization', 'data' => ['sanitizedPage' => $page, 'shortName' => $shortName], 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'A']) . "\n", FILE_APPEND);
// #endregion

// Try namespaced controllers across known groups
$namespaces = [
    'App\\Modules\\Controllers\\',
    'App\\Modules\\Controllers\\Public\\',
    'App\\Modules\\Controllers\\Users\\',
    'App\\Modules\\Controllers\\Events\\',
    'App\\Modules\\Controllers\\Articles\\',
    'App\\Modules\\Controllers\\Pwd\\',
    'App\\Modules\\Controllers\\Cookie\\',
];

$resolved = null;
foreach ($namespaces as $ns) {
    $fqcn = $ns . $shortName;
    // #region agent log
    file_put_contents('/home/g5kf55/PhpstormProjects/BdeLive/.cursor/debug.log', json_encode(['timestamp' => time() * 1000, 'location' => 'rooter.php:70', 'message' => 'Checking controller class', 'data' => ['fqcn' => $fqcn, 'exists' => class_exists($fqcn)], 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'B']) . "\n", FILE_APPEND);
    // #endregion
    if (class_exists($fqcn)) {
        $resolved = $fqcn;
        break;
    }
}

if ($resolved !== null) {
    // #region agent log
    file_put_contents('/home/g5kf55/PhpstormProjects/BdeLive/.cursor/debug.log', json_encode(['timestamp' => time() * 1000, 'location' => 'rooter.php:77', 'message' => 'Controller found, instantiating', 'data' => ['resolved' => $resolved], 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'C']) . "\n", FILE_APPEND);
    // #endregion
    new $resolved();
} else {
    // Backward compatibility: non-namespaced class if present
    if (class_exists($shortName)) {
        // #region agent log
        file_put_contents('/home/g5kf55/PhpstormProjects/BdeLive/.cursor/debug.log', json_encode(['timestamp' => time() * 1000, 'location' => 'rooter.php:82', 'message' => 'Non-namespaced controller found', 'data' => ['shortName' => $shortName], 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'D']) . "\n", FILE_APPEND);
        // #endregion
        new $shortName();
    } else {
        // #region agent log
        file_put_contents('/home/g5kf55/PhpstormProjects/BdeLive/.cursor/debug.log', json_encode(['timestamp' => time() * 1000, 'location' => 'rooter.php:86', 'message' => '404 - Controller not found', 'data' => ['page' => $page, 'shortName' => $shortName, 'namespacesChecked' => $namespaces], 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'E']) . "\n", FILE_APPEND);
        // #endregion
        http_response_code(404);
        echo 'Page non trouvée';
    }
}
