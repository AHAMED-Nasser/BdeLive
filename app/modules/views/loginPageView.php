<?php
    require_once __DIR__. '/../../include/include.inc.php';
    start_page("Login");
?>

    <h1>Connexion</h1>
    <form id="form" action="../../include/AuthController.php" method="POST">
        <label for="email">Adresse e-mail</label>
        <input id="email" type="text" name="email" placeholder="Entrez votre Adresse mail" required> <br>
        <label for="password">Password</label>
        <input id="password" type="text" name="pwd" placeholder="Entrez votre mot de passe" required>
        <button type="submit" name="ok">Se connecter</button>
    </form>
    <br>
    <p><a href="index.php?page=home"> <-- Retour à l'accueil</a></p>
    <p><a href="index.php?page= ">Mot de passe oublié ?</a></p>
    <p><a href="index.php?page=register">Pas de compte?</a></p>

<?php end_page(); ?>