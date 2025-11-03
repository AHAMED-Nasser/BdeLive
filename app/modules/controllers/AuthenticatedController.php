<?php

declare(strict_types=1);

namespace App\Modules\Controllers;

abstract class AuthenticatedController extends DefaultController
{
    public function __construct()
    {
        parent::__construct();
        requireLogin();
    }
}
