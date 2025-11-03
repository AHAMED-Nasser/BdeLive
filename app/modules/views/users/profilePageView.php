<?php
    start_page("Modification du profil", True);
?>

<div class="change-user">

    <h1>Modification des informations</h1>
    <form method="POST" action="index.php?page=profile">
        <div class="change-user-div">
            <div>
                <label class="change-user-label">Prénom</label>
                <?php if (isset($_SESSION['first_name'])) : ?>
                    <span>(Actuel : <?= htmlspecialchars($_SESSION['first_name'])?>)</span>
                <?php endif ?>
            </div>

            <input class="change-user-input" type="text" name="first-name" placeholder="Saisissez le nouveau prénom">
            <button class="change-user-button" type="submit" name="ok">Confirmer</button>
        </div>
        <div class="change-user-div">
            <div>
                <label class="change-user-label">Nom</label>
                <?php if (isset($_SESSION['last_name'])) : ?>
                    <span>(Actuel : <?= htmlspecialchars($_SESSION['last_name'])?>)</span>
                <?php endif?>
            </div>
            <input class="change-user-input" placeholder="Saisissez le nouveau nom" name="last-name">
            <button class="change-user-button">Confirmer</button>
        </div>

        <div class="change-user-div">
            <div>
                <label class="change-user-label">Mail</label>
                <?php if (isset($_SESSION['email'])) : ?>
                    <span>(Actuel : <?= htmlspecialchars($_SESSION['email'])?>)</span>
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
    </form>
</div>