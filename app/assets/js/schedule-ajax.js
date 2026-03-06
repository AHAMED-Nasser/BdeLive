/**
 * Gestion du chargement AJAX des vues de l'emploi du temps.
 * La délégation est posée sur `document` pour rester active après chaque
 * remplacement de contenu AJAX (innerHTML) sans avoir besoin d'être ré-attachée.
 */

(function () {
    'use strict';

    /**
     * Lit les paramètres courants depuis l'URL (year, group et contexte temporel).
     *
     * @returns {Object}
     */
    function getUrlParams()
    {
        const p = new URLSearchParams(window.location.search);
        return {
            page:     'schedule',
            year:     p.get('year')     || '',
            group:    p.get('group')    || '',
            view:     p.get('view')     || 'week',
            date:     p.get('date')     || '',
            week:     p.get('week')     || '',
            calyear:  p.get('calyear')  || '',
            calmonth: p.get('calmonth') || ''
        };
    }

    /**
     * Charge une vue via AJAX et l'injecte dans .calendar-wrapper.
     * Les `overrides` écrasent les params de l'URL courante.
     *
     * @param {string} view      - 'day' | 'week' | 'month'
     * @param {Object} overrides - Paramètres spécifiques à cette navigation
     */
    function loadView(view, overrides = {})
    {
        const calendarWrapper = document.querySelector('.calendar-wrapper');
        if (!calendarWrapper) {
            return;
        }

        // Fusionner URL courante + overrides + action AJAX
        const allParams = Object.assign({}, getUrlParams(), overrides, {
            view:   view,
            action: 'load-view'
        });

        // Sauvegarder la position du scroll avant le chargement
        const savedScrollY = window.scrollY;

        // Fade-out : griser le contenu pendant la requête
        calendarWrapper.classList.remove('view-ready');
        calendarWrapper.classList.add('is-loading');

        // Construire l'URL de la requête
        const url = new URL('index.php', window.location.origin);
        Object.keys(allParams).forEach(function (key) {
            const val = allParams[key];
            if (val !== null && val !== undefined && val !== '') {
                url.searchParams.set(key, val);
            }
        });

        fetch(url.toString())
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }
                const ct = response.headers.get('content-type') || '';
                if (!ct.includes('application/json')) {
                    return response.text().then(function (text) {
                        throw new Error('Réponse non-JSON : ' + text.substring(0, 200));
                    });
                }
                return response.json();
            })
            .then(function (data) {
                if (data.success && data.html) {
                    calendarWrapper.classList.remove('is-loading');
                    calendarWrapper.innerHTML = data.html;

                    // Déclencher le fade-in du nouveau contenu
                    requestAnimationFrame(function () {
                        calendarWrapper.classList.add('view-ready');
                    });

                    // Restaurer la position du scroll exacte
                    window.scrollTo({ top: savedScrollY, behavior: 'instant' });

                    if (typeof resetModalListeners === 'function') {
                        resetModalListeners();
                    }

                    // Mettre à jour l'URL du navigateur sans rechargement
                    const newUrl = new URL(window.location.href);
                    Object.keys(allParams).forEach(function (key) {
                        if (key === 'action') {
                            newUrl.searchParams.delete('action');
                        } else if (allParams[key]) {
                            newUrl.searchParams.set(key, allParams[key]);
                        }
                    });
                    window.history.pushState({ view: view }, '', newUrl.toString());
                } else {
                    calendarWrapper.classList.remove('is-loading');
                    const msg = data.error || 'Erreur lors du chargement de la vue';
                    calendarWrapper.innerHTML =
                        '<div style="text-align:center;padding:3rem;color:var(--color-danger);">' +
                        msg + '</div>';
                }
            })
            .catch(function () {
                calendarWrapper.classList.remove('is-loading');
                calendarWrapper.innerHTML =
                    '<div style="text-align:center;padding:3rem;color:var(--color-danger);">' +
                    'Erreur de connexion</div>';
            });
    }

    /**
     * Délégation posée sur `document`.
     * Active dès le chargement et reste valide après chaque injection AJAX.
     */
    document.addEventListener('click', function (e) {
        // Ne traiter que les clics sur la page emploi du temps
        if (!document.querySelector('.calendar-wrapper')) {
            return;
        }

        // --- Boutons Vue (Jour / Semaine / Mois) ---
        const viewBtn = e.target.closest('.view-switcher .view-btn');
        if (viewBtn) {
            e.preventDefault();
            const view = viewBtn.getAttribute('data-view');
            if (!view) {
                return;
            }

            // Conserver le contexte temporel courant pour la vue cible
            const urlParams = getUrlParams();
            const overrides = {};

            if (view === 'day') {
                // Cibler le jour affiché si dispo, sinon aujourd'hui
                overrides.date = urlParams.date || new Date().toISOString().slice(0, 10);
            } else if (view === 'week') {
                if (urlParams.calyear) {
                    overrides.calyear = urlParams.calyear;
                }
                if (urlParams.week) {
                    overrides.week = urlParams.week;
                }
            } else if (view === 'month') {
                if (urlParams.calyear) {
                    overrides.calyear = urlParams.calyear;
                }
                if (urlParams.calmonth) {
                    overrides.calmonth = urlParams.calmonth;
                }
            }

            loadView(view, overrides);
            return;
        }

        // --- Boutons Navigation (Aujourd'hui / Précédent / Suivant) ---
        const navBtn = e.target.closest('.calendar-header .nav-btn');
        if (navBtn) {
            e.preventDefault();
            const view = navBtn.getAttribute('data-view');
            if (!view) {
                return;
            }

            const overrides = {};

            if (view === 'day') {
                const date = navBtn.getAttribute('data-date');
                if (date) {
                    overrides.date = date;
                }
            } else if (view === 'week') {
                const calyear = navBtn.getAttribute('data-calyear');
                const week    = navBtn.getAttribute('data-week');
                if (calyear) {
                    overrides.calyear = calyear;
                }
                if (week) {
                    overrides.week = week;
                }
            } else if (view === 'month') {
                const calyear  = navBtn.getAttribute('data-calyear');
                const calmonth = navBtn.getAttribute('data-calmonth');
                if (calyear) {
                    overrides.calyear = calyear;
                }
                if (calmonth) {
                    overrides.calmonth = calmonth;
                }
            }

            loadView(view, overrides);
        }
    });

    // Bouton retour/avance du navigateur
    window.addEventListener('popstate', function () {
        const p    = getUrlParams();
        const view = p.view || 'week';

        const overrides = {};
        if (view === 'day' && p.date)         { overrides.date = p.date; }
        if (view === 'week') {
            if (p.calyear) { overrides.calyear = p.calyear; }
            if (p.week)    { overrides.week    = p.week; }
        }
        if (view === 'month') {
            if (p.calyear)  { overrides.calyear  = p.calyear; }
            if (p.calmonth) { overrides.calmonth = p.calmonth; }
        }

        loadView(view, overrides);
    });

    // Exposer pour schedule-filters.js
    window.scheduleLoadView = loadView;

})();
