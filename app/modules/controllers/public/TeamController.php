<?php


class TeamController{
    public function __construct(){
        $this->loadView('teamView');

    }

    private function loadView(string $viewName): void {
        require_once __DIR__ . '/../../views/public/' . $viewName . '.php';
    }
}
?>

