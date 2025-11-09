<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Public;

use App\Modules\Controllers\DefaultController;

/**
 * SitemapController - Site Map Display
 *
 * Displays the site navigation map with all available pages.
 * Accessible to all users (authenticated or not).
 *
 * @package BdeLive\Controllers\Public
 * @version 1.0.0
 * @author BdeLive Team
 * 
 * @see DefaultController For base functionality
 */
class SitemapController extends DefaultController
{
    public function __construct()
    {
        parent::__construct();
        $this->render('public/sitemapView');
    }
}
