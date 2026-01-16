/**
 * Dark Mode Manager
 *
 * Gère le mode sombre avec :
 * - Détection automatique des préférences système
 * - Stockage dans localStorage
 * - Toggle manuel via bouton
 *
 * Priorité : localStorage > préférence système > mode clair
 *
 * @author BdeLive Team
 * @version 1.0.0
 */

(function () {
    'use strict';

    const DARK_MODE_KEY = 'darkMode';
    const DARK_MODE_CLASS = 'dark-mode';
    const LIGHT_MODE_CLASS = 'light-mode';

    /**
     * Détecte si le système est en mode sombre
     * @returns {boolean}
     */
    function isSystemDarkMode()
    {
        return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    }

    /**
     * Récupère la préférence depuis localStorage
     * @returns {string|null} 'true', 'false', ou null
     */
    function getStoredPreference()
    {
        try {
            return localStorage.getItem(DARK_MODE_KEY);
        } catch (e) {
            return null;
        }
    }

    /**
     * Sauvegarde la préférence dans localStorage
     * @param {boolean} isDark
     */
    function savePreference(isDark)
    {
        try {
            localStorage.setItem(DARK_MODE_KEY, isDark ? 'true' : 'false');
        } catch (e) {
            // localStorage non disponible, ignorer silencieusement
        }
    }

    /**
     * Applique le mode sombre ou clair
     * @param {boolean} isDark
     */
    function applyDarkMode(isDark)
    {
        const html = document.documentElement;

        if (isDark) {
            html.classList.add(DARK_MODE_CLASS);
            html.classList.remove(LIGHT_MODE_CLASS);
        } else {
            html.classList.remove(DARK_MODE_CLASS);
            html.classList.add(LIGHT_MODE_CLASS);
        }

        // Mettre à jour l'icône immédiatement
        updateToggleIcon();
    }

    /**
     * Détermine le mode à appliquer selon la priorité
     * @returns {boolean}
     */
    function determineDarkMode()
    {
        const stored = getStoredPreference();

        // Priorité 1 : localStorage
        if (stored !== null) {
            return stored === 'true';
        }

        // Priorité 2 : Préférence système
        return isSystemDarkMode();
    }

    /**
     * Initialise le mode sombre au chargement
     * Cette fonction doit être appelée dans le <head> pour éviter le FOUC
     */
    function initDarkMode()
    {
        const isDark = determineDarkMode();
        applyDarkMode(isDark);
    }

    /**
     * Toggle le mode sombre manuellement
     */
    function toggleDarkMode()
    {
        const html = document.documentElement;
        const isCurrentlyDark = html.classList.contains(DARK_MODE_CLASS);
        const newMode = !isCurrentlyDark;

        applyDarkMode(newMode);
        savePreference(newMode);
    }

    /**
     * Écoute les changements de préférence système
     */
    function watchSystemPreference()
    {
        if (window.matchMedia) {
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

            mediaQuery.addEventListener('change', function (e) {
                // Ne changer que si l'utilisateur n'a pas fait de choix manuel
                const stored = getStoredPreference();
                if (stored === null) {
                    applyDarkMode(e.matches);
                }
            });
        }
    }

    /**
     * Met à jour l'icône du bouton toggle
     */
    function updateToggleIcon()
    {
        const toggle = document.getElementById('dark-mode-toggle');
        const toggleMobile = document.getElementById('dark-mode-toggle-mobile');
        const textMobile = document.getElementById('dark-mode-text-mobile');

        if (!toggle && !toggleMobile) {
            return;
        }

        const isDark = document.documentElement.classList.contains(DARK_MODE_CLASS);

        // Mettre à jour le toggle desktop
        if (toggle) {
            const sunIcon = toggle.querySelector('.sun-icon');
            const moonIcon = toggle.querySelector('.moon-icon');
            if (sunIcon && moonIcon) {
                sunIcon.style.removeProperty('display');
                moonIcon.style.removeProperty('display');
            }
        }

        // Mettre à jour le toggle mobile
        if (toggleMobile) {
            const sunIcon = toggleMobile.querySelector('.sun-icon');
            const moonIcon = toggleMobile.querySelector('.moon-icon');
            if (sunIcon && moonIcon) {
                sunIcon.style.removeProperty('display');
                moonIcon.style.removeProperty('display');
            }

            // Mettre à jour le texte et l'aria-label
            if (isDark) {
                toggleMobile.setAttribute('aria-label', 'Basculer en mode clair');
                if (textMobile) {
                    textMobile.textContent = 'Mode clair';
                }
            } else {
                toggleMobile.setAttribute('aria-label', 'Basculer en mode sombre');
                if (textMobile) {
                    textMobile.textContent = 'Mode sombre';
                }
            }
        }
    }

    // Observer les changements de classe pour mettre à jour l'icône
    const observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                updateToggleIcon();
            }
        });
    });

    if (document.documentElement) {
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class']
        });
    }

    /**
     * Initialise le bouton toggle
     */
    function initToggleButton()
    {
        const toggle = document.getElementById('dark-mode-toggle');
        const toggleMobile = document.getElementById('dark-mode-toggle-mobile');

        if (toggle) {
            toggle.addEventListener('click', function (e) {
                e.preventDefault();
                toggleDarkMode();
                updateToggleIcon();
            });
        }

        if (toggleMobile) {
            toggleMobile.addEventListener('click', function (e) {
                e.preventDefault();
                toggleDarkMode();
                updateToggleIcon();
            });
        }

        updateToggleIcon();
    }

    // Initialisation au chargement du DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            initDarkMode();
            watchSystemPreference();
            initToggleButton();
        });
    } else {
        initDarkMode();
        watchSystemPreference();
        initToggleButton();
    }

    // Expose les fonctions publiques
    window.DarkMode = {
        toggle: toggleDarkMode,
        init: initDarkMode,
        isDark: function () {
            return document.documentElement.classList.contains(DARK_MODE_CLASS);
        }
    };

})();

