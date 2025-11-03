<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Users;

use App\Modules\Controllers\AuthenticatedController;

class ProfileController extends AuthenticatedController
{
    public function __construct()
    {
        parent::__construct();
        $this->render('users/profilePageView');
    }

    protected function loadView(string $viewName): void
    {
        $this->render('users/' . $viewName);
    }
}
