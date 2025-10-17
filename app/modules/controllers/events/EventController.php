<?php

    class EventController {
        public function __construct() {
            $this -> loadView('eventPageView');
        }

        public function loadView($viewName) {
            require_once __DIR__ . '/../../views/events/' . $viewName . '.php';
        }
    }