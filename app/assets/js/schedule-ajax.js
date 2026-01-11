/**
 * Gestion du chargement AJAX des vues de l'emploi du temps
 * Évite le rechargement complet de la page lors du changement de vue
 */

(function() {
    'use strict';

    // Récupérer les paramètres de base depuis l'URL
    function getBaseParams() {
        const urlParams = new URLSearchParams(window.location.search);
        return {
            page: 'schedule', // Toujours inclure le paramètre page
            year: urlParams.get('year') || '',
            group: urlParams.get('group') || ''
        };
    }

    // Charger une vue via AJAX
    function loadView(view, params = {}) {
        const baseParams = getBaseParams();
        const allParams = {
            ...baseParams,
            ...params,
            view: view,
            action: 'load-view'
        };

        // Afficher un indicateur de chargement
        const calendarWrapper = document.querySelector('.calendar-wrapper');
        if (!calendarWrapper) {
            return;
        }
        
        const loadingIndicator = document.createElement('div');
        loadingIndicator.className = 'schedule-loading';
        loadingIndicator.innerHTML = '<div class="loading-spinner"></div><p>Chargement...</p>';
        loadingIndicator.style.cssText = 'text-align: center; padding: 3rem; color: var(--text-secondary);';
        
        // Sauvegarder le contenu actuel
        const currentContent = calendarWrapper.innerHTML;
        calendarWrapper.innerHTML = '';
        calendarWrapper.appendChild(loadingIndicator);

        // Construire l'URL
        const url = new URL('index.php', window.location.origin);
        Object.keys(allParams).forEach(key => {
            if (allParams[key] !== null && allParams[key] !== undefined && allParams[key] !== '') {
                url.searchParams.set(key, allParams[key]);
            }
        });

        // Requête AJAX
        fetch(url.toString())
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur HTTP: ' + response.status);
                }
                // Vérifier que la réponse est bien du JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    return response.text().then(text => {
                        throw new Error('Réponse non-JSON reçue: ' + text.substring(0, 200));
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.html) {
                    // Remplacer le contenu
                    calendarWrapper.innerHTML = data.html;

                    if (typeof updateCurrentTimeIndicator === 'function') {
                        updateCurrentTimeIndicator();
                    }
                    
                    if (typeof resetModalListeners === 'function') {
                        resetModalListeners();
                    }
                    
                    const newUrl = new URL(window.location.href);
                    Object.keys(allParams).forEach(key => {
                        if (key !== 'action' && allParams[key]) {
                            newUrl.searchParams.set(key, allParams[key]);
                        } else if (key === 'action') {
                            newUrl.searchParams.delete('action');
                        }
                    });
                    window.history.pushState({view: view}, '', newUrl.toString());
                } else {
                    const errorMsg = data.error || 'Erreur lors du chargement de la vue';
                    calendarWrapper.innerHTML = '<div style="text-align: center; padding: 3rem; color: var(--text-error);">' + errorMsg + '</div>';
                }
            })
            .catch(() => {
                calendarWrapper.innerHTML = '<div style="text-align: center; padding: 3rem; color: var(--text-error);">Erreur de connexion</div>';
            });
    }

    // Variable pour stocker le handler de délégation d'événements
    let delegationHandler = null;

    // Initialiser les event listeners avec délégation d'événements
    function initScheduleListeners() {
        const calendarWrapper = document.querySelector('.calendar-wrapper');
        if (!calendarWrapper) {
            return;
        }
        
        // Supprimer l'ancien handler s'il existe
        if (delegationHandler) {
            calendarWrapper.removeEventListener('click', delegationHandler);
        }
        
        // Créer un nouveau handler de délégation d'événements
        delegationHandler = function(e) {
            // View switcher buttons
            const viewBtn = e.target.closest('.view-switcher .view-btn');
            if (viewBtn) {
                e.preventDefault();
                e.stopPropagation();
                const view = viewBtn.getAttribute('data-view');
                if (view) {
                    loadView(view);
                }
                return;
            }
            
            // Navigation buttons (Today, Previous, Next)
            const navBtn = e.target.closest('.calendar-header .nav-btn');
            if (navBtn) {
                e.preventDefault();
                e.stopPropagation();
                const view = navBtn.getAttribute('data-view');
                const params = {};
                
                if (view === 'day') {
                    const date = navBtn.getAttribute('data-date');
                    if (date) params.date = date;
                } else if (view === 'week') {
                    const calyear = navBtn.getAttribute('data-calyear');
                    const week = navBtn.getAttribute('data-week');
                    if (calyear) params.calyear = calyear;
                    if (week) params.week = week;
                } else if (view === 'month') {
                    const calyear = navBtn.getAttribute('data-calyear');
                    const calmonth = navBtn.getAttribute('data-calmonth');
                    if (calyear) params.calyear = calyear;
                    if (calmonth) params.calmonth = calmonth;
                }
                
                if (view) {
                    loadView(view, params);
                }
                return;
            }
        };
        
        // Attacher le handler avec délégation d'événements
        calendarWrapper.addEventListener('click', delegationHandler);
    }

    // Gérer le bouton retour du navigateur
    window.addEventListener('popstate', function(event) {
        const urlParams = new URLSearchParams(window.location.search);
        const view = urlParams.get('view') || 'week';
        const params = {};
        
        if (view === 'day') {
            const date = urlParams.get('date');
            if (date) params.date = date;
        } else if (view === 'week') {
            const calyear = urlParams.get('calyear');
            const week = urlParams.get('week');
            if (calyear) params.calyear = calyear;
            if (week) params.week = week;
        } else if (view === 'month') {
            const calyear = urlParams.get('calyear');
            const calmonth = urlParams.get('calmonth');
            if (calyear) params.calyear = calyear;
            if (calmonth) params.calmonth = calmonth;
        }
        
        loadView(view, params);
    });

    // Initialiser au chargement de la page
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initScheduleListeners);
    } else {
        initScheduleListeners();
    }

})();
