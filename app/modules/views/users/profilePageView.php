<?php
    start_page("Modification du profil", True);
?>

<div class="change-user">
    <h1>Modification des informations</h1>
    <form action="index.php?page=profile" method="post">
        <div class="change-user-div">
            <label class="change-user-label">Prénom</label>
            <input class="change-user-input" placeholder="Changement du prénom">
            <button class="change-user-button">Confirmer le changement de prénom</button>
        </div>
        <div class="change-user-div">
            <label class="change-user-label">Nom</label>
            <input class="change-user-input" placeholder="Changement du nom">
            <button class="change-user-button">Confirmer le changement du nom</button>
        </div>
        <div class="change-user-div">
            <label class="change-user-label">Mail</label>
            <input class="change-user-input" placeholder="Nouvelle adresse mail">
            <button class="change-user-button">Confirmer la nouvelle adresse mail</button>
        </div>
    </form>
</div>

