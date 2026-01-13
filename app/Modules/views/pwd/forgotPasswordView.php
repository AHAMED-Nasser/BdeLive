<?php
/**
 * @var \App\Core\Security\CsrfProtection $csrf
 * @var array<string, mixed>|null $user
 * @var array<string, string|null> $flash
 */
start_page("Mot de passe oublié - BDELive", true, $user ?? null);
?>

<div class="forgot-container">
    <h1 class="title">Mot de passe oublié</h1>

    <?php if (!empty($flash['error'])) : ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($flash['error']) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($flash['success'])) : ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($flash['success']) ?>
        </div>
    <?php endif; ?>

    <form action="index.php?page=forgot_password" method="POST">
        <label for="email">Adresse e-mail :</label><br>
        <input id="email" type="email" name="email" placeholder="Entrez votre email" required><br>
        <?= $csrf->getTokenField() ?>
        <button type="submit" name="submit">Envoyer le code</button>
    </form>

    <a href="index.php?page=login"> <--- Retour page de connexion</a>
</div>

<?php end_page() ?>