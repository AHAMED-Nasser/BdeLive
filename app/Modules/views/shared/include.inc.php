<?php

use App\Core\Application;
/**
 * @param string $title
 * @param bool $wouldNav
 * @param array<string, mixed>|null $user
 * @return void
 */
function start_page(string $title, bool $wouldNav = true, ?array $user = null): void
{
    $auth = Application::getInstance()->auth();
    $isAdmin = $auth->isAdmin();
    ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Site officiel du BDE Inform'Aix - BDE Informatique à Aix-en-Provence. Découvrez nos événements, avantages étudiants et réseaux sociaux.">
    <link rel="icon" href="./assets/img/logo.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="./assets/css/style.css">
    <link rel="stylesheet" href="./assets/css/footer.css">
    <link rel="stylesheet" href="./assets/css/navbar.css">
    <link rel="stylesheet" href="./assets/css/member.css">
    <link rel="stylesheet" href="./assets/css/team.css">
    <link rel="stylesheet" href="./assets/css/join.css">
    <link rel="stylesheet" href="./assets/css/caroussel.css">
    <link rel="stylesheet" href="./assets/css/createEvent.css">
    <link rel="stylesheet" href="./assets/css/profile.css">
    <link rel="stylesheet" href="./assets/css/articles.css">
    <link rel="stylesheet" href="./assets/css/homepage-articles.css">
    <title><?= $title ?></title>
</head>
<body>
    <?php if ($wouldNav) : ?>
    <header>
        <nav class="nav" aria-label="Main navigation">
            <ul>
                <a href="index.php?page=home" class="nav-logo">
                    <img src="./assets/img/logo.png" alt="Logo Bde">
                </a>
            </ul>
            <ul>

                <li><a href="index.php?page=home">Accueil</a></li>
                <li><a href="index.php?page=articles">Articles</a></li>
                <li><a href="index.php?page=event">Evénements</a></li>
                <?php if (isset($user) && $user !== null && $isAdmin) : ?>
                    <li><a href="index.php?page=createEvent">Créer évenement</a></li>
                    <li><a href="index.php?page=createArticle">Créer article</a></li>
                    <li><a href="index.php?page=adminSection">Administrer</a></li>
                    <li><a href="index.php?page=profile">
                        <?php
                        $displayName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
                        echo htmlspecialchars($displayName ?: 'Mon Profil');
                        ?>
                    </a></li>
                    <li><a href="index.php?page=logout">Déconnexion</a></li>
                <?php elseif (isset($user) && $user !== null) : ?>
                    <li><a href="index.php?page=deleteAccount">Suppression Compte</a></li>
                    <li><a href="index.php?page=profile">
                        <?php
                        $displayName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
                        echo htmlspecialchars($displayName ?: 'Mon Profil');
                        ?>
                    </a></li>
                    <li><a href="index.php?page=logout">Déconnexion</a></li>
                <?php else : ?>
                    <li><a href="index.php?page=login">Connexion</a></li>
                    <li><a href="index.php?page=register">Inscription</a></li>
                <?php endif; ?>
            </ul>

            <!-- Menu Hamburger -->
            <input type="checkbox" id="menu-toggle" class="menu-toggle">
            <label for="menu-toggle" class="hamburger-icon">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </label>

            <!-- Menu Sidebar -->
            <div class="sidebar-menu">
                <ul>
                    <li><a href="index.php?page=home">Accueil</a></li>
                    <li><a href="index.php?page=articles">Nos articles</a></li>
                    <?php if (isset($user) && $user !== null && $isAdmin) : ?>
                        <li><a href="index.php?page=event">Evénements</a></li>
                        <li><a href="index.php?page=createEvent">Créer un événement</a></li>
                        <li><a href="index.php?page=createArticle">Créer un article</a></li>
                        <li><a href="index.php?page=profile">
                            <?php
                            $displayName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
                            echo htmlspecialchars($displayName ?: 'Mon Profil');
                            ?>
                        </a></li>
                        <li><a href="index.php?page=logout">Déconnexion</a></li>
                    <?php elseif (isset($user) && $user !== null) : ?>
                        <li><a href="index.php?page=event">Evénements</a></li>
                        <li><a href="index.php?page=profile">
                            <?php
                            $displayName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
                            echo htmlspecialchars($displayName ?: 'Mon Profil');
                            ?>
                        </a></li>
                        <li><a href="index.php?page=deleteAccount">Suppression Compte</a></li>
                        <li><a href="index.php?page=logout">Déconnexion</a></li>
                    <?php else : ?>
                        <li><a href="index.php?page=event">Evénements</a></li>
                        <li><a href="index.php?page=login">Connexion</a></li>
                        <li><a href="index.php?page=register">Inscription</a></li>
                    <?php endif; ?>
                    <li><a href="index.php?page=legalTerms">Mentions légales</a></li>
                    <li><a href="index.php?page=sitemap">Plan du site</a></li>
                </ul>
            </div>
        </nav>
    </header>
    <?php endif; ?>

<?php }
?>

<?php
function end_page(): void
{
    ?>
    <footer>
        <nav aria-label="Footer navigation">
            <ul>
                <li><a href="index.php?page=about">À propos</a></li> <br><br>
                <li><a href="index.php?page=legalTerms">Mentions légales</a></li>
                <li><a href="index.php?page=sitemap">Plan du site</a></li> <br><br>
                <li><a href="index.php?page=history">Notre histoire</a></li> <br><br>


                <div class="social-logos"> <h3>Nos réseaux</h3>
                    <a href="https://www.instagram.com/informaix/" target="_blank" rel="noopener noreferrer" class="footer-logo">
                        <img src="../../../assets/img/insta.png" alt="Instagram" class="insta-logo">
                    </a>

                    <a href="https://discord.gg/4dXHpN6JCK" target="_blank" rel="noopener noreferrer" class="footer-logo">
                        <img src="../../../assets/img/discord.png" alt="Discord" class="discord-logo">
                    </a>

                    <a href="https://www.tiktok.com/#/" target="_blank" rel="noopener noreferrer">
                        <img src="../../../assets/img/tiktok.png" alt="Tiktok" class="tiktok-logo">
                    </a>
                </div>
            </ul>
        </nav>
        <p>&copy; <?= date("Y") ?> BdeLive. Tous droits réservés.</p>
    </footer>

<!--    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.slim.min.js" defer></script>-->
<!--    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" defer></script>-->
        <script src="./assets/js/slider.js"></script>
        <script src="./assets/js/dropImageArea.js"></script>

    <?php
    // Display the cookie popup on all pages (autoload Composer)
    if (class_exists('App\\Modules\\Controllers\\Cookie\\CookieConsentController')) {
        $cls = 'App\\Modules\\Controllers\\Cookie\\CookieConsentController';
        new $cls();
    }
    ?>

        </body>
</html>
<?php }
?>