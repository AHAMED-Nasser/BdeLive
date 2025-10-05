<?php
    require_once __DIR__ . '/autoload.php';
    require_once __DIR__ . '/include/include.inc.php';

    $page = $_GET['page'] ?? 'home';

    // Handle logout separately with AuthController
    if ($page === 'logout') {
        require_once __DIR__ . '/include/AuthController.php';
        $authController = new AuthController();
        $authController->logout();
        exit;
    }

    $controllerMap = [
        'home' => 'HomePageController',
        'login' => 'LoginController',
        'register' => 'RegisterController',
        'legalTerms' => 'LegalTermsPageController',
        'forgotPassword' => 'ForgotPasswordController',
    ];

    if (isset($controllerMap[$page])) {
        $controllerName = $controllerMap[$page];
        new $controllerName();
    } else {
        echo "Page not found.";
    }