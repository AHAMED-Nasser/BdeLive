<?php
require_once __DIR__. '/../../include/include.inc.php';
start_page("Plan du site - BDE Live");
?>
    <div class="legal-terms-page">
        <h1>Plan du site</h1>

    <section>
        <h2>Navigation principale</h2>
        <ul>
            <li><a href="index.php?page=home">Accueil</a></li>
            <li><a href="index.php?page=login">Connexion</a></li>
            <li><a href="index.php?page=register">Inscription</a></li>
            <li><a href="index.php?page=legalTerms">Mentions légales</a></li>
        </ul>
    </section>

    <section>
        <h2>Fonctionnalités</h2>
        <ul>
            <li><a href="index.php?page=forgot_password">Mot de passe oublié</a></li>
        </ul>
    </section>

    <?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
    <?php if (isset($_SESSION['utilisateur_id'])): ?>
    <section>
        <h2>Espace utilisateur</h2>
        <ul>
            <li><a href="index.php?page=logout">Déconnexion</a></li>
        </ul>
    </section>
    <?php endif; ?>
    
        <p><a href="index.php?page=home">← Retour à l'accueil</a></p>
    </div>

<?php
end_page();
?>
