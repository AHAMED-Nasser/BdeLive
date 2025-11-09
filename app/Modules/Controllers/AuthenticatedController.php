<?php

declare(strict_types=1);

namespace App\Modules\Controllers;

/**
 * AuthenticatedController - Base Controller for Authenticated Pages
 *
 * This abstract controller requires users to be logged in.
 * Authentication is automatically checked in the constructor.
 *
 * If the user is not authenticated, an {@see AuthenticationException} is thrown
 * with HTTP 401 status code.
 *
 * Use this controller for:
 * - User profile pages
 * - Event registration
 * - Account management
 * - Any page requiring login
 *
 * @package App\Modules\Controllers
 * @version 1.0.0
 * @author BdeLive Team
 *
 * @see BaseController For available methods and properties
 * @see AdminController For admin-only pages
 *
 * @throws \App\Core\Exception\AuthenticationException If user is not logged in
 */
abstract class AuthenticatedController extends BaseController
{
    /**
     * Constructor - Automatically checks authentication
     *
     * @throws \App\Core\Exception\AuthenticationException If user is not authenticated
     */
    public function __construct()
    {
        parent::__construct();
        $this->auth->requireAuthentication();
    }
}
