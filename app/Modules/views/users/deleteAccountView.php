<?php
/**
 * Delete Account View
 *
 * Displays the account deletion confirmation page.
 * Allows users to permanently delete their account with CSRF protection.
 *
 * @author BdeLive Team
 * @version 1.1.0
 * @package BdeLive\Views\Users
 *
 * @var \App\Core\Security\CsrfProtection $csrf CSRF protection service
 * @var array<string, mixed>|null $user Current user data
 * @var array<string, string|null> $flash Flash messages (success, error, etc.)
 */

start_page('Supprimer mon compte', true, $user ?? null);
?>

<div class="container" style="max-width:600px;margin:60px auto;">
    <h2>Supprimer mon compte</h2>

    <!-- Error Message -->
    <?php if (!empty($flash['error'])) : ?>
        <div class="alert alert-danger"><?= htmlspecialchars($flash['error']) ?></div>
    <?php endif; ?>

    <!-- Success Message -->
    <?php if (!empty($flash['success'])) : ?>
        <div class="alert alert-success"><?= htmlspecialchars($flash['success']) ?></div>
    <?php endif; ?>

    <p>Attention : cette action est irréversible. Toutes vos données seront supprimées.</p>

    <form method="post" action="index.php?page=delete_account">
        <?= $csrf->getTokenField() ?>

        <!-- Buttons -->
        <button type="submit" class="btn btn-danger">Supprimer mon compte</button>
        <a href="index.php?page=home" class="btn btn-secondary">Annuler</a>
    </form>
</div>

<?php end_page(); ?>
