<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Admin;

use App\Modules\Controllers\AdminController;

class AdminSectionController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->render("admin/adminSectionView");
    }
}
