<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Cookie;

use App\Modules\Controllers\DefaultController;

/**
 * Controller responsible for managing cookie consent.
 * Handles user choices (accept/reject) and sets the appropriate cookie.
 *
 * @author BDELIVE - Groupe 8
 * @package App\Modules\Controllers\Cookie
 * @version 1.0.0
 * @see DefaultController For base functionality
 */
class CookieConsentController extends DefaultController
{
    private string $cookieName = 'cookie_consent';
    private int $cookieDays = 365;

    /**
     * Initializes the controller.
     * Processes post requests and manages the display of the cookie popup.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->handlePost();
        $this->renderCookiePopup();
    }

    /**
     * Handles the form submission for cookie consent.
     * Sets the cookie with security parameters (Secure, HttpOnly, SameSite).
     *
     * @return void
     */
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

    /**
     * Checks if the consent cookie exists and renders the popup if necessary.
     *
     * @return void
     */
    private function renderCookiePopup(): void
    {
        $consent = $this->request->cookie($this->cookieName);
        $showPopup = ($consent !== 'yes');

        if ($showPopup) {
            include __DIR__ . '/../../views/shared/cookie_popup.php';
        }
    }
}
