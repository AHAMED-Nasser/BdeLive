<?php
/**
 * @var \App\Core\Security\CsrfProtection $csrf
 * @var array<string, mixed>|null $user
 * @var array<string, string|null> $flash
 */
start_page("Inscription - BDE Inform'Aix", true, $user ?? null);
?>
    <div class="forgot-container">
        <h1 class="title">Inscription</h1>
        
        <?php if (!empty($flash['error'])) : ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($flash['error']) ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($flash['success'])) : ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($flash['success']) ?>
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

            <?= $csrf->getTokenField() ?>
            <button type="submit" name="ok">S'inscrire</button>
        </form>
        
        <a href="index.php?page=login">Déjà un compte ? Se connecter</a>
        <a href="index.php?page=home">← Retour à l'accueil</a>
    </div>

<?php end_page(); ?>
