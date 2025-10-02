<?php

    $page = $_GET['page'] ?? 'home';

    switch ($page) {
        case 'home':
            require_once __DIR__ . '/include/include.inc.php';
            require_once __DIR__ . '/modules/controllers/HomePageController.php';
            $controller = new HomePageController();
            $controller->index();
            break;
    }
?>