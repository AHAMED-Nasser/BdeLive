<?php
// On inclut les fichiers nécessaires pour les autres pages
require_once __DIR__ . '/include/include.inc.php';
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
    'profile' => 'ProfileController',

];

if (isset($controllerMap[$page])) {
    $controllerName = $controllerMap[$page];
    $controller = new $controllerName();
//    if ($controllerName === 'SitemapController') {
//        $controller->showHtmlPage();
//    }
} else {
    http_response_code(404);
    echo 'Page non trouvée';
}

