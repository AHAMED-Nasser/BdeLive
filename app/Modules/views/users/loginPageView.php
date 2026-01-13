<?php
/**
 * @var \App\Core\Security\CsrfProtection $csrf
 * @var array<string, mixed>|null $user
 * @var array<string, string|null> $flash
 */
start_page("Connexion - BDELive", true, $user ?? null);
?>

<div class="forgot-container">
    <h1 class="title">Connexion</h1>

    <?php if (!empty($flash['success'])) : ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($flash['success']) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($flash['error'])) : ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($flash['error']) ?>
        </div>
    <?php endif; ?>

    <form id="form" action="index.php?page=login" method="POST">
        <label for="email">Adresse e-mail :</label>
        <input id="email" type="email" name="email" placeholder="Entrez votre adresse mail" required>

        <label for="password">Mot de passe :</label>
        <input id="password" type="password" name="password" placeholder="Entrez votre mot de passe" required>

        <?= $csrf->getTokenField() ?>
        <button type="submit" name="ok">Se connecter</button>
    </form>

    <a href="index.php?page=home">← Retour à l'accueil</a>
    <a href="index.php?page=forgot_password">Mot de passe oublié ?</a>
    <a href="index.php?page=register">Pas de compte ? Inscrivez-vous</a>
</div>

<?php end_page(); ?>