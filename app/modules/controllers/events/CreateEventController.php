<?php

    class CreateEventController {
        public function __construct() {
            $this -> loadView('createEventPageView');
        }

        public function loadView($viewName) {
            require_once __DIR__ . '/../../views/events/' . $viewName . '.php';
        }
    }