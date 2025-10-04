<?php
    require_once __DIR__ . '/autoload.php';
    require_once __DIR__ . '/include/include.inc.php';

    $page = $_GET['page'] ?? 'home';

    $controllerMap = [
        'home' => 'HomePageController',
        'login' => 'LoginController',
        'register' => 'RegisterController',
        'legalTerms' => 'LegalTermsPageController',

    ];

    if (isset($controllerMap[$page])) {
        $controllerName = $controllerMap[$page];
        new $controllerName();
    } else {
        echo "Page not found.";
    }