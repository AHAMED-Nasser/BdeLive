<?php
/**
 * @var \App\Core\Security\CsrfProtection $csrf
 * @var array<string, mixed>|null $user
 * @var array<string, string|null> $flash
 */
start_page("Vérification du code - BDELive", true, $user ?? null);
?>

<div class="forgot-container">
    <h1 class="title">Vérification du code</h1>

    <?php if (!empty($flash['error'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($flash['error']) ?>
        </div>
    <?php endif; ?>

    <p>Un code de vérification a été envoyé à votre adresse email. Veuillez le saisir ci-dessous :</p>

    <form action="index.php?page=verify_token" method="POST">
        <label for="token">Code de vérification :</label><br>
        <input id="token" type="text" name="token" placeholder="Entrez le code reçu par email" required
            maxlength="64"><br>
        <?= $csrf->getTokenField() ?>
        <button type="submit" name="submit">Vérifier le code</button>
    </form>

    <a href="index.php?page=login"> <--- Retour page de connexion</a>
</div>

<?php
end_page();
?>