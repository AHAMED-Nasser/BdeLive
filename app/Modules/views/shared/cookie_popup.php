<?php

/**
 * Fichier : views/shared/cookie_popup.php
 * Description : Popup cookies
 *
 * @var \App\Core\Application $app
 */

use App\Core\Application;

$app = Application::getInstance();

?>

<!-- Lien CSS -->
<link rel="stylesheet" href="../../../assets/css/cookie_popup.css">

<div id="cookieConsent" role="dialog" aria-live="polite">
    <div class="text">
        <h4>Nous utilisons des cookies</h4>
        <p>Nous utilisons des cookies pour améliorer votre expérience, analyser le trafic et personnaliser le contenu</p>
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

<!-- Lien JS -->
<script src="../../../assets/js/cookie_popup.js"></script>
