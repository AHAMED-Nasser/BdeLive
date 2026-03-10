<?php

/**
 * Cookie Consent Popup
 *
 * Displays the GDPR-compliant cookie consent popup for user privacy compliance.
 *
 * @package BdeLive\Views\Shared
 * @version 1.0.0
 * @author BdeLive - Group 8
 *
 * @var Application $app
 */

use App\Core\Application;

$app = Application::getInstance();

?>

<!-- CSS link -->
<link rel="stylesheet" href="./assets/css/pages/cookie_popup.css">

<div id="cookieConsent" role="dialog" aria-live="polite">
    <div class="text">
        <h4>Nous utilisons des cookies</h4>
        <p>Nous utilisons des cookies pour améliorer votre expérience, analyser le trafic et personnaliser le contenu
        </p>
    </div>
    <div class="actions">
        <button id="acceptBtn" class="btn">Accepter</button>
        <button id="declineBtn" class="btn secondary">Refuser</button>
    </div>

    <noscript>
        <form method="post">
            <?= $app->csrf()->getTokenField() ?>
            <input type="hidden" name="cookie_consent" value="accept">
            <button type="submit" class="btn">Accepter</button>
        </form>
    </noscript>
</div>

<!-- JS link -->
<script src="./assets/js/cookie_popup.js"></script>
