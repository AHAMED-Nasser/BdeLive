<?php
    require_once __DIR__ . "/../../include/include.inc.php";
    start_page("Réinitialiser le mot de passe - BDE Inform'Aix", true);
?>

<link rel="stylesheet" href="./assets/css/forgotPassword.css">
    <div class="forgot-container">
        <h1>Nouveau mot de passe</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <form action="index.php?page=reset_password" method="POST">
            <label for="password">Nouveau mot de passe :</label><br>
            <input id="password" type="password" name="password" placeholder="Entrez votre nouveau mot de passe" required minlength="6"><br><br>
            
            <label for="confirm_password">Confirmer le mot de passe :</label><br>
            <input id="confirm_password" type="password" name="confirm_password" placeholder="Confirmez votre mot de passe" required minlength="6"><br>
            
            <button type="submit" name="submit">Réinitialiser le mot de passe</button>
        </form>

        <a href="index.php?page=login"> <--- Retour page de connexion</a>
    </div>

<?php
    end_page();
?>

