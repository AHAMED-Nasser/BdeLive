<?php

abstract class AuthenticatedController extends DefaultController {
    public function __construct() {
        parent::__construct();
        requireLogin();
    }
}