<?php
/**
 * @var \App\Core\Security\CsrfProtection $csrf
 * @var array<string, mixed>|null $user
 * @var array<string, string|null> $flash
 */
start_page("Vérification de l'email - BDELive", true, $user ?? null);
?>

<div class="forgot-container">
    <h1 class="title">Vérification de l'email</h1>

    <?php if (!empty($flash['error'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($flash['error']) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($flash['success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($flash['success']) ?>
        </div>
    <?php endif; ?>

    <div style="text-align: center; margin-top: 20px;">
        <a href="index.php?page=login" class="btn">Se connecter</a>
        <a href="index.php?page=home" class="btn">Retour à l'accueil</a>
    </div>
</div>

<?php
end_page();
?>