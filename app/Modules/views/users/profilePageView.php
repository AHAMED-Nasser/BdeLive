<?php
/**
 * @var \App\Core\Security\CsrfProtection $csrf
 * @var array<string, mixed>|null $user
 * @var array<string, string|null> $flash
 */
start_page("Modification du profil", true, $user ?? null);
?>

<div class="change-user">

    <h1>Modification des informations</h1>

    <form method="POST" action="index.php?page=profile&action=processFirstName">
        <?= $csrf->getTokenField() ?>

        <div class="change-user-div">

            <div>
                <label class="change-user-label">Prénom</label>

                <?php if (isset($user['first_name'])) : ?>
                    <span>(Actuel : <?= htmlspecialchars($user['first_name'])?>)</span>
                <?php endif ?>
            </div>

            <input class="change-user-input" type="text" name="first-name" placeholder="Saisissez le nouveau prénom">

            <button class="change-user-button" type="submit">Confirmer</button>

        </div>

    </form>

    <form method="POST" action="index.php?page=profile&action=processLastName">
        <?= $csrf->getTokenField() ?>

        <div class="change-user-div">
            <div>
                <label class="change-user-label">Nom</label>
                <?php if (isset($user['last_name'])) : ?>
                    <span>(Actuel : <?= htmlspecialchars($user['last_name'])?>)</span>
                <?php endif?>
            </div>
            <input class="change-user-input" placeholder="Saisissez le nouveau nom" name="last-name">
            <button class="change-user-button" type="submit">Confirmer</button>
        </div>
    </form>

    <div class="change-user-div">
        <div>
            <label class="change-user-label">Mail</label>
            <?php if (isset($user['email'])) : ?>
                <span>(Actuel : <?= htmlspecialchars($user['email'])?>)</span>
            <?php endif?>
        </div>
        <input class="change-user-input" placeholder="Entrez votre nouvelle adresse mail">
        <button class="change-user-button">Confirmer</button>
    </div>

    <div class="change-user-div">
        <label class="change-user-label">Mot de passe</label>
        <input class="change-user-input" placeholder="Entrez votre mot de passe actuel" type="password">
        <input class="change-user-input" placeholder="Entrez votre nouveau mot de passe" type="password">
        <input class="change-user-input" placeholder="Confirmez votre nouveau mot de passe" type="password">
        <button class="change-user-button">Confirmer</button>
    </div>
</div>

<?php end_page(); ?>
