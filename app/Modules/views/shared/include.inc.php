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
        <meta name="description"
            content="Site officiel du BDE Inform'Aix - BDE Informatique à Aix-en-Provence. Découvrez nos événements, avantages étudiants et réseaux sociaux.">

        <!-- CSRF Token for JavaScript AJAX requests -->
        <?php
        $csrf = Application::getInstance()->csrf();
        ?>
        <meta name="csrf-token" content="<?= htmlspecialchars($csrf->getToken(), ENT_QUOTES, 'UTF-8') ?>">

        <link rel="icon" href="./assets/img/logo.png">

        <!-- Anti-FOUC: Script inline pour détection immédiate du mode sombre -->
        <script>
            (function () {
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
        <link rel="stylesheet" href="./assets/css/base/markdown.css">
        <link rel="stylesheet" href="./assets/css/base/style.css">
        <link rel="stylesheet" href="./assets/css/layout/footer.css">
        <link rel="stylesheet" href="./assets/css/layout/navbar.css">

        <!-- CSS Pages -->
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
        <link rel="stylesheet" href="./assets/css/components/badge.css">
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
                                        <svg class="dark-mode-icon sun-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                                        <svg class="dark-mode-icon moon-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path
                                                d="M20.985 12.486a9 9 0 1 1-9.473-9.472c.405-.022.617.46.402.803a6 6 0 0 0 8.268 8.268c.344-.215.825-.004.803.401">
                                            </path>
                                        </svg>
                                    </button>
                                </li>
                                <!-- Profile dropdown menu -->
                                <li class="profile-dropdown-container">
                                    <input type="checkbox" id="profile-dropdown-toggle" class="profile-dropdown-toggle">
                                    <label for="profile-dropdown-toggle" class="profile-dropdown-trigger">
                                        <span class="visually-hidden">Ouvrir le menu de profil</span>
                                        <i class="fas fa-user" aria-hidden="true"></i>
                                        <?php
                                        $displayName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
                                        echo htmlspecialchars($displayName ?: 'Mon Profil');
                                        ?>
                                        <i class="fas fa-chevron-down dropdown-arrow" aria-hidden="true"></i>
                                    </label>
                                    <div class="profile-dropdown-menu">
                                        <a href="index.php?page=profile"><i class="fas fa-id-card"></i> Mon Profil</a>
                                        <a href="index.php?page=privacy"><i class="fas fa-shield-alt"></i> Confidentialité</a>
                                        <?php if ($isAdmin) : ?>
                                            <a href="index.php?page=createEvent"><i class="fas fa-plus-circle"></i> Créer un
                                                événement</a>
                                            <a href="index.php?page=createArticle"><i class="fas fa-edit"></i> Créer un article</a>
                                            <div class="dropdown-divider"></div>
                                            <a href="index.php?page=adminSection"><i class="fas fa-cogs"></i> Administration</a>
                                        <?php endif; ?>
                                        <a href="index.php?page=logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
                                        <a href="index.php?page=deleteAccount" class="dropdown-danger"><i
                                                class="fas fa-trash-alt"></i>
                                            Supprimer mon compte</a>
                                    </div>
                                </li>
                            <?php elseif (isset($user) && $user !== null) : ?>
                                <li><a href="index.php?page=schedule">Emploi du temps</a></li>
                                <!-- Dark Mode Toggle -->
                                <li>
                                    <button id="dark-mode-toggle" class="dark-mode-toggle" aria-label="Basculer le mode sombre">
                                        <svg class="dark-mode-icon sun-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                                        <svg class="dark-mode-icon moon-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path
                                                d="M20.985 12.486a9 9 0 1 1-9.473-9.472c.405-.022.617.46.402.803a6 6 0 0 0 8.268 8.268c.344-.215.825-.004.803.401">
                                            </path>
                                        </svg>
                                    </button>
                                </li>
                                <!-- Profile dropdown menu -->
                                <li class="profile-dropdown-container">
                                    <input type="checkbox" id="profile-dropdown-toggle" class="profile-dropdown-toggle">
                                    <label for="profile-dropdown-toggle" class="profile-dropdown-trigger">
                                        <span class="visually-hidden">Ouvrir le menu de profil</span>
                                        <i class="fas fa-user" aria-hidden="true"></i>
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
                                        <a href="index.php?page=deleteAccount" class="dropdown-danger"><i
                                                class="fas fa-trash-alt"></i>
                                            Supprimer mon compte</a>
                                    </div>
                                </li>
                            <?php else : ?>
                                <li><a href="index.php?page=login">Connexion</a></li>
                                <li><a href="index.php?page=register">Inscription</a></li>
                                <!-- Dark Mode Toggle -->
                                <li>
                                    <button id="dark-mode-toggle" class="dark-mode-toggle" aria-label="Basculer le mode sombre">
                                        <svg class="dark-mode-icon sun-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                                        <svg class="dark-mode-icon moon-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path
                                                d="M20.985 12.486a9 9 0 1 1-9.473-9.472c.405-.022.617.46.402.803a6 6 0 0 0 8.268 8.268c.344-.215.825-.004.803.401">
                                            </path>
                                        </svg>
                                    </button>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <!-- Hamburger menu -->
                    <input type="checkbox" id="menu-toggle" class="menu-toggle">
                    <label for="menu-toggle" class="hamburger-icon">
                        <span class="visually-hidden">Ouvrir ou fermer le menu de navigation</span>
                        <span class="bar"></span>
                        <span class="bar"></span>
                        <span class="bar"></span>
                    </label>

                    <!-- Overlay to close the menu -->
                    <button type="button" class="sidebar-overlay" aria-label="Fermer le menu"
                        onclick="document.getElementById('menu-toggle').checked = false"></button>

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
                                <!-- Section Administration (BDE only) -->
                                <li class="sidebar-section-title">Administration</li>
                                <li><a href="index.php?page=createEvent"><i class="fas fa-plus-circle"></i> Créer un événement</a>
                                </li>
                                <li><a href="index.php?page=createArticle"><i class="fas fa-edit"></i> Créer un article</a></li>
                                <li><a href="index.php?page=adminSection"><i class="fas fa-cogs"></i> Administration</a></li>
                            <?php endif; ?>

                            <?php if (!isset($user)) : ?>
                                <!-- Section Authentication (not connected) -->
                                <li class="sidebar-section-title">Connexion</li>
                                <li><a href="index.php?page=login"><i class="fas fa-sign-in-alt"></i> Connexion</a></li>
                                <li><a href="index.php?page=register"><i class="fas fa-user-plus"></i> Inscription</a></li>
                            <?php endif; ?>

                            <?php if (isset($user) && $user !== null) : ?>
                                <!-- Section My account (connected users) -->
                                <li class="sidebar-section-title">Mon compte</li>
                                <li><a href="index.php?page=profile"><i class="fas fa-user-circle"></i> Mon Profil</a></li>
                                <li><a href="index.php?page=privacy"><i class="fas fa-shield-alt"></i> Confidentialité</a></li>
                                <li><a href="index.php?page=logout"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                                <?php if (!isset($user['user_status']) || $user['user_status'] !== 'BDE') : ?>
                                    <li><a href="index.php?page=deleteAccount" class="sidebar-danger"><i class="fas fa-trash-alt"></i>
                                            Supprimer mon compte</a></li>
                                <?php endif; ?>
                            <?php endif; ?>

                            <!-- Section Information -->
                            <li class="sidebar-section-title">Informations</li>
                            <li><a href="index.php?page=legalTerms"><i class="fas fa-file-contract"></i> Mentions légales</a>
                            </li>
                            <li><a href="index.php?page=sitemap"><i class="fas fa-sitemap"></i> Plan du site</a></li>

                            <!-- Section Preferences -->
                            <li class="sidebar-section-title">Préférences</li>
                            <li>
                                <button id="dark-mode-toggle-mobile" class="dark-mode-toggle-mobile"
                                    aria-label="Basculer le mode sombre">
                                    <svg class="dark-mode-icon sun-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                                    <svg class="dark-mode-icon moon-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path
                                            d="M20.985 12.486a9 9 0 1 1-9.473-9.472c.405-.022.617.46.402.803a6 6 0 0 0 8.268 8.268c.344-.215.825-.004.803.401">
                                        </path>
                                    </svg>
                                    <span id="dark-mode-text-mobile">Mode sombre</span>
                                </button>
                            </li>
                        </ul>
                    </div>
                </nav>
            </header>
        <?php endif; ?>

        <!-- Back to Top button -->
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
                    <p class="footer-copyright">&copy; <?= date("Y") ?> BdeLive - Inform'Aix. Tous droits réservés.</p>
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
                        <h3 class="social-logos-title">Suivez-nous</h3>
                        <a href="https://www.instagram.com/informaix/" target="_blank" rel="noopener noreferrer"
                            class="social-link" aria-label="Instagram - Ouvrir dans un nouvel onglet">
                            <svg class="social-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path
                                    d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"
                                    fill="currentColor" />
                            </svg>
                        </a>
                        <a href="https://discord.gg/4dXHpN6JCK" target="_blank" rel="noopener noreferrer"
                            class="social-link" aria-label="Discord - Ouvrir dans un nouvel onglet">
                            <svg class="social-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path
                                    d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994a.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"
                                    fill="currentColor" />
                            </svg>
                        </a>
                        <a href="https://www.tiktok.com/@informaix" target="_blank" rel="noopener noreferrer"
                            class="social-link" aria-label="TikTok - Ouvrir dans un nouvel onglet">
                            <svg class="social-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path
                                    d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1.04-.1z"
                                    fill="currentColor" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </footer>

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
        <script src="./assets/js/markdownEditor.js"></script>

        <!-- Script pour fermer le menu mobile au clic sur un lien -->
        <script src="./assets/js/mobile-menu.js"></script>

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
