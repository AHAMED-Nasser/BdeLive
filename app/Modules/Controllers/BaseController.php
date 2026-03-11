<?php

declare(strict_types=1);

namespace App\Modules\Controllers;

use App\Core\Application;
use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Session\SessionManager;
use App\Core\Security\CsrfProtection;
use App\Core\Auth\AuthManager;

/**
 * BaseController - Base Controller for All Controllers
 *
 * All controllers must extend this class.
 * Provides access to core services via Application singleton.
 *
 * Features:
 * - Automatic service injection (request, response, session, csrf, auth)
 * - View rendering with automatic data injection
 * - Flash message helpers
 * - CSRF validation helpers
 * - Redirect shortcuts
 *
 * @package App\Modules\Controllers
 * @author Bdelive - Group 8
 * @version 1.0.0
 */
abstract class BaseController
{
    protected Application $app;
    protected Request $request;
    protected Response $response;
    protected SessionManager $session;
    protected CsrfProtection $csrf;
    protected AuthManager $auth;

    /**
     * Initialize controller with core services from Application singleton
     */
    public function __construct()
    {
        $this->app = Application::getInstance();
        $this->request = $this->app->request();
        $this->response = $this->app->response();
        $this->session = $this->app->session();
        $this->csrf = $this->app->csrf();
        $this->auth = $this->app->auth();
    }

    /**
     * Render a view with automatic variable injection
     *
     * This method automatically injects services and data that all views need,
     * eliminating the need to directly access $_SESSION or global functions.
     *
     * Automatically injected variables:
     * - $csrf: CsrfProtection service
     * - $auth: AuthManager service
     * - $request: Request object
     * - $flash: Flash messages array (success, error, warning, info)
     * - $user: Current user data (null if not logged in)
     *
     * @param string $viewPath View path (e.g., 'users/loginPageView')
     * @param array<string, mixed> $data View-specific data
     * @return void
     */
    protected function render(string $viewPath, array $data = []): void
    {
        $globalData = [
            'csrf' => $this->csrf,
            'auth' => $this->auth,
            'request' => $this->request,

            'flash' => [
                'success' => $this->session->getFlash('success'),
                'error' => $this->session->getFlash('error'),
                'warning' => $this->session->getFlash('warning'),
                'info' => $this->session->getFlash('info'),
                'show_register_link' => $this->session->getFlash('show_register_link'),
            ],

            'user' => $this->auth->getUser(),
            'isAdmin' => $this->auth->isAdmin(),
        ];

        $allData = array_merge($data, $globalData);
        extract($allData);

        require_once __DIR__ . '/../views/' . $viewPath . '.php';
    }

    /**
     * Redirect to a URL
     *
     * @param string $url Destination URL
     * @return never Terminates execution
     */
    protected function redirect(string $url): never
    {
        $this->response->redirect($url);
    }

    /**
     * Set a flash message
     *
     * @param string $type Message type (success, error, warning, info)
     * @param string $message Message to display
     * @return void
     */
    protected function setFlash(string $type, string $message): void
    {
        $this->session->flash($type, $message);
    }

    /**
     * Set an error flash message
     *
     * @param string $message Error message
     * @return void
     */
    protected function setError(string $message): void
    {
        $this->setFlash('error', $message);
    }

    /**
     * Set a success flash message
     *
     * @param string $message Success message
     * @return void
     */
    protected function setSuccess(string $message): void
    {
        $this->setFlash('success', $message);
    }

    /**
     * Set a warning flash message
     *
     * @param string $message Warning message
     * @return void
     */
    protected function setWarning(string $message): void
    {
        $this->setFlash('warning', $message);
    }

    /**
     * Set an info flash message
     *
     * @param string $message Info message
     * @return void
     */
    protected function setInfo(string $message): void
    {
        $this->setFlash('info', $message);
    }

    /**
     * Validate the CSRF token from the request
     *
     * Supports both traditional POST body tokens and modern AJAX header tokens.
     * Checks in order:
     * 1. Provided token parameter
     * 2. POST body 'csrf_token' field
     * 3. X-CSRF-Token HTTP header (for AJAX requests)
     *
     * @param string|null $token Token to validate (if null, retrieves from POST or header)
     * @return bool True if valid, false otherwise
     */
    protected function validateCsrf(?string $token = null): bool
    {
        // Try provided token first
        if ($token !== null) {
            return $this->csrf->validateToken($token);
        }

        // Try POST body
        $token = $this->request->post('csrf_token', '');

        // If empty, try AJAX header
        if (empty($token)) {
            $token = $this->request->header('X-CSRF-Token', '');
        }

        return $this->csrf->validateToken((string) $token);
    }

    /**
     * Get the CSRF token for JavaScript usage
     *
     * This method is useful for embedding the token in JavaScript
     * or as a meta tag for automatic AJAX injection.
     *
     * @return string The current CSRF token
     */
    protected function getCsrfTokenForJs(): string
    {
        return $this->csrf->getToken();
    }

    /**
     * Redirect with an error message
     *
     * @param string $url Destination URL
     * @param string $message Error message
     * @return never Terminates execution
     */
    protected function redirectWithError(string $url, string $message): never
    {
        $this->setError($message);
        $this->redirect($url);
    }

    /**
     * Redirect with a success message
     *
     * @param string $url Destination URL
     * @param string $message Success message
     * @return never Terminates execution
     */
    protected function redirectWithSuccess(string $url, string $message): never
    {
        $this->setSuccess($message);
        $this->redirect($url);
    }
}
