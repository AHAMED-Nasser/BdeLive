<?php
class HomePageController {

    public function __construct() {
        $this -> loadView('homePageView');
    }

    public function loadView($viewName) {
        require_once __DIR__ . '/../views/' . $viewName . '.php';
    }
}
?>
