<?php
    start_page("Connexion - BDE Inform'Aix", true);
?>

    <div class="forgot-container">
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
            <label for="email">Adresse e-mail :</label>
            <input id="email" type="email" name="email" placeholder="Entrez votre adresse mail" required>
            
            <label for="password">Mot de passe :</label>
            <input id="password" type="password" name="pwd" placeholder="Entrez votre mot de passe" required>
            
            <button type="submit" name="ok">Se connecter</button>
        </form>
        
        <a href="index.php?page=home">← Retour à l'accueil</a>
        <a href="index.php?page=forgot_password">Mot de passe oublié ?</a>
        <a href="index.php?page=register">Pas de compte ? Inscrivez-vous</a>
    </div>

<?php end_page(); ?>