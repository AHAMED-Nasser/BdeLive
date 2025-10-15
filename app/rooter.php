<?php

    require_once __DIR__ . '/include/include.inc.php';
    require_once __DIR__ . '/include/carousel.inc.php';
    require_once __DIR__ . '/include/autoload.php';

    $page = $_GET['page'] ?? 'home';

    $controllerMap = [
        'home' => 'HomePageController',
        'register' => 'RegisterController',
        'login' => 'LoginController',
        'legalTerms' => 'LegalTermsPageController',
        'logout' => 'LogoutController',
        'forgot_password' => 'ForgotPasswordController',
        'verify_token' => 'VerifyTokenController',
        'reset_password' => 'ResetPasswordController',
        'sitemap' => 'SitemapController',
        'event' => 'EventController'
    ];

    if (isset($controllerMap[$page])){
        $controllerName = $controllerMap[$page];
        new $controllerName();
    } else {
        echo 'Page non trouvé';
    }

