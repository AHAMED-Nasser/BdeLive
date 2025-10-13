<?php
    start_page("Inscription - BDE Inform'Aix", true);
?>
    <div class="forgot-container">
        <h1>Inscription</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <form action="index.php?page=register" method="POST">
            <label for="nom">Nom :</label>
            <input type="text" id="nom" name="nom" placeholder="Entrez votre nom" maxlength="20" required>

            <label for="prenom">Prénom :</label>
            <input type="text" id="prenom" name="prenom" placeholder="Entrez votre prénom" maxlength="20" required>

            <label for="email">Email :</label>
            <input type="email" id="email" name="email" placeholder="Entrez votre email" maxlength="100" required>

            <label for="classe_annee">Année de classe (1, 2 ou 3) :</label>
            <select id="classe_annee" name="classe_annee" required>
                <option value="">-- Sélectionnez --</option>
                <option value="1">BUT 1</option>
                <option value="2">BUT 2</option>
                <option value="3">BUT 3</option>
            </select>

            <label for="password">Mot de passe :</label>
            <input type="password" id="password" name="password" placeholder="Entrez votre mot de passe" required>

            <button type="submit" name="ok">S'inscrire</button>
        </form>
        
        <a href="index.php?page=login">Déjà un compte ? Se connecter</a>
        <a href="index.php?page=home">← Retour à l'accueil</a>
    </div>

<?php
    end_page();
?>
