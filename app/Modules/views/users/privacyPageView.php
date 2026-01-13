<?php
/**
 * Privacy Settings Page View
 *
 * Displays user privacy settings with email and password modification forms.
 * Implements security features including:
 * - Password verification and email code verification for email changes
 * - 6-digit code verification for password changes
 * - Account blocking display when security measures are active
 * - Real-time countdown for code resend cooldown
 *
 * @author BdeLive Team
 * @version 1.2.0
 * @package BdeLive\Views\Users
 *
 * @var \App\Core\Security\CsrfProtection $csrf CSRF protection service
 * @var array<string, mixed>|null $user Current user data
 * @var array<string, string|null> $flash Flash messages (success, error, etc.)
 * @var bool|null $isBlocked Whether account is blocked for privacy modifications
 * @var int|null $remainingTime Remaining block time in minutes
 * @var array<string, mixed>|null $passwordResendStatus Password resend code status
 * @var array<string, mixed>|null $emailResendStatus Email resend code status
 * @var bool|null $hasActivePasswordCode Whether there's an active password verification code
 * @var bool|null $hasActiveEmailCode Whether there's an active email verification code
 * @var string|null $pendingEmail Pending new email address
 * @var array{can_resend: bool, wait_seconds: int, resend_count: int} $emailResendStatus
 * @var array{can_resend: bool, wait_seconds: int, resend_count: int} $passwordResendStatus
 */

start_page("Confidentialité - BDELive", true, $user ?? null);

$editField = $_GET['edit'] ?? null;
$step = $_GET['step'] ?? null;
//$isBlocked = $isBlocked ?? false;
//$remainingTime = $remainingTime ?? 0;
//$passwordResendStatus = $passwordResendStatus ?? ['can_resend' => true, 'wait_seconds' => 0, 'resend_count' => 0];
//$emailResendStatus = $emailResendStatus ?? ['can_resend' => true, 'wait_seconds' => 0, 'resend_count' => 0];
//$hasActivePasswordCode = $hasActivePasswordCode ?? false;
//$hasActiveEmailCode = $hasActiveEmailCode ?? false;
//$pendingEmail = $pendingEmail ?? null;
?>

<div class="profile-page privacy-page">
    <h1><i class="fas fa-shield-alt"></i> Confidentialité</h1>

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

    <?php if ($isBlocked) : ?>
        <!-- Account Blocked Message -->
        <div class="profile-card">
            <div class="profile-card-header privacy-blocked-header">
                <div class="profile-avatar">
                    <i class="fas fa-lock"></i>
                </div>
                <h2>Compte temporairement bloqué</h2>
            </div>
            <div class="profile-card-body">
                <div class="privacy-blocked-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>La modification des informations de confidentialité a été bloquée sur votre compte.</p>
                    <p class="privacy-blocked-time">
                        <strong>Veuillez réessayer dans <?= $remainingTime ?> minute(s).</strong>
                    </p>
                </div>
            </div>
        </div>
    <?php else : ?>
        <div class="profile-card">
            <!-- Privacy Header -->
            <div class="profile-card-header privacy-header">
                <div class="profile-avatar">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h2>Paramètres de sécurité</h2>
                <div class="profile-status">
                    Gérez vos informations sensibles
                </div>
            </div>

            <!-- Privacy Body -->
            <div class="profile-card-body">
                <!-- Email Field -->
                <div class="profile-field">
                    <?php if ($editField === 'email' && $step === 'verify') : ?>
                        <!-- Step 2: Enter verification code sent to new email -->
                        <div class="profile-field-header">
                            <span class="profile-field-label"><i class="fas fa-envelope"></i> Vérification de la nouvelle
                                adresse</span>
                        </div>
                        <form method="POST" action="index.php?page=privacy&action=verifyEmailCode" class="profile-edit-form">
                            <?= $csrf->getTokenField() ?>

                            <?php if ($pendingEmail) : ?>
                                <div class="privacy-current-value">
                                    <span>Nouvelle adresse : </span>
                                    <strong><?= htmlspecialchars($pendingEmail) ?></strong>
                                </div>
                            <?php endif; ?>

                            <div class="privacy-code-section">
                                <label class="privacy-label">
                                    <i class="fas fa-shield-alt"></i> Code de vérification (6 chiffres)
                                </label>
                                <input type="text" name="verification_code" class="profile-input privacy-code-input"
                                    placeholder="000000" maxlength="6" pattern="[0-9]{6}" required autofocus>
                                <div class="privacy-notice">
                                    <i class="fas fa-envelope"></i>
                                    <span>Un code a été envoyé à votre nouvelle adresse email pour vérifier qu'elle
                                        existe.</span>
                                </div>
                            </div>

                            <div class="profile-form-actions">
                                <button type="submit" class="profile-btn-save">
                                    <i class="fas fa-check"></i> Confirmer le changement
                                </button>
                                <a href="index.php?page=privacy" class="profile-btn-cancel">
                                    <i class="fas fa-times"></i> Annuler
                                </a>
                            </div>
                        </form>

                        <!-- Resend Code Section -->
                        <div class="privacy-resend-section" id="email-resend-section">
                            <p>Vous n'avez pas reçu le code ?</p>
                            <?php if ($emailResendStatus['resend_count'] >= 5) : ?>
                                <div class="privacy-resend-disabled">
                                    <i class="fas fa-ban"></i>
                                    <span>Nombre maximum de renvois atteint.</span>
                                </div>
                            <?php else : ?>
                                <form method="POST" action="index.php?page=privacy&action=resendEmailCode"
                                    class="privacy-resend-form" id="email-resend-form">
                                    <?= $csrf->getTokenField() ?>
                                    <button type="submit" class="privacy-resend-btn" id="email-resend-btn"
                                        <?= !$emailResendStatus['can_resend'] ? 'disabled' : '' ?>>
                                        <i class="fas fa-redo"></i>
                                        <span id="email-resend-text">
                                            <?php if (!$emailResendStatus['can_resend']) : ?>
                                                Renvoyer dans <span
                                                    id="email-countdown"><?= $emailResendStatus['wait_seconds'] ?></span>s
                                            <?php else : ?>
                                                Renvoyer le code
                                            <?php endif; ?>
                                        </span>
                                    </button>
                                </form>
                                <?php if ($emailResendStatus['resend_count'] > 0) : ?>
                                    <p class="privacy-resend-count">
                                        Renvois restants : <?= 5 - $emailResendStatus['resend_count'] ?>/5
                                    </p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                    <?php elseif ($editField === 'email') : ?>
                        <!-- Step 1: Enter new email and password -->
                        <div class="profile-field-header">
                            <span class="profile-field-label"><i class="fas fa-envelope"></i> Modifier l'adresse email</span>
                        </div>
                        <form method="POST" action="index.php?page=privacy&action=requestEmailCode" class="profile-edit-form">
                            <?= $csrf->getTokenField() ?>
                            <div class="privacy-current-value">
                                <span>Email actuel : </span>
                                <strong><?= htmlspecialchars($user['email'] ?? 'Non renseigné') ?></strong>
                            </div>
                            <input type="email" name="new_email" class="profile-input" placeholder="Nouvelle adresse email"
                                required autofocus>
                            <div class="privacy-password-confirm">
                                <label class="privacy-label">
                                    <i class="fas fa-key"></i> Confirmez avec votre mot de passe
                                </label>
                                <input type="password" name="password" class="profile-input"
                                    placeholder="Votre mot de passe actuel" required>
                            </div>
                            <div class="privacy-notice privacy-notice-info">
                                <i class="fas fa-info-circle"></i>
                                <span>Un code de vérification sera envoyé à votre nouvelle adresse email pour confirmer qu'elle
                                    existe.</span>
                            </div>
                            <div class="profile-form-actions">
                                <button type="submit" class="profile-btn-save">
                                    <i class="fas fa-paper-plane"></i> Envoyer le code
                                </button>
                                <a href="index.php?page=privacy" class="profile-btn-cancel">
                                    <i class="fas fa-times"></i> Annuler
                                </a>
                            </div>
                        </form>
                    <?php else : ?>
                        <div class="profile-field-header">
                            <span class="profile-field-label"><i class="fas fa-envelope"></i> Adresse email</span>
                            <a href="index.php?page=privacy&edit=email" class="profile-edit-btn">
                                <i class="fas fa-pencil-alt"></i> Modifier
                            </a>
                        </div>
                        <div class="profile-field-value">
                            <?= htmlspecialchars($user['email'] ?? 'Non renseigné') ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Password Field -->
                <div class="profile-field">
                    <?php if ($editField === 'password' && $step === 'verify') : ?>
                        <!-- Step 2: Enter verification code and new password -->
                        <div class="profile-field-header">
                            <span class="profile-field-label"><i class="fas fa-key"></i> Modification du mot de passe</span>
                        </div>
                        <form method="POST" action="index.php?page=privacy&action=verifyPasswordCode" class="profile-edit-form">
                            <?= $csrf->getTokenField() ?>

                            <div class="privacy-code-section">
                                <label class="privacy-label">
                                    <i class="fas fa-shield-alt"></i> Code de vérification (6 chiffres)
                                </label>
                                <input type="text" name="verification_code" class="profile-input privacy-code-input"
                                    placeholder="000000" maxlength="6" pattern="[0-9]{6}" required autofocus>
                                <div class="privacy-notice">
                                    <i class="fas fa-envelope"></i>
                                    <span>Un code a été envoyé à votre adresse email.</span>
                                </div>
                            </div>

                            <div class="privacy-password-section">
                                <label class="privacy-label">
                                    <i class="fas fa-lock"></i> Nouveau mot de passe
                                </label>
                                <input type="password" name="new_password" class="profile-input"
                                    placeholder="Nouveau mot de passe" required>
                                <input type="password" name="confirm_password" class="profile-input"
                                    placeholder="Confirmer le nouveau mot de passe" required>
                                <div class="privacy-password-requirements">
                                    <p><strong>Le mot de passe doit contenir :</strong></p>
                                    <ul>
                                        <li><i class="fas fa-check-circle"></i> Au moins 10 caractères</li>
                                        <li><i class="fas fa-check-circle"></i> Au moins 1 lettre majuscule</li>
                                        <li><i class="fas fa-check-circle"></i> Au moins 1 chiffre</li>
                                        <li><i class="fas fa-check-circle"></i> Au moins 1 caractère spécial</li>
                                        <li><i class="fas fa-exclamation-circle"></i> Être différent de l'ancien mot de passe
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="profile-form-actions">
                                <button type="submit" class="profile-btn-save">
                                    <i class="fas fa-check"></i> Changer le mot de passe
                                </button>
                                <a href="index.php?page=privacy" class="profile-btn-cancel">
                                    <i class="fas fa-times"></i> Annuler
                                </a>
                            </div>
                        </form>

                        <!-- Resend Code Section -->
                        <div class="privacy-resend-section" id="password-resend-section">
                            <p>Vous n'avez pas reçu le code ?</p>
                            <?php if ($passwordResendStatus['resend_count'] >= 5) : ?>
                                <div class="privacy-resend-disabled">
                                    <i class="fas fa-ban"></i>
                                    <span>Nombre maximum de renvois atteint.</span>
                                </div>
                            <?php else : ?>
                                <form method="POST" action="index.php?page=privacy&action=resendPasswordCode"
                                    class="privacy-resend-form" id="password-resend-form">
                                    <?= $csrf->getTokenField() ?>
                                    <button type="submit" class="privacy-resend-btn" id="password-resend-btn"
                                        <?= !$passwordResendStatus['can_resend'] ? 'disabled' : '' ?>>
                                        <i class="fas fa-redo"></i>
                                        <span id="password-resend-text">
                                            <?php if (!$passwordResendStatus['can_resend']) : ?>
                                                Renvoyer dans <span
                                                    id="password-countdown"><?= $passwordResendStatus['wait_seconds'] ?></span>s
                                            <?php else : ?>
                                                Renvoyer le code
                                            <?php endif; ?>
                                        </span>
                                    </button>
                                </form>
                                <?php if ($passwordResendStatus['resend_count'] > 0) : ?>
                                    <p class="privacy-resend-count">
                                        Renvois restants : <?= 5 - $passwordResendStatus['resend_count'] ?>/5
                                    </p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                    <?php elseif ($editField === 'password') : ?>
                        <!-- Step 1: Request verification code -->
                        <div class="profile-field-header">
                            <span class="profile-field-label"><i class="fas fa-key"></i> Modification du mot de passe</span>
                        </div>
                        <form method="POST" action="index.php?page=privacy&action=requestPasswordCode"
                            class="profile-edit-form">
                            <?= $csrf->getTokenField() ?>
                            <div class="privacy-notice privacy-notice-info">
                                <i class="fas fa-info-circle"></i>
                                <span>Pour modifier votre mot de passe, un code de vérification à 6 chiffres sera envoyé à votre
                                    adresse email.</span>
                            </div>
                            <div class="profile-form-actions">
                                <button type="submit" class="profile-btn-save">
                                    <i class="fas fa-paper-plane"></i> Envoyer le code
                                </button>
                                <a href="index.php?page=privacy" class="profile-btn-cancel">
                                    <i class="fas fa-times"></i> Annuler
                                </a>
                            </div>
                        </form>
                    <?php else : ?>
                        <div class="profile-field-header">
                            <span class="profile-field-label"><i class="fas fa-key"></i> Mot de passe</span>
                            <a href="index.php?page=privacy&edit=password" class="profile-edit-btn">
                                <i class="fas fa-pencil-alt"></i> Modifier
                            </a>
                        </div>
                        <div class="profile-field-value">
                            <span class="privacy-password-masked">••••••••••</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <a href="index.php?page=profile" class="profile-back-link">
        <i class="fas fa-arrow-left"></i> Retour au profil
    </a>
</div>

<!-- JavaScript for real-time countdown -->
<script src="./app/assets/js/privacy-countdown.js"></script>

<?php
end_page();
