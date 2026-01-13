<?php

/**
 * Sitemap View
 *
 * Displays the sitemap with links to all major sections of the website.
 * Helps with navigation and SEO.
 *
 * @package BdeLive\Views\Public
 * @version 1.0.0
 * @author BdeLive Team
 */

start_page("Plan du site - BDELive", true, $user ?? null);
?>
<div class="legal-terms-page">
    <h1 class="title">Plan du site</h1>

    <section>
        <h2>Navigation principale</h2>
        <ul>
            <li><a href="index.php?page=home">Accueil</a></li>
            <li><a href="index.php?page=articles">Nos articles</a></li>
            <li><a href="index.php?page=event">Événements</a></li>
            <li><a href="index.php?page=team">Équipe</a></li>
        </ul>
    </section>

    <?php if (isset($user) && $user !== null): ?>
        <section>
            <h2>Pages réservées aux membres</h2>
            <ul>
                <li><a href="index.php?page=schedule">Emploi du temps</a></li>
            </ul>
        </section>
    <?php endif; ?>

    <?php if (isset($user) && $user !== null && isset($user['user_status']) && $user['user_status'] === 'BDE'): ?>
        <section>
            <h2>Administration (BDE)</h2>
            <ul>
                <li><a href="index.php?page=createEvent">Créer un événement</a></li>
                <li><a href="index.php?page=createArticle">Créer un article</a></li>
            </ul>
        </section>
    <?php endif; ?>

    <section>
        <h2>Gestion de compte</h2>
        <ul>
            <?php if (isset($user) && $user !== null): ?>
                <li><a href="index.php?page=profile">Mon Profil</a></li>
                <li><a href="index.php?page=privacy">Confidentialité</a></li>
                <li><a href="index.php?page=logout">Déconnexion</a></li>
                <?php if (!isset($user['user_status']) || $user['user_status'] !== 'BDE'): ?>
                    <li><a href="index.php?page=deleteAccount">Supprimer mon compte</a></li>
                <?php endif; ?>
            <?php else: ?>
                <li><a href="index.php?page=login">Connexion</a></li>
                <li><a href="index.php?page=register">Inscription</a></li>
                <li><a href="index.php?page=forgot_password">Mot de passe oublié</a></li>
            <?php endif; ?>
        </ul>
    </section>

    <section>
        <h2>À propos</h2>
        <ul>
            <li><a href="index.php?page=about">À propos</a></li>
            <li><a href="index.php?page=history">Notre histoire</a></li>
        </ul>
    </section>

    <section>
        <h2>Informations légales</h2>
        <ul>
            <li><a href="index.php?page=sitemap">Plan du site</a></li>
            <li><a href="index.php?page=legalTerms">Mentions légales</a></li>
        </ul>
    </section>

    <p><a href="index.php?page=home">← Retour à l'accueil</a></p>
</div>

<?php
end_page();
?>