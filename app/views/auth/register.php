<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/css/style.css">
    <title>Inscription</title>
</head>
<body>
    <h1>Inscription</h1>
    <form action="register.php" method="POST">
        <label for="username">Nom d'utilisateur :</label>
        <input type="text" id="username" name="username" placeholder="Entrez votre nom d'utilisateur" required><br><br>

        <label for="email">Email :</label>
        <input type="email" id="email" name="email" placeholder="Entrez votre email" required><br><br>

        <label for="password">Mot de passe :</label>
        <input type="password" id="password" name="password" placeholder="Entrez votre mot de passe" required><br><br>

        <input type="submit" value="S'inscrire" name="ok">
    </form>
    
    <p><a href="login.php">Déjà un compte ? Se connecter</a></p>
    <p><a href="../../public/index.php">← Retour à l'accueil</a></p>
</body>
</html>
