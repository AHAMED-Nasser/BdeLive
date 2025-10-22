<?php

    class CreateEventController extends AdminController {
        public function __construct() {
            parent::__construct();
            $this -> loadView('createEventPageView');
        }

        protected function loadView(string $viewName): void {
            require_once __DIR__ . '/../../views/events/{' . $viewName . '}.php';
        }
    }