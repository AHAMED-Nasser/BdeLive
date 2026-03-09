<?php
/**
 * @var \App\Core\Security\CsrfProtection $csrf
 * @var array<string, mixed>|null $user
 * @var array<string, string|null> $flash
 */
start_page("Réinitialiser le mot de passe - BDELive ", true, $user ?? null);
?>

<div class="forgot-container">
    <h1 class="title">Nouveau mot de passe</h1>

    <?php if (!empty($flash['error'])) : ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($flash['error']) ?>
        </div>
    <?php endif; ?>

    <form class="form-authentification" action="index.php?page=reset_password" method="POST">
        <?= $csrf->getTokenField() ?>

        <label for="password">Nouveau mot de passe :</label><br>
        <div class="password-container">
            <input id="password" type="password" name="password" placeholder="Entrez votre nouveau mot de passe" class="form-control" required>
            <button type="button" class="password-toggle">
                <i class="fa-regular fa-eye"></i>
            </button>
        </div>

        <div id="pwd-conditions" class="pwd-conditions" style="display: none">
            <p class="pwd-conditions-message">Votre mot de passe doit contenir</p>
            <ul class="pwd-conditions-list">
                <li id="verifyLength" class="invalid"><span>*</span> Au moins 12 caractères</li>
                <li id="verifyLower" class="invalid"><span>*</span> Au moins 1 minuscule</li>
                <li id="verifyUpper" class="invalid"><span>*</span> Au moins 1 majuscule</li>
                <li id="verifyDigit" class="invalid"><span>*</span> Au moins 1 chiffre</li>
                <li id="verifySpecialChar" class="invalid"><span>*</span> Au moins 1 caractère spécial (ex: @, $, !, %, *, ?, &) </li>
            </ul>
        </div>



        <label for="confirm-password">Confirmer le mot de passe :</label><br>
        <div class="password-container">
        <input id="confirm-password" type="password" name="confirm-password" placeholder="Confirmez votre mot de passe" class="form-control" required>
        <button type="button" class="password-toggle">
            <i class="fa-regular fa-eye"></i>
        </button>
        </div>

        <p class="confirm-pwd-message"></p>

        <button type="submit" name="submit">Réinitialiser le mot de passe</button>
    </form>

    <a href="index.php?page=login"><i class="fa-solid fa-arrow-left"></i> Retour à la connexion</a>
</div>

<?php
end_page();
?>
