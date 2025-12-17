<?php

namespace App\Modules\Controllers\Public;

use App\Modules\Controllers\DefaultController;
use Controller;

class HistoryController extends DefaultController
{
    public function __construct()
    {
        parent::__construct();
        $this->render('public/historyView');
    }
}
