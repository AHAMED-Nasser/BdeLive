<?php

    $page = $_GET['page'] ?? 'home';

    switch ($page) {
        case 'home':
            require_once __DIR__ . '/include/include.inc.php';
            require_once __DIR__ . '/modules/controllers/HomePageController.php';
            $controller = new HomePageController();
            $controller->index();
            break;
            
        case 'register':
            require_once __DIR__ . '/include/include.inc.php';
            require_once __DIR__ . '/modules/controllers/RegisterController.php';
            $controller = new RegisterController();
            $controller->index();
            break;
            
        case 'login':
            require_once __DIR__ . '/include/include.inc.php';
            require_once __DIR__ . '/modules/controllers/LoginController.php';
            $controller = new LoginController();
            $controller->index();
            break;
            
        case 'legalTerms':
            require_once __DIR__ . '/include/include.inc.php';
            require_once __DIR__ . '/modules/controllers/LegalTermsPageController.php';
            $controller = new LegalTermsPageController();
            $controller->index();
            break;
            
        case 'logout':
            require_once __DIR__ . '/modules/controllers/LogoutController.php';
            $controller = new LogoutController();
            $controller->index();
            break;
            
        case 'forgot_password':
            require_once __DIR__ . '/include/include.inc.php';
            require_once __DIR__ . '/modules/controllers/ForgotPasswordController.php';
            $controller = new ForgotPasswordController();
            $controller->index();
            break;
            
        case 'verify_token':
            require_once __DIR__ . '/include/include.inc.php';
            require_once __DIR__ . '/modules/controllers/VerifyTokenController.php';
            $controller = new VerifyTokenController();
            $controller->index();
            break;
            
        case 'reset_password':
            require_once __DIR__ . '/include/include.inc.php';
            require_once __DIR__ . '/modules/controllers/ResetPasswordController.php';
            $controller = new ResetPasswordController();
            $controller->index();
            break;
        
        case 'sitemap':
            require_once __DIR__ . '/include/include.inc.php';
            require_once __DIR__ . '/modules/controllers/SitemapController.php';
            $controller = new SitemapController();
            $controller->index();
            break;
            
        default:
            header('Location: index.php?page=home');
            exit;
    }
?>
