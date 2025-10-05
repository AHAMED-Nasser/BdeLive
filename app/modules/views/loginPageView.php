<?php
    require_once __DIR__. '/../../include/include.inc.php';
    start_page("Login");
?>

    <h1>Connexion</h1>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    
    <form id="form" action="index.php?page=login" method="POST">
        <label for="email">Adresse e-mail</label>
        <input id="email" type="email" name="email" placeholder="Entrez votre Adresse mail" required> <br>
        <label for="password">Mot de passe</label>
        <input id="password" type="password" name="pwd" placeholder="Entrez votre mot de passe" required>
        <button type="submit" name="ok">Se connecter</button>
    </form>
    <br>
    <p><a href="index.php?page=home"> <-- Retour à l'accueil</a></p>
    <p><a href="index.php?page=forgot_password">Mot de passe oublié ?</a></p>
    <p><a href="index.php?page=register">Pas de compte?</a></p>

<?php end_page(); ?>