<?php
/**
 * @var \App\Core\Security\CsrfProtection $csrf
 * @var array<string, mixed>|null $user
 * @var array<string, string|null> $flash
 */
start_page('Supprimer mon compte', true, $user ?? null);
if (session_status() === PHP_SESSION_NONE) {
}

require_once __DIR__ . '/../shared/include.inc.php';


?>

    <div class="container" style="max-width:600px;margin:60px auto;">
        <h2>Supprimer mon compte</h2>

        <!-- Message Erreur -->
        <?php if (!empty($flash['error'])) : ?>
            <div class="alert alert-danger"><?= htmlspecialchars($flash['error']) ?></div>
        <?php endif; ?>

        <!-- Message Réussie-->
        <?php if (!empty($flash['success'])) : ?>
            <div class="alert alert-success"><?= htmlspecialchars($flash['success']) ?></div>
        <?php endif; ?>

        <p>Attention : cette action est irréversible. Toutes vos données seront supprimées.</p>

            <form method="post" action="index.php?page=delete_account" onsubmit="return confirm('Voulez-vous vraiment supprimer votre compte ?');">
                <?= $csrf->getTokenField() ?>

            <!-- Boutons -->
            <button type="submit" class="btn btn-danger">Supprimer mon compte</button>
            <a href="index.php?page=home" class="btn btn-secondary">Annuler</a>
        </form>
    </div>

<?php end_page(); ?>
