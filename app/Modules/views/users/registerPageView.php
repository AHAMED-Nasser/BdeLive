<?php global $app;
/**
 * @var \App\Core\Security\CsrfProtection $csrf
 * @var array<string, mixed>|null $user
 * @var array<string, string|null> $flash
 */

$oldLastName = $app->session()->get('old_last_name');
$oldFirstName = $app->session()->get('old_first_name');
$oldEmail = $app->session()->get('old_email');
$oldUserStatus = $app->session()->get('old_user_status');

$app->session()->remove('old_last_name');
$app->session()->remove('old_first_name');
$app->session()->remove('old_email');
$app->session()->remove('old_user_status');

start_page("Inscription - BDELive", true, $user ?? null);
?>
<div class="forgot-container">
    <h1 class="title">Inscription</h1>

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

    <form action="index.php?page=register" method="POST">
        <label for="last_name">Nom</label>
        <input type="text" id="last_name" name="last_name" placeholder="Entrez votre nom" maxlength="100" value="<?= htmlspecialchars((string)$oldLastName) ?>" required>

        <label for="first_name">Prénom</label>
        <input type="text" id="first_name" name="first_name" placeholder="Entrez votre prénom" maxlength="100" value="<?= htmlspecialchars((string)$oldFirstName) ?>" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="Entrez votre email" maxlength="100" value="<?= htmlspecialchars((string)$oldEmail) ?>" required>

        <label for="user_status">Statut</label>
        <select id="user_status" name="user_status" required>
            <option value="">-- Sélectionnez --</option>
            <option value="BUT 1" <?= $oldUserStatus === 'BUT 1' ? 'selected' : ''?>>BUT 1</option>
            <option value="BUT 2" <?= $oldUserStatus === 'BUT 2' ? 'selected' : ''?>>BUT 2</option>
            <option value="BUT 3" <?= $oldUserStatus === 'BUT 3' ? 'selected' : ''?>>BUT 3</option>
            <option value="Personnel Enseignant" <?= $oldUserStatus === 'Personnel Enseignant' ? 'selected' : ''?>>Personnel Enseignant</option>
        </select>

        <label for="password">Mot de passe</label>
        <div class="password-container">
            <input id="password" type="password" name="password" placeholder="Entrez votre mot de passe" class="form-control" required>
            <button type="button" class="password-toggle">
                <i class="fa-regular fa-eye"></i>
            </button>
        </div>

        <label for="confirm-password">Confirmation mot de passe</label>
        <div class="password-container">
            <input id="confirm-password" type="password" name="confirm_password" placeholder="Confirmez votre mot de passe" class="form-control" required>
            <button type="button" class="password-toggle">
                <i class="fa-regular fa-eye"></i>
            </button>
        </div>

        <?= $csrf->getTokenField() ?>
        <button type="submit" name="ok">S'inscrire</button>
    </form>

    <a href="index.php?page=login">Déjà un compte ? Se connecter</a>
    <a href="index.php?page=home">← Retour à l'accueil</a>
</div>

<?php end_page(); ?>
