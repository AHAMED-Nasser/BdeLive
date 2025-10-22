<?php
start_page("Inscription - BDE Inform'Aix", true);
?>
    <div class="forgot-container">
        <h1 class="title">Inscription</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <form action="index.php?page=register" method="POST">
            <label for="last_name">Nom :</label>
            <input type="text" id="last_name" name="last_name" placeholder="Entrez votre nom" maxlength="100" required>

            <label for="first_name">Prénom :</label>
            <input type="text" id="first_name" name="first_name" placeholder="Entrez votre prénom" maxlength="100" required>

            <label for="email">Email :</label>
            <input type="email" id="email" name="email" placeholder="Entrez votre email" maxlength="100" required>

            <label for="user_status">Statut :</label>
            <select id="user_status" name="user_status" required>
                <option value="">-- Sélectionnez --</option>
                <option value="BUT 1">BUT 1</option>
                <option value="BUT 2">BUT 2</option>
                <option value="BUT 3">BUT 3</option>
                <option value="Personnel Enseignant">Personnel Enseignant</option>
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
