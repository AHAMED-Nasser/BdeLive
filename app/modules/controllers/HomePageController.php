<?php
class HomePageController {

    public function index(): void {
        $this->loadView('homePageView');
    }

    private function loadView(string $viewName): void {
        require_once __DIR__ . '/../views/' . $viewName . '.php';
    }
}
?>
