<?php
    
    function start_page($title) {
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Site officiel du BDE Inform'Aix - BDE Informatique à Aix-en-Provence. Découvrez nos événements, avantages étudiants et réseaux sociaux.">
    <link rel="stylesheet" href="/app/public/css/style.css">
    <link rel="icon" href="/app/public/img/logo.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <title><?= $title ?></title>
</head>
<body>
    <header>
        <nav aria-label="Main navigation">
            <ul>
                <li><a href="../../../public/index.php">Accueil</a></li>
                <li><a href="#">Horaire</a></li>
                <li><a href="#">BDE Info</a></li>
                <li><a href="../auth/login.php">Connexion</a></li>
                <li><a href="../auth/register.php">Inscription</a></li>
            </ul>
        </nav>
    </header>
<?php } ?>

<?php
    function end_page() {
?>
    <footer>
        <nav aria-label="Footer navigation">
            <ul>
                <li><a href="#">Politique de confidentialité</a></li>
                <li><a href="#">Contact et FAQ</a></li>
                <li><a href="#">À propos</a></li>
                <li><a href="#">Mentions légales</a></li>
            </ul>
        </nav>
        <p>&copy; 2025 BdeLive. Tous droits réservés.</p>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.slim.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" defer></script>
</body>
</html>
<?php } ?>