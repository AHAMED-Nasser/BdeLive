<?php
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    require_once __DIR__. '/../../include/include.inc.php';
    start_page("Connexion - BDE Inform'Aix");
?>

    <h1>Connexion</h1>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid_credentials'): ?>
        <div class="alert alert-danger">
            Email ou mot de passe incorrect
        </div>
    <?php endif; ?>
    
    <form id="form" action="index.php?page=login" method="POST">
        <label for="email">Adresse e-mail :</label>
        <input id="email" type="email" name="email" placeholder="Entrez votre adresse mail" required><br><br>
        
        <label for="password">Mot de passe :</label>
        <input id="password" type="password" name="pwd" placeholder="Entrez votre mot de passe" required><br><br>
        
        <button type="submit" name="ok">Se connecter</button>
    </form>
    <br>
    <p><a href="index.php?page=home">← Retour à l'accueil</a></p>
    <p><a href="#">Mot de passe oublié ?</a></p>
    <p><a href="index.php?page=register">Pas de compte ? Inscrivez-vous</a></p>

<?php end_page(); ?>
