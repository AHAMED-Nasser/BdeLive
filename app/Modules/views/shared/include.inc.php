<?php

declare(strict_types=1);

use App\Core\Application;

/**
 * Renders the page header with navigation
 *
 * Generates the HTML head section and navigation bar with responsive
 * hamburger menu for mobile devices. Includes user-specific navigation
 * options based on authentication status and user role.
 *
 * @author BdeLive Team
 * @version 1.1.0
 * @package BdeLive\Views\Shared
 *
 * @param string $title Page title for the browser tab
 * @param bool $wouldNav Whether to display the navigation bar
 * @param array<string, mixed>|null $user Current user data or null if not logged in
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
        
        <!-- CSRF Token for JavaScript AJAX requests -->
        <?php
        $csrf = Application::getInstance()->csrf();
        ?>
        <meta name="csrf-token" content="<?= htmlspecialchars($csrf->getToken(), ENT_QUOTES, 'UTF-8') ?>">
        
        <link rel="icon" href="./assets/img/logo.png">

        <!-- Anti-FOUC: Script inline pour détection immédiate du mode sombre -->
        <script>
            (function() {
                const DARK_MODE_KEY = 'darkMode';
                const DARK_MODE_CLASS = 'dark-mode';
                const LIGHT_MODE_CLASS = 'light-mode';

                function getStoredPreference() {
                    try {
                        return localStorage.getItem(DARK_MODE_KEY);
                    } catch (e) {
                        return null;
                    }
                }

                function isSystemDarkMode() {
                    return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                }

                function determineDarkMode() {
                    const stored = getStoredPreference();
                    if (stored !== null) {
                        return stored === 'true';
                    }
                    return isSystemDarkMode();
                }

                const isDark = determineDarkMode();
                const html = document.documentElement;
                if (isDark) {
                    html.classList.add(DARK_MODE_CLASS);
                } else {
                    html.classList.add(LIGHT_MODE_CLASS);
                }
            })();
        </script>

        <!-- Google Fonts: Roboto -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://unpkg.com/easymde/dist/easymde.min.css">

        <!-- Dark Mode CSS - Doit être chargé en premier -->
        <link rel="stylesheet" href="./assets/css/themes/dark-mode.css">

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <link rel="stylesheet" href="./assets/css/base/password-control.css">
        <link rel="stylesheet" href="./assets/css/base/style.css">
        <link rel="stylesheet" href="./assets/css/layout/footer.css">
        <link rel="stylesheet" href="./assets/css/layout/navbar.css">
        <link rel="stylesheet" href="./assets/css/pages/member.css">
        <link rel="stylesheet" href="./assets/css/pages/team.css">
        <link rel="stylesheet" href="./assets/css/pages/join.css">
        <link rel="stylesheet" href="./assets/css/pages/caroussel.css">
        <link rel="stylesheet" href="./assets/css/pages/createEvent.css">
        <link rel="stylesheet" href="./assets/css/pages/profile.css">
        <link rel="stylesheet" href="./assets/css/pages/articles.css">
        <link rel="stylesheet" href="./assets/css/pages/homepage-articles.css">
        <link rel="stylesheet" href="./assets/css/pages/admin.css">
        <link rel="stylesheet" href="./assets/css/pages/schedule.css">
        <link rel="stylesheet" href="./assets/css/pages/event.css">
        <link rel="stylesheet" href="./assets/css/components/modal.css">
        <link rel="stylesheet" href="./assets/css/pages/group-registration.css">
        <link rel="stylesheet" href="./assets/css/pages/bde-opening.css">
        <title><?= $title ?></title>
    </head>
    <body>
    <?php if ($wouldNav) : ?>
    <header>
        <nav class="nav" aria-label="Main navigation">
            <div class="nav-inner">
            <ul>
                <li>
                    <a href="index.php?page=home" class="nav-logo" aria-label="BDE Inform'Aix - Accueil">
                        <img src="./assets/img/logo.png" alt="Logo BDE Inform'Aix">
                    </a>
                </li>
            </ul>
            <ul>
                <li><a href="index.php?page=articles">Nos articles</a></li>
                <li><a href="index.php?page=event">Evénements</a></li>
                <?php if (isset($user) && $user !== null && $isAdmin) : ?>
                    <li><a href="index.php?page=schedule">Emploi du temps</a></li>
                    <!-- Dark Mode Toggle -->
                    <li>
                        <button id="dark-mode-toggle" class="dark-mode-toggle" aria-label="Basculer le mode sombre">
                            <svg class="dark-mode-icon sun-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="4"></circle>
                                <path d="M12 2v2"></path>
                                <path d="M12 20v2"></path>
                                <path d="m4.93 4.93 1.41 1.41"></path>
                                <path d="m17.66 17.66 1.41 1.41"></path>
                                <path d="M2 12h2"></path>
                                <path d="M20 12h2"></path>
                                <path d="m6.34 17.66-1.41 1.41"></path>
                                <path d="m19.07 4.93-1.41 1.41"></path>
                            </svg>
                            <svg class="dark-mode-icon moon-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20.985 12.486a9 9 0 1 1-9.473-9.472c.405-.022.617.46.402.803a6 6 0 0 0 8.268 8.268c.344-.215.825-.004.803.401"></path>
                            </svg>
                        </button>
                    </li>
                    <!-- Profile dropdown menu -->
                    <li class="profile-dropdown-container">
                        <input type="checkbox" id="profile-dropdown-toggle" class="profile-dropdown-toggle">
                        <label for="profile-dropdown-toggle" class="profile-dropdown-trigger">
                            <span class="visually-hidden">Ouvrir le menu de profil</span>
                            <svg class="profile-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
                            </svg>
                            <?php
                            $displayName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
                            echo htmlspecialchars($displayName ?: 'Mon Profil');
                            ?>
                            <i class="fas fa-chevron-down dropdown-arrow" aria-hidden="true"></i>
                        </label>
                        <div class="profile-dropdown-menu">
                            <a href="index.php?page=profile"><i class="fas fa-id-card"></i> Mon Profil</a>
                            <a href="index.php?page=privacy"><i class="fas fa-shield-alt"></i> Confidentialité</a>
                            <?php
                            // Vérification supplémentaire de sécurité pour les liens admin
                            // @phpstan-ignore-next-line
                            if ($isAdmin) : ?>
                                <a href="index.php?page=createEvent"><i class="fas fa-plus-circle"></i> Créer un événement</a>
                                <a href="index.php?page=createArticle"><i class="fas fa-edit"></i> Créer un article</a>
                                <div class="dropdown-divider"></div>
                                <a href="index.php?page=adminSection"><i class="fas fa-cogs"></i> Administration</a>
                            <?php endif; ?>
                            <a href="index.php?page=logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
                            <a href="index.php?page=deleteAccount" class="dropdown-danger"><i class="fas fa-trash-alt"></i> Supprimer mon compte</a>
                        </div>
                    </li>
                <?php elseif (isset($user) && $user !== null) : ?>
                    <li><a href="index.php?page=schedule">Emploi du temps</a></li>
                    <!-- Dark Mode Toggle -->
                    <li>
                        <button id="dark-mode-toggle" class="dark-mode-toggle" aria-label="Basculer le mode sombre">
                            <svg class="dark-mode-icon sun-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="4"></circle>
                                <path d="M12 2v2"></path>
                                <path d="M12 20v2"></path>
                                <path d="m4.93 4.93 1.41 1.41"></path>
                                <path d="m17.66 17.66 1.41 1.41"></path>
                                <path d="M2 12h2"></path>
                                <path d="M20 12h2"></path>
                                <path d="m6.34 17.66-1.41 1.41"></path>
                                <path d="m19.07 4.93-1.41 1.41"></path>
                            </svg>
                            <svg class="dark-mode-icon moon-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20.985 12.486a9 9 0 1 1-9.473-9.472c.405-.022.617.46.402.803a6 6 0 0 0 8.268 8.268c.344-.215.825-.004.803.401"></path>
                            </svg>
                        </button>
                    </li>
                    <!-- Profile dropdown menu -->
                    <li class="profile-dropdown-container">
                        <input type="checkbox" id="profile-dropdown-toggle" class="profile-dropdown-toggle">
                        <label for="profile-dropdown-toggle" class="profile-dropdown-trigger">
                            <span class="visually-hidden">Ouvrir le menu de profil</span>
                            <svg class="profile-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
                            </svg>
                            <?php
                            $displayName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
                            echo htmlspecialchars($displayName ?: 'Mon Profil');
                            ?>
                            <i class="fas fa-chevron-down dropdown-arrow" aria-hidden="true"></i>
                        </label>
                        <div class="profile-dropdown-menu">
                            <a href="index.php?page=profile"><i class="fas fa-id-card"></i> Mon Profil</a>
                            <a href="index.php?page=privacy"><i class="fas fa-shield-alt"></i> Confidentialité</a>
                            <div class="dropdown-divider"></div>
                            <a href="index.php?page=logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
                            <a href="index.php?page=deleteAccount" class="dropdown-danger"><i class="fas fa-trash-alt"></i> Supprimer mon compte</a>
                        </div>
                    </li>
                <?php else : ?>
                    <li><a href="index.php?page=login">Connexion</a></li>
                    <li><a href="index.php?page=register">Inscription</a></li>
                    <!-- Dark Mode Toggle -->
                    <li>
                        <button id="dark-mode-toggle" class="dark-mode-toggle" aria-label="Basculer le mode sombre">
                            <svg class="dark-mode-icon sun-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="4"></circle>
                                <path d="M12 2v2"></path>
                                <path d="M12 20v2"></path>
                                <path d="m4.93 4.93 1.41 1.41"></path>
                                <path d="m17.66 17.66 1.41 1.41"></path>
                                <path d="M2 12h2"></path>
                                <path d="M20 12h2"></path>
                                <path d="m6.34 17.66-1.41 1.41"></path>
                                <path d="m19.07 4.93-1.41 1.41"></path>
                            </svg>
                            <svg class="dark-mode-icon moon-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20.985 12.486a9 9 0 1 1-9.473-9.472c.405-.022.617.46.402.803a6 6 0 0 0 8.268 8.268c.344-.215.825-.004.803.401"></path>
                            </svg>
                        </button>
                    </li>
                <?php endif; ?>
            </ul>
            </div>

            <!-- Menu Hamburger -->
            <input type="checkbox" id="menu-toggle" class="menu-toggle">
            <label for="menu-toggle" class="hamburger-icon">
                <span class="visually-hidden">Ouvrir ou fermer le menu de navigation</span>
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </label>

            <!-- Overlay pour fermer le menu -->
            <button type="button" class="sidebar-overlay" aria-label="Fermer le menu" onclick="document.getElementById('menu-toggle').checked = false"></button>

            <!-- Menu Sidebar -->
            <div class="sidebar-menu">
                <ul>
                    <!-- Section Navigation -->
                    <li class="sidebar-section-title">Navigation</li>
                    <li><a href="index.php?page=home"><i class="fas fa-home"></i> Accueil</a></li>
                    <li><a href="index.php?page=articles"><i class="fas fa-newspaper"></i> Nos articles</a></li>
                    <li><a href="index.php?page=event"><i class="fas fa-calendar-alt"></i> Événements</a></li>
                    <li><a href="index.php?page=schedule"><i class="fas fa-calendar-week"></i> Emploi du temps</a></li>

                    <?php if (isset($user) && $isAdmin) : ?>
                        <!-- Section Administration (BDE uniquement) -->
                        <li class="sidebar-section-title">Administration</li>
                        <li><a href="index.php?page=createEvent"><i class="fas fa-plus-circle"></i> Créer un événement</a></li>
                        <li><a href="index.php?page=createArticle"><i class="fas fa-edit"></i> Créer un article</a></li>
                        <li><a href="index.php?page=adminSection"><i class="fas fa-cogs"></i> Administration</a></li>
                    <?php endif; ?>

                    <?php if (!isset($user)) : ?>
                        <!-- Section Authentification (non connecté) -->
                        <li class="sidebar-section-title">Connexion</li>
                        <li><a href="index.php?page=login"><i class="fas fa-sign-in-alt"></i> Connexion</a></li>
                        <li><a href="index.php?page=register"><i class="fas fa-user-plus"></i> Inscription</a></li>
                    <?php endif; ?>

                    <?php if (isset($user) && $user !== null) : ?>
                        <!-- Section Mon compte (utilisateurs connectés) -->
                        <li class="sidebar-section-title">Mon compte</li>
                        <li><a href="index.php?page=profile"><i class="fas fa-user-circle"></i> Mon Profil</a></li>
                        <li><a href="index.php?page=privacy"><i class="fas fa-shield-alt"></i> Confidentialité</a></li>
                        <li><a href="index.php?page=logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                        <?php if (!isset($user['user_status']) || $user['user_status'] !== 'BDE') : ?>
                            <li><a href="index.php?page=deleteAccount" class="sidebar-danger"><i class="fas fa-trash-alt"></i> Supprimer mon compte</a></li>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Section Informations -->
                    <li class="sidebar-section-title">Informations</li>
                    <li><a href="index.php?page=legalTerms"><i class="fas fa-file-contract"></i> Mentions légales</a></li>
                    <li><a href="index.php?page=sitemap"><i class="fas fa-sitemap"></i> Plan du site</a></li>

                    <!-- Section Préférences -->
                    <li class="sidebar-section-title">Préférences</li>
                    <li>
                        <button id="dark-mode-toggle-mobile" class="dark-mode-toggle-mobile" aria-label="Basculer le mode sombre">
                            <svg class="dark-mode-icon sun-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="4"></circle>
                                <path d="M12 2v2"></path>
                                <path d="M12 20v2"></path>
                                <path d="m4.93 4.93 1.41 1.41"></path>
                                <path d="m17.66 17.66 1.41 1.41"></path>
                                <path d="M2 12h2"></path>
                                <path d="M20 12h2"></path>
                                <path d="m6.34 17.66-1.41 1.41"></path>
                                <path d="m19.07 4.93-1.41 1.41"></path>
                            </svg>
                            <svg class="dark-mode-icon moon-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20.985 12.486a9 9 0 1 1-9.473-9.472c.405-.022.617.46.402.803a6 6 0 0 0 8.268 8.268c.344-.215.825-.004.803.401"></path>
                            </svg>
                            <span id="dark-mode-text-mobile">Mode sombre</span>
                        </button>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
    <?php endif; ?>

    <!-- Bouton Back to Top -->
    <button id="back-to-top" aria-label="Retour en haut de la page">
        <i class="fas fa-arrow-up" aria-hidden="true"></i>
    </button>

<?php }
?>

<?php
/**
 * Renders the page footer
 *
 * Generates the HTML footer section with navigation links,
 * social media icons, and copyright information.
 * Also handles cookie consent popup display.
 *
 * @author BdeLive Team
 * @version 1.1.0
 * @package BdeLive\Views\Shared
 *
 * @return void
 */
function end_page(): void
{
    ?>
    <footer>
        <div class="footer-inner">
            <div class="footer-left">
                <p>&copy; <?= date("Y") ?> BdeLive - Inform'Aix. Tous droits réservés.</p>
                <nav aria-label="Liens utiles">
                    <ul class="footer-nav">
                        <li><a href="index.php?page=about">À propos</a></li>
                        <li><a href="index.php?page=legalTerms">Mentions légales</a></li>
                        <li><a href="index.php?page=sitemap">Plan du site</a></li>
                    </ul>
                </nav>
            </div>
            <div class="footer-right">
                <div class="social-logos">
                    <h3>Suivez-nous</h3>
                    <a href="https://www.instagram.com/informaix/" target="_blank" rel="noopener noreferrer" class="social-link" aria-label="Instagram - Ouvrir dans un nouvel onglet">
                        <i class="fa-brands fa-instagram"></i>
                    </a>
                    <a href="https://discord.gg/4dXHpN6JCK" target="_blank" rel="noopener noreferrer" class="social-link" aria-label="Discord - Ouvrir dans un nouvel onglet">
                        <i class="fa-brands fa-discord"></i>
                    </a>
                    <a href="https://www.tiktok.com/@informaix" target="_blank" rel="noopener noreferrer" class="social-link" aria-label="TikTok - Ouvrir dans un nouvel onglet">
                        <i class="fa-brands fa-tiktok"></i>
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <!--    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.slim.min.js" defer></script>-->
    <!--    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" defer></script>-->
    
    <!-- CSRF Handler: Must be loaded BEFORE any script that uses fetch() -->
    <script src="./assets/js/csrf-handler.js"></script>
    
    <script src="./assets/js/dark-mode.js"></script>
    <script src="./assets/js/auto-dismiss-alerts.js"></script>
    <script src="./assets/js/slider.js"></script>
    <script src="./assets/js/dropImageArea.js"></script>
    <script src="./assets/js/back-to-top.js"></script>
    <script src="./assets/js/delete-confirm.js"></script>
    <script src="./assets/js/form-submit-protection.js"></script>
    <script src="./assets/js/modal.js"></script>
    <script src="./assets/js/togglePassword.js"></script>
    <script src="./assets/js/passwordControl.js"></script>
    <script src="./assets/js/navbar-scroll.js"></script>
    <script src="https://unpkg.com/easymde/dist/easymde.min.js"></script>

    <!-- Script pour fermer le menu mobile au clic sur un lien -->
    <script src="./app/assets/js/mobile-menu.js"></script>

    <?php
    // Display the cookie popup on all pages (autoload Composer)
    if (class_exists('App\\Modules\\Controllers\\Cookie\\CookieConsentController')) {
        $cls = 'App\\Modules\\Controllers\\Cookie\\CookieConsentController';
        new $cls();
    }
    ?>

    </body>
    </html>
    <?php
}
?>
