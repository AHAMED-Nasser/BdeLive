<?php
    require_once __DIR__ . "/../../include/include.inc.php";
    start_page("Mot de passe oublié - BDE Inform'Aix", true);
?>

<link rel="stylesheet" href="./assets/css/forgotPassword.css">
    <div class="forgot-container">
        <h1>Mot de passe oublié</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <form action="index.php?page=forgot_password" method="POST">
            <label for="email">Adresse e-mail :</label><br>
            <input id="email" type="email" name="email" placeholder="Entrez votre email" required><br>
            <button type="submit" name="submit">Envoyer le code</button>
        </form>

        <a href="index.php?page=login"> <--- Retour page de connexion</a>
    </div>

<?php
    end_page();
?>

