<?php
class HomePageController {

    public function __construct() {
        $this->loadView('homePageView');
    }

    private function loadView(string $viewName): void {
        require_once __DIR__ . '/../views/' . $viewName . '.php';
    }
}
?>
