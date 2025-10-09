<?php

    require_once __DIR__ . '/include/autoload.php';
    require_once __DIR__ . '/include/include.inc.php';

    $page = $_GET['page'] ?? 'home';

    $controllerMap = [
        'home' => 'HomePageController',
        'register' => 'RegisterController',
        'login' => 'LoginController',
        'legalTerms' => 'LegalTermsPageController',
        'logout' => 'LogoutController',
        'forgot_password' => 'ForgotPasswordController',
        'verify_token' => 'VerifyTokenController',
        'reset_password' => 'ResetPasswordController'
    ];

    if (isset($controllerMap[$page])) {
        $controllerName = $controllerMap[$page];
        new $controllerName();
    } else {
        echo 'Page non trouvé';
    }
