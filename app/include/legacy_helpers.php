<?php

/**
 * Helper Functions - Compatibility Layer
 *
 * Provides convenient global functions that wrap the OOP architecture.
 * These functions are maintained for backward compatibility and convenience.
 *
 * While the OOP approach (using $csrf, $auth in views, and BaseController methods)
 * is recommended, these functions remain available as a stable compatibility layer.
 *
 * @package BdeLive\Include
 * @version 1.0.0
 * @author BdeLive Team
 *
 * @see \App\Core\Application For the OOP architecture
 * @see \App\Modules\Controllers\BaseController For controller base class
 */

declare(strict_types=1);

use App\Core\Application;

/**
 * Generate a CSRF token hidden input field
 *
 * Convenience function that wraps CsrfProtection::getTokenField().
 *
 * @return string HTML hidden input with CSRF token
 *
 * @see \App\Core\Security\CsrfProtection::getTokenField()
 *
 * @example
 * <form method="POST">
 *     <?= csrfField() ?>
 *     <!-- form fields -->
 * </form>
 *
 * // Or use OOP approach in views:
 * <?= $csrf->getTokenField() ?>
 */
function csrfField(): string
{
    return Application::getInstance()->csrf()->getTokenField();
}

/**
 * Validate a CSRF token
 *
 * Convenience function that wraps CsrfProtection::validateToken().
 *
 * @param string $token The CSRF token to validate
 * @return bool True if valid, false otherwise
 *
 * @see \App\Core\Security\CsrfProtection::validateToken()
 *
 * @example
 * if (validateCsrfToken($_POST['csrf_token'])) {
 *     // Token is valid
 * }
 *
 * // Or use OOP approach in controllers:
 * if ($this->csrf->validateToken($token)) { ... }
 */
function validateCsrfToken(string $token): bool
{
    return Application::getInstance()->csrf()->validateToken($token);
}

/**
 * Generate a new CSRF token
 *
 * Convenience function that wraps CsrfProtection::generateToken().
 *
 * @return string The generated CSRF token (64 hex characters)
 *
 * @see \App\Core\Security\CsrfProtection::generateToken()
 *
 * @example
 * $token = generateCsrfToken();
 *
 * // Or use OOP approach in controllers:
 * $token = $this->csrf->generateToken();
 */
function generateCsrfToken(): string
{
    return Application::getInstance()->csrf()->generateToken();
}

/**
 * Require user authentication
 *
 * Checks if user is authenticated and redirects to login page if not.
 * Convenience function that wraps AuthManager::requireAuthentication().
 *
 * @return void Redirects to login page if not authenticated
 *
 * @see \App\Core\Auth\AuthManager::requireAuthentication()
 * @see \App\Modules\Controllers\AuthenticatedController For OOP approach
 *
 * @example
 * // In procedural code:
 * requireLogin();
 *
 * // Or use OOP approach - extend AuthenticatedController:
 * class MyController extends AuthenticatedController {
 *     // Authentication checked automatically in constructor
 * }
 */
function requireLogin(): void
{
    try {
        Application::getInstance()->auth()->requireAuthentication();
    } catch (\App\Core\Exception\AuthenticationException $e) {
        Application::getInstance()->session()->flash('error', $e->getMessage());
        header('Location: index.php?page=login&error=login_required');
        exit();
    }
}

/**
 * Require administrator rights (BDE)
 *
 * Checks if user is authenticated and has admin (BDE) status.
 * Redirects to login or home page with error message if requirements not met.
 * Convenience function that wraps AuthManager::requireAdmin().
 *
 * @return void Redirects if not authenticated or not admin
 *
 * @see \App\Core\Auth\AuthManager::requireAdmin()
 * @see \App\Modules\Controllers\AdminController For OOP approach
 *
 * @example
 * // In procedural code:
 * requireAdmin();
 *
 * // Or use OOP approach - extend AdminController:
 * class MyAdminController extends AdminController {
 *     // Admin rights checked automatically in constructor
 * }
 */
function requireAdmin(): void
{
    try {
        Application::getInstance()->auth()->requireAdmin();
    } catch (\App\Core\Exception\AuthenticationException $e) {
        Application::getInstance()->session()->flash('error', $e->getMessage());
        header('Location: index.php?page=login&error=login_required');
        exit();
    } catch (\App\Core\Exception\AuthorizationException $e) {
        Application::getInstance()->session()->flash('error', $e->getMessage());
        http_response_code(403);
        header('Location: index.php?page=home&error=access_denied');
        exit();
    }
}
