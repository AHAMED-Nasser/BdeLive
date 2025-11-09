<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Cookie;

use App\Modules\Controllers\DefaultController;

/**
 * CookieConsentController - Cookie Consent Management
 *
 * Handles cookie consent preferences (accept/reject).
 * Sets a cookie to remember user's choice.
 *
 * @package BdeLive\Controllers\Cookie
 * @version 1.0.0
 * @author BdeLive Team
 * 
 * @see DefaultController For base functionality
 */
class CookieConsentController extends DefaultController
{
    private string $cookieName = 'cookie_consent';
    private int $cookieDays = 365;

    public function __construct()
    {
        parent::__construct();
        
        $this->handlePost();
        $this->renderCookiePopup();
    }

    private function handlePost(): void
    {
        if ($this->request->isPost() && $this->request->post('cookie_consent') !== null) {
            $consentValue = $this->request->post('cookie_consent', '');
            $value = $consentValue === 'accept' ? 'yes' : 'no';

            // Secure cookie settings (same as session cookies)
            $isProduction = strpos($this->request->server('HTTP_HOST', ''), 'alwaysdata.net') !== false;

            setcookie(
                $this->cookieName,
                $value,
                [
                    'expires' => time() + $this->cookieDays * 24 * 60 * 60,
                    'path' => '/',
                    'domain' => $isProduction ? 'bdelivesae.alwaysdata.net' : '',
                    'secure' => $isProduction,  // HTTPS only in production
                    'httponly' => true,         // Inaccessible by JavaScript
                    'samesite' => 'Lax'         // CSRF protection
                ]
            );

            // Redirect to referer or home
            $referer = $this->request->server('HTTP_REFERER', 'index.php?page=home');
            $this->redirect($referer);
        }
    }

    private function renderCookiePopup(): void
    {
        $consent = $this->request->cookie($this->cookieName);
        $showPopup = ($consent !== 'yes');

        if ($showPopup) {
            include __DIR__ . '/../../views/shared/cookie_popup.php';
        }
    }
}
