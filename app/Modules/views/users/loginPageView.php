<?php global $app;
/**
 * @var \App\Core\Security\CsrfProtection $csrf
 * @var array<string, mixed>|null $user
 * @var array<string, string|null> $flash
 * @var bool   $isSuspect        True when the IP+email pair has >= 5 failed attempts in 15 min
 * @var string $recaptchaSiteKey Google reCAPTCHA v2 public site key
 */

$oldEmail = $app->session()->get('old_email');
$app->session()->remove('old_email');

start_page("Connexion - BDELive", true, $user ?? null);

// Load reCAPTCHA script early in body so it is ready when the widget div is rendered
if ($isSuspect && $recaptchaSiteKey !== '') {
    echo '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
}
?>

<div class="forgot-container">
    <h1 class="title">Connexion</h1>

    <?php if (!empty($flash['success'])) : ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($flash['success']) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($flash['error'])) : ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($flash['error']) ?>
        </div>
    <?php endif; ?>

    <?php if ($isSuspect && $recaptchaSiteKey === '') : ?>
        <div class="alert alert-danger">Warning: reCAPTCHA Site Key is missing in .env</div>
    <?php endif; ?>

    <form class="form-authentification" id="form" action="index.php?page=login" method="POST">
        <label for="email">Adresse e-mail :</label>
        <input id="email" type="email" name="email" placeholder="Entrez votre adresse mail" value="<?= htmlspecialchars((string)$oldEmail) ?>" required>

        <label for="password">Mot de passe :</label>
        <div class="password-container">
            <input id="password" type="password" name="password" placeholder="Entrez votre mot de passe" class="form-control" required>
            <button type="button" id="togglePassword" class="password-toggle">
                <i class="fa-regular fa-eye"></i>
            </button>
        </div>


        <?= $csrf->getTokenField() ?>

        <?php if ($isSuspect && $recaptchaSiteKey !== '') : ?>
            <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars($recaptchaSiteKey) ?>"></div>
        <?php endif; ?>

        <button type="submit" name="ok">Se connecter</button>
    </form>

    <a href="index.php?page=home">← Retour à l'accueil</a>
    <a href="index.php?page=forgot_password">Mot de passe oublié ?</a>
    <a href="index.php?page=register">Pas de compte ? Inscrivez-vous</a>
</div>

<?php end_page(); ?>
