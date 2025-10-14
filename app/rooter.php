<?php
// On inclut les fichiers nécessaires une seule fois au début
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
    'sitemap' => 'SitemapController'
];

if (isset($controllerMap[$page])) {
    $controllerName = $controllerMap[$page];
    $controller = new $controllerName();
    // Cas spécial pour le sitemap qui a deux formats (HTML ou XML)
    if ($controllerName === 'SitemapController') {
        // Si le format demandé est 'xml', on génère le XML
        if (isset($_GET['format']) && $_GET['format'] === 'xml') {
            $controller->generateXml();
        } else {
            // Sinon, on affiche la page HTML normale
            $controller->showHtmlPage();
        }
    }


} else {
    // Page non trouvée
    http_response_code(404);
    echo 'Page non trouvée';
}

