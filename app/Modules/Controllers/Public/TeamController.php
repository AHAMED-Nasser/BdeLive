<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Public;

use App\Modules\Controllers\DefaultController;

/**
 * TeamController - Team Page Display
 *
 * Displays information about the BDE team members.
 * Accessible to all users (authenticated or not).
 *
 * @package BdeLive\Controllers\Public
 * @version 1.0.0
 * @author BdeLive Team
 * 
 * @see DefaultController For base functionality
 */
class TeamController extends DefaultController
{
    public function __construct()
    {
        parent::__construct();
        $this->render('public/teamView');
    }
}
