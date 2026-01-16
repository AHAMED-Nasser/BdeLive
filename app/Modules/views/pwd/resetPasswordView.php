<?php
/**
 * @var \App\Core\Security\CsrfProtection $csrf
 * @var array<string, mixed>|null $user
 * @var array<string, string|null> $flash
 */
start_page("Réinitialiser le mot de passe - BDELive ", true, $user ?? null);
?>

<div class="forgot-container">
    <h1 class="title">Nouveau mot de passe</h1>

    <?php if (!empty($flash['error'])) : ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($flash['error']) ?>
        </div>
    <?php endif; ?>

    <form action="index.php?page=reset_password" method="POST">
        <?= $csrf->getTokenField() ?>

        <label for="password">Nouveau mot de passe :</label><br>
        <input id="password" type="password" name="password" placeholder="Entrez votre nouveau mot de passe" required
            minlength="6"><br><br>

        <label for="confirm_password">Confirmer le mot de passe :</label><br>
        <input id="confirm_password" type="password" name="confirm_password" placeholder="Confirmez votre mot de passe"
            required minlength="6"><br>


        <button type="submit" name="submit">Réinitialiser le mot de passe</button>
    </form>

    <a href="index.php?page=login"> <--- Retour page de connexion</a>
</div>

<?php
end_page();
?>
