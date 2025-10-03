<?php
    
    function start_page(string $title, bool $wouldNav = true) {
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Site officiel du BDE Inform'Aix - BDE Informatique à Aix-en-Provence. Découvrez nos événements, avantages étudiants et réseaux sociaux.">
    <link rel="stylesheet" href="./assets/css/style.css">
    <link rel="stylesheet" href="./assets/css/navbar.css"
    <link rel="icon" href="./assets/img/logo.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <title><?= $title ?></title>
</head>
<body>
<?php if ($wouldNav): ?>
    <header>
        <nav class="nav" aria-label="Main navigation">
            <ul>
                <a href="index.php?page=home" class="nav-logo">
                    <img src="./assets/img/logo.png" alt="Logo Bde">
                </a>
            </ul>
            <ul>

                <li><a href="index.php?page=home">Accueil</a></li>
                <li><a href="#">Horaire</a></li>
                <li><a href="#">BDE Info</a></li>
                <li><a href="index.php?page=login">Connexion</a></li>
                <li><a href="index.php?page=register">Inscription</a></li>
            </ul>
        </nav>
    </header>
<?php endif; ?>

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