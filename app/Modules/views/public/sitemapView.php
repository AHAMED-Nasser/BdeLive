<?php
start_page("Plan du site - BDE Live", true, $user ?? null);
?>
    <div class="legal-terms-page">
        <h1 class="title">Plan du site</h1>

        <section>
            <h2>Navigation</h2>
            <ul>
                <li><a href="index.php?page=home">Accueil</a></li>
            </ul>
        </section>

        <section>
            <h2>Information</h2>
            <ul>
                <li><a href="index.php?page=sitemap">Plan du site</a></li>
                <li><a href="index.php?page=legalTerms">Mentions Légales</a></li>
            </ul>
        </section>

        <section>
            <h2>Gestion de Compte</h2>
            <ul>
                <?php if (session_status() === PHP_SESSION_NONE) {
                } ?>
                <?php if (isset($user) && $user !== null) : ?>
                    <li><a href="index.php?page=logout">Déconnexion</a></li>
                    <li><a href="index.php?page=profile">Mon Profil</a></li>
                <?php else : ?>
                    <li><a href="index.php?page=login">Connexion</a></li>
                    <li><a href="index.php?page=register">Inscription</a></li>
                    
                <?php endif; ?>
                <li><a href="index.php?page=forgot_password">Mot de passe oublié</a></li>
            </ul>
        </section>

        <section>
            <h2>Événements & Vie Étudiante</h2>
            <ul>
                <li><a href="index.php?page=event">Tous les événements</a></li>
                <li><a href="index.php?page=createEvent">Créer un événement (Admin)</a></li>
            </ul>
        </section>
    
        <p><a href="index.php?page=home">← Retour à l'accueil</a></p>
    </div>

<?php
end_page();
?>
