<?php

abstract class AdminController extends DefaultController
{
    public function __construct()
    {
        parent::__construct();
        requireAdmin();
    }
}
