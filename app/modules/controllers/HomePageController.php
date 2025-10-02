<?php
class HomePageController {

    public function index() {
        $this -> loadView('homePageView');
    }

    public function loadView($viewName) {
        require_once __DIR__ . '/../views/' . $viewName . '.php';
    }
}
?>
