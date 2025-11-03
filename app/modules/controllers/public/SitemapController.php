<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Public;

use App\Modules\Controllers\DefaultController;

class SitemapController extends DefaultController
{
    public function __construct()
    {
        parent::__construct();
        $this->render('public/sitemapView');
    }
}
