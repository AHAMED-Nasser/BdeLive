<?php
    require_once __DIR__. '/../../include/include.inc.php';
    start_page("Login", false);
?>

    <h1>Connexion</h1>
    <form id="form" action="index.php?page=home" method="POST">
        <label for="email">Adresse e-mail</label>
        <input id="email" type="text" name="Adresse e-mail" value=" "> <br>
        <label for="password">Password</label>
        <input id="password" type="text" name="Password" value=" ">
    </form>
    <br>
    <p><a href="index.php?page=home">Revenir à l'accueil</a></p>
    <p><a href="index.php?page= ">Mot de passe oublié ?</a></p>

<?php end_page(); ?>