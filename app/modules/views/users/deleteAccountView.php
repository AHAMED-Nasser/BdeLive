<?php
start_page('Supprimer mon compte', true);
if (session_status() === PHP_SESSION_NONE) {
}

require_once __DIR__ . '/../shared/include.inc.php';


?>

    <div class="container" style="max-width:600px;margin:60px auto;">
        <h2>Supprimer mon compte</h2>

        <!-- Message Erreur -->
        <?php if (! empty($_SESSION['error'])) : ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']);
            unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <!-- Message Réussie-->
        <?php if (! empty($_SESSION['success'])) : ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']);
            unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <p>Attention : cette action est irréversible. Toutes vos données seront supprimées.</p>

            <form method="post" action="index.php?page=delete_account" onsubmit="return confirm('Voulez-vous vraiment supprimer votre compte ?');">
                <?= csrfField() ?>

            <!-- Boutons -->
            <button type="submit" class="btn btn-danger">Supprimer mon compte</button>
            <a href="index.php?page=home" class="btn btn-secondary">Annuler</a>
        </form>
    </div>

<?php
end_page();
?>
<?php
