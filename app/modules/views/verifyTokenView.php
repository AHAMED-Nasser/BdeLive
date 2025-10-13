<?php
    start_page("Vérification du code - BDE Inform'Aix", true);
?>

    <div class="forgot-container">
        <h1>Vérification du code</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <p>Un code de vérification a été envoyé à votre adresse email. Veuillez le saisir ci-dessous :</p>
        
        <form action="index.php?page=verify_token" method="POST">
            <label for="token">Code de vérification :</label><br>
            <input id="token" type="text" name="token" placeholder="Entrez le code reçu par email" required maxlength="64"><br>
            <button type="submit" name="submit">Vérifier le code</button>
        </form>

        <a href="index.php?page=login"> <--- Retour page de connexion</a>
    </div>

<?php
    end_page();
?>
