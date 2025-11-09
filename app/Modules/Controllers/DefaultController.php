<?php

declare(strict_types=1);

namespace App\Modules\Controllers;

/**
 * DefaultController - Base Controller for Public Pages
 *
 * This abstract controller is used for pages accessible without authentication.
 * Extends BaseController without adding any access restrictions.
 *
 * Use this controller for:
 * - Home page
 * - Login/Register pages
 * - Public information pages
 * - Legal pages
 *
 * All users (authenticated or not) can access controllers extending this class.
 *
 * @package App\Modules\Controllers
 * @version 1.0.0
 * @author BdeLive Team
 * 
 * @see BaseController For available methods and properties
 */
abstract class DefaultController extends BaseController
{
    // No authentication required
    // Public pages can be accessed by everyone
}
