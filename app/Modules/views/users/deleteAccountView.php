<?php
/**
 * Delete Account View
 *
 * Displays the account deletion confirmation page.
 * Allows users to permanently delete their account with CSRF protection.
 * Requires manual email confirmation (no copy-paste allowed).
 *
 * @author BdeLive - Group 8
 * @version 2.0.0
 * @package BdeLive\Views\Users
 *
 * @var \App\Core\Security\CsrfProtection $csrf CSRF protection service
 * @var array<string, mixed>|null $user Current user data
 * @var array<string, string|null> $flash Flash messages (success, error, etc.)
 * @var bool $isAdminBlocked True if user is admin/super_admin and cannot delete account
 */

start_page('Supprimer mon compte - BDELive', true, $user ?? null);
?>

<link rel="stylesheet" href="./assets/css/pages/delete-account.css">

<div class="delete-account-container">
    <div class="delete-account-card">
        <div class="delete-account-header">
            <i class="fas fa-exclamation-triangle delete-account-icon"></i>
            <h1>Supprimer mon compte</h1>
        </div>

        <!-- Error Message (masqué pour les admins bloqués, le message est déjà affiché dans le bloc dédié) -->
        <?php
        $isAdminBlockedEarly = $isAdminBlocked ?? false;
        $earlyRole = $user['role'] ?? 'user';
        $isAdminRole = in_array($earlyRole, ['admin', 'super_admin'], true);
        if (!empty($flash['error']) && !$isAdminBlockedEarly && !$isAdminRole) :
            ?>
            <div class="alert alert-danger"><?= htmlspecialchars($flash['error']) ?></div>
        <?php endif; ?>

        <!-- Success Message -->
        <?php if (!empty($flash['success'])) : ?>
            <div class="alert alert-success"><?= htmlspecialchars($flash['success']) ?></div>
        <?php endif; ?>

        <?php
        $isAdminBlocked = $isAdminBlocked ?? false;
        $userRole = $user['role'] ?? 'user';
        if ($isAdminBlocked || in_array($userRole, ['admin', 'super_admin'], true)) :
            $roleLabel = ($userRole === 'super_admin') ? 'super administrateur' : 'administrateur';
            ?>
            <div class="delete-account-warning">
                <p>
                    <i class="fas fa-shield-alt"></i>
                    <strong>Suppression impossible</strong>
                </p>
                <p>
                    Les comptes <strong><?= htmlspecialchars($roleLabel) ?></strong> ne peuvent pas être supprimés
                    depuis cette page. La suppression d'un compte avec des droits élevés doit être réalisée
                    par un autre administrateur depuis le panneau d'administration.
                </p>
            </div>
            <div class="form-actions" style="margin-top: 24px;">
                <a href="index.php?page=home" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour à l'accueil
                </a>
            </div>
        <?php else : ?>
        <div class="delete-account-warning">
            <p><strong>Attention :</strong> Cette action est <strong>irréversible</strong>.</p>
            <p>Toutes vos données seront définitivement supprimées :</p>
            <ul>
                <li>Votre profil et vos informations personnelles</li>
                <li>Vos inscriptions aux événements</li>
                <li>Vos groupes et équipes</li>
                <li>Toutes vos autres données associées</li>
            </ul>
        </div>

        <div class="delete-account-info">
            <p>Pour confirmer la suppression, veuillez <strong>taper manuellement</strong> votre adresse email actuelle
                :</p>
            <p class="user-email-hint"><strong><?= htmlspecialchars($user['email'] ?? '') ?></strong></p>
        </div>

        <form method="post" action="index.php?page=delete_account" id="deleteAccountForm" class="delete-account-form">
            <?= $csrf->getTokenField() ?>

            <div class="form-group">
                <label for="confirm-email" class="form-label">
                    <i class="fas fa-envelope"></i> Confirmez votre email
                </label>
                <input type="email" id="confirm-email" name="confirm_email" class="form-input email-confirm-input"
                    placeholder="Tapez votre email ici..." autocomplete="off" required>
                <div class="form-error" id="email-error"></div>
                <div class="form-hint">
                    <i class="fas fa-info-circle"></i> Vous devez taper votre email manuellement (copier-coller
                    désactivé)
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-danger btn-delete" id="deleteBtn" disabled>
                    <i class="fas fa-trash-alt"></i> Supprimer définitivement mon compte
                </button>
                <a href="index.php?page=home" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Annuler
                </a>
            </div>
        </form>

        <?php endif; ?>
    </div>
</div>

<?php if (!$isAdminBlocked && !in_array($user['role'] ?? 'user', ['admin', 'super_admin'], true)) : ?>
<script src="./assets/js/delete-account.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof initDeleteAccount !== 'undefined') {
            initDeleteAccount('<?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES) ?>');
        }
    });
</script>
<?php endif; ?>

<?php end_page(); ?>
