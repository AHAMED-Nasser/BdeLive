<?php
    require_once __DIR__ . "/../../include/include.inc.php";

    start_page("Mot de passe oublié - BDE Inform'Aix", true);
?>

    <div class="forgot-container">
        <h1>Mot de passe oublié</h1>
        <form action="index.php?page=home" method="POST">
            <label for="email">Adresse e-mail :</label>
            <input id="email" type="email" name="email" required>
            <button type="submit">Envoyer le lien</button>
        </form>

        <a href="index.php?page=login">&larr; Retour page de connexion</a>
    </div>

<?php
    end_page();
?>

