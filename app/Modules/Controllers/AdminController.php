<?php

declare(strict_types=1);

namespace App\Modules\Controllers;

use App\Core\Application;
use App\Core\Auth\AuthManager;

/**
 * AdminController - Base Controller for Administrator Pages (BDE Only)
 *
 * This abstract controller requires users to be logged in AND have 'BDE' status.
 * Admin rights are automatically checked in the constructor.
 *
 * If the user is not authenticated, an {@see AuthenticationException} is thrown.
 * If the user is authenticated but not admin, an {@see AuthorizationException} is thrown
 * with HTTP 403 status code.
 *
 * Use this controller for:
 * - Event creation/deletion
 * - User management
 * - System administration
 * - Any BDE-only feature
 *
 * @package App\Modules\Controllers
 * @version 1.0.0
 * @author BdeLive Team
 *
 * @see BaseController For available methods and properties
 * @see AuthenticatedController For non-admin authenticated pages
 *
 * @throws \App\Core\Exception\AuthenticationException If user is not logged in
 * @throws \App\Core\Exception\AuthorizationException If user is not an administrator
 */
abstract class AdminController extends AuthenticatedController
{
    /**
     * Constructor - Automatically checks authentication and admin rights
     *
     * @throws \App\Core\Exception\AuthenticationException If user is not authenticated
     * @throws \App\Core\Exception\AuthorizationException If user lacks admin rights
     */
    public function __construct()
    {
        parent::__construct();

        $auth = Application::getInstance()->auth();

        if (!$auth->isAdmin()) {
            $this->redirectWithError(
                'index.php?page=home',
                'Accès refusé : vous n\'avez pas les droits administrateur.'
            );
        }

        $this->auth->requireAdmin();
    }
}
