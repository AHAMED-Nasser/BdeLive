<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Public;

use App\Modules\Controllers\AuthenticatedController;

/**
 * AboutController - Displays the "About" information page for members
 *
 * Extends AuthenticatedController to ensure only logged-in members
 * can access this page. Unauthenticated users will be redirected.
 *
 * @package BdeLive\Controllers
 * @version 1.0.0
 * @author BdeLive Team
 */
class AboutController extends AuthenticatedController
{
    /**
     * Constructor - Initializes the controller and renders the About view
     */
    public function __construct()
    {
        parent::__construct();

        // Load the "About" page view without photos
        $this->render('public/aboutView');
    }
}
