<?php
/**
 * Profile Page View
 *
 * Displays user profile information with inline editing capabilities.
 * Users can modify their first name, last name, and status.
 * Email and password fields are displayed but cannot be modified on this page.
 *
 * @author BdeLive Team
 * @version 1.1.0
 * @package BdeLive\Views\Users
 *
 * @var \App\Core\Security\CsrfProtection $csrf CSRF protection service
 * @var array<string, mixed>|null $user Current user data
 * @var array<string, string|null> $flash Flash messages (success, error, etc.)
 * @var string|null $editField Field currently being edited (first_name, last_name, user_status)
 */

start_page("Mon Profil - BDELive", true, $user ?? null);

$editField = $_GET['edit'] ?? null;
?>

<div class="profile-page">
    <h1><i class="fas fa-user-circle"></i> Mon Profil</h1>

    <?php if (!empty($flash['success'])) : ?>
        <div class="profile-alert profile-alert-success">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($flash['success']) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($flash['error'])) : ?>
        <div class="profile-alert profile-alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($flash['error']) ?>
        </div>
    <?php endif; ?>

    <div class="profile-card">
        <!-- Profile Header -->
        <div class="profile-card-header">
            <div class="profile-avatar">
                <i class="fas fa-user"></i>
            </div>
            <h2>
                <?php
                $displayName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
                echo htmlspecialchars($displayName ?: 'Utilisateur');
                ?>
            </h2>
            <div class="profile-status">
                <?= htmlspecialchars($user['user_status'] ?? 'Non défini') ?>
            </div>
        </div>

        <!-- Profile Body -->
        <div class="profile-card-body">
            <!-- First Name Field -->
            <div class="profile-field">
                <?php if ($editField === 'first_name') : ?>
                    <div class="profile-field-header">
                        <span class="profile-field-label"><i class="fas fa-id-badge"></i> Prénom</span>
                    </div>
                    <form method="POST" action="index.php?page=profile&action=processFirstName" class="profile-edit-form">
                        <?= $csrf->getTokenField() ?>
                        <input type="text" name="first-name" class="profile-input"
                            value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" placeholder="Saisissez votre prénom"
                            required autofocus>
                        <div class="profile-form-actions">
                            <button type="submit" class="profile-btn-save">
                                <i class="fas fa-check"></i> Valider
                            </button>
                            <a href="index.php?page=profile" class="profile-btn-cancel">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                        </div>
                    </form>
                <?php else : ?>
                    <div class="profile-field-header">
                        <span class="profile-field-label"><i class="fas fa-id-badge"></i> Prénom</span>
                        <a href="index.php?page=profile&edit=first_name" class="profile-edit-btn">
                            <i class="fas fa-pencil-alt"></i> Modifier
                        </a>
                    </div>
                    <div class="profile-field-value">
                        <?= htmlspecialchars($user['first_name'] ?? 'Non renseigné') ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Last Name Field -->
            <div class="profile-field">
                <?php if ($editField === 'last_name') : ?>
                    <div class="profile-field-header">
                        <span class="profile-field-label"><i class="fas fa-id-badge"></i> Nom</span>
                    </div>
                    <form method="POST" action="index.php?page=profile&action=processLastName" class="profile-edit-form">
                        <?= $csrf->getTokenField() ?>
                        <input type="text" name="last-name" class="profile-input"
                            value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" placeholder="Saisissez votre nom"
                            required autofocus>
                        <div class="profile-form-actions">
                            <button type="submit" class="profile-btn-save">
                                <i class="fas fa-check"></i> Valider
                            </button>
                            <a href="index.php?page=profile" class="profile-btn-cancel">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                        </div>
                    </form>
                <?php else : ?>
                    <div class="profile-field-header">
                        <span class="profile-field-label"><i class="fas fa-id-badge"></i> Nom</span>
                        <a href="index.php?page=profile&edit=last_name" class="profile-edit-btn">
                            <i class="fas fa-pencil-alt"></i> Modifier
                        </a>
                    </div>
                    <div class="profile-field-value">
                        <?= htmlspecialchars($user['last_name'] ?? 'Non renseigné') ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- User Status Field -->
            <?php if (!isset($user['user_status']) || $user['user_status'] !== 'BDE') : ?>
                <div class="profile-field">
                    <?php if ($editField === 'user_status') : ?>
                        <div class="profile-field-header">
                            <span class="profile-field-label"><i class="fas fa-graduation-cap"></i> Statut</span>
                        </div>
                        <form method="POST" action="index.php?page=profile&action=processUserStatus" class="profile-edit-form">
                            <?= $csrf->getTokenField() ?>
                            <select name="user_status" class="profile-select" required autofocus>
                                <option value="">-- Sélectionnez --</option>
                                <option value="BUT 1" <?= ($user['user_status'] ?? '') === 'BUT 1' ? 'selected' : '' ?>>BUT 1
                                </option>
                                <option value="BUT 2" <?= ($user['user_status'] ?? '') === 'BUT 2' ? 'selected' : '' ?>>BUT 2
                                </option>
                                <option value="BUT 3" <?= ($user['user_status'] ?? '') === 'BUT 3' ? 'selected' : '' ?>>BUT 3
                                </option>
                                <option value="Personnel Enseignant" <?= ($user['user_status'] ?? '') === 'Personnel Enseignant' ? 'selected' : '' ?>>Personnel Enseignant</option>
                            </select>
                            <div class="profile-form-actions">
                                <button type="submit" class="profile-btn-save">
                                    <i class="fas fa-check"></i> Valider
                                </button>
                                <a href="index.php?page=profile" class="profile-btn-cancel">
                                    <i class="fas fa-times"></i> Annuler
                                </a>
                            </div>
                        </form>
                    <?php else : ?>
                        <div class="profile-field-header">
                            <span class="profile-field-label"><i class="fas fa-graduation-cap"></i> Statut</span>
                            <a href="index.php?page=profile&edit=user_status" class="profile-edit-btn">
                                <i class="fas fa-pencil-alt"></i> Modifier
                            </a>
                        </div>
                        <div class="profile-field-value">
                            <?= htmlspecialchars($user['user_status'] ?? 'Non défini') ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else : ?>
                <div class="profile-field">
                    <div class="profile-field-header">
                        <span class="profile-field-label"><i class="fas fa-graduation-cap"></i> Statut</span>
                    </div>
                    <div class="profile-field-value">
                        <span class="profile-field-readonly"><?= htmlspecialchars($user['user_status']) ?></span>
                    </div>
                    <div class="profile-field-readonly-notice">
                        <i class="fas fa-lock"></i> Le statut BDE ne peut pas être modifié
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <a href="index.php?page=home" class="profile-back-link">
        <i class="fas fa-arrow-left"></i> Retour à l'accueil
    </a>
</div>

<?php end_page(); ?>
