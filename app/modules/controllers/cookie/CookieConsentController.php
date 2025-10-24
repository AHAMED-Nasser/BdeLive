<?php
// modules/controllers/CookieConsentController.php

class CookieConsentController
{
    private $cookieName = 'cookie_consent';
    private $cookieDays = 365;

    public function __construct()
    {
        $this->handlePost();
        $this->render();
    }

    private function handlePost()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cookie_consent'])) {
            $value = $_POST['cookie_consent'] === 'accept' ? 'yes' : 'no';
            setcookie($this->cookieName, $value, time() + $this->cookieDays * 24 * 60 * 60, '/');
            $_COOKIE[$this->cookieName] = $value;
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
            exit;
        }
    }

    private function render()
    {
        $consent = $_COOKIE[$this->cookieName] ?? null;
        $showPopup = ($consent !== 'yes');

        if ($showPopup) {
            include __DIR__ . '/../../views/shared/cookie_popup.php';
        }
    }
}
