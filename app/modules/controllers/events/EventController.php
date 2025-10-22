<?php

    class EventController {
        public function __construct() {
            $this -> loadView('eventPageView');
        }

        public function loadView(string $viewName): void{
            require_once __DIR__ . '/../../views/events/' . $viewName . '.php';
        }
    }