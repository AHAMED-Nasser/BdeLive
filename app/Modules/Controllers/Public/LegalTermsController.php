<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Public;

use App\Modules\Controllers\DefaultController;

/**
 * Legal Terms Controller
 *
 * Handles the display of legal terms and conditions page.
 *
 * @package BdeLive\Controllers
 * @version 1.0.0
 * @author BDELIVE - Group 8
 */
class LegalTermsController extends DefaultController
{
    /**
     * Display the legal terms page
     *
     * Loads and renders the legal terms view containing terms of service
     * and privacy policy information.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->render('public/legalTermsPageView');
    }
}
