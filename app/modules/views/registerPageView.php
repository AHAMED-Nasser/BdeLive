<?php
    require_once __DIR__. '/../../include/include.inc.php';
    start_page("Inscription - BDE Inform'Aix")
?>
    <h1>Inscription</h1>
    <form action="index.php?page=home" method="POST">
        <label for="username">Nom d'utilisateur :</label>
        <input type="text" id="username" name="username" placeholder="Entrez votre nom d'utilisateur" required><br><br>

        <label for="email">Email :</label>
        <input type="email" id="email" name="email" placeholder="Entrez votre email" required><br><br>

        <label for="password">Mot de passe :</label>
        <input type="password" id="password" name="password" placeholder="Entrez votre mot de passe" required><br><br>

        <input type="submit" value="S'inscrire" name="ok">
    </form>
    
    <p><a href="index.php?page=login">Déjà un compte ? Se connecter</a></p>
    <p><a href="index.php?page=home">← Retour à l'accueil</a></p>

<?php
    end_page();
?>
