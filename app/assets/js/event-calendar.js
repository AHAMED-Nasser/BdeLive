/**
 * Event Calendar - FullCalendar Implementation
 * Gestion du calendrier des événements avec FullCalendar
 *
 * Ce fichier gère l'initialisation et le comportement du calendrier des événements.
 * Il inclut des corrections d'accessibilité pour les alertes WAVE.
 */

(function () {
    'use strict';

    /**
     * Initialise le calendrier des événements
     * @param {string} containerId - L'ID du conteneur du calendrier
     * @param {Array} events - La liste des événements à afficher
     */
    function initEventCalendar(containerId, events)
    {
        const calendarEl = document.getElementById(containerId);

        if (!calendarEl) {
            console.warn('EventCalendar: Element with id "' + containerId + '" not found');
            return;
        }

        // Détection mobile
        const isMobile = window.innerWidth <= 767;

        // Configuration responsive de la toolbar
        const getHeaderToolbar = function () {
            if (isMobile) {
                return {
                    left: 'prev,next',
                    center: 'title',
                    right: ''
                };
            }
            return {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek'
            };
        };

        // Vue initiale responsive
        const getInitialView = function () {
            return 'dayGridMonth';
        };

        // Créer le calendrier
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: getInitialView(),
            locale: 'fr',
            headerToolbar: getHeaderToolbar(),
            buttonText: {
                today: "Aujourd'hui",
                month: 'Mois',
                week: 'Semaine',
                prev: "Précédent",
                next: "Suivant"
            },
            height: 'auto',
            contentHeight: 'auto',
            aspectRatio: isMobile ? 1.2 : 1.5,
            events: events,
            eventClick: function (info) {
                info.jsEvent.preventDefault();

                // Sur mobile : afficher une modal avec les détails
                // Sur PC : rediriger vers la page de détails
                if (isMobile) {
                    const eventTitle = info.event.title;
                    const eventStart = info.event.start;
                    const eventUrl = info.event.url;
                    showEventModal(eventTitle, eventStart, eventUrl);
                } else {
                    if (info.event.url) {
                        window.location.href = info.event.url;
                    }
                }
            },
            // Désactiver le popover/tooltip par défaut qui agrandit les cases
            eventDisplay: 'block',
            dayMaxEvents: true,
            moreLinkClick: 'popover',

            // ==============================================
            // FIX ACCESSIBILITÉ WAVE : Layout Tables
            // Callback après le rendu de chaque vue
            // ==============================================
            viewDidMount: function (info) {
                // Appliquer les corrections d'accessibilité après le rendu complet
                fixCalendarAccessibility(calendarEl);
            }
        });

        calendar.render();

        // Appliquer les corrections immédiatement après le premier rendu
        setTimeout(function () {
            fixCalendarAccessibility(calendarEl);
        }, 100);

        // Vérification et correction des dimensions sur mobile
        setTimeout(function () {
            handleMobileLayout(calendar, calendarEl, isMobile);
        }, 500);

        // Ajouter un menu de sélection de vue sur mobile
        if (isMobile) {
            addMobileViewSelector(calendar, calendarEl);
        }

        // Adapter la toolbar lors du redimensionnement
        handleWindowResize(calendar, isMobile);

        return calendar;
    }

    /**
     * Corrige les problèmes d'accessibilité du calendrier FullCalendar
     * @param {HTMLElement} calendarEl - L'élément du calendrier
     */
    function fixCalendarAccessibility(calendarEl)
    {
        // Attendre que FullCalendar ait fini de rendre le calendrier
        setTimeout(function () {
            // ==============================================
            // FIX WAVE: Corriger les rôles ARIA des tables
            // ==============================================
            // FullCalendar génère des tables complexes qui mélangent tables de layout
            // et grilles interactives. WAVE signale un conflit quand des cellules
            // avec role="gridcell" sont dans des tables sans role="grid".

            // 1. PREMIÈRE ÉTAPE : Trouver les tables qui contiennent des gridcells
            //    et leur donner role="grid" (ce sont des grilles interactives)
            const gridCells = calendarEl.querySelectorAll('[role="gridcell"]');
            const tablesWithGridCells = new Set();

            gridCells.forEach(function (cell) {
                // Trouver la table parente la plus proche
                const parentTable = cell.closest('table');
                if (parentTable) {
                    tablesWithGridCells.add(parentTable);
                    // Cette table contient des gridcells, elle DOIT avoir role="grid"
                    parentTable.setAttribute('role', 'grid');
                }
            });

            // 2. DEUXIÈME ÉTAPE : Traiter TOUTES les tables qui ne sont PAS des grilles
            //    en leur donnant role="presentation" (tables de layout)
            const allTables = calendarEl.querySelectorAll('table');
            allTables.forEach(function (table) {
                // Si la table contient des gridcells, elle a déjà role="grid", on ne touche pas
                if (tablesWithGridCells.has(table)) {
                    return;
                }

                // Vérifier si la table a déjà un rôle ARIA grid
                const currentRole = table.getAttribute('role');
                if (currentRole === 'grid') {
                    return;
                }

                // Pour toutes les autres tables, ajouter role="presentation"
                table.setAttribute('role', 'presentation');
            });

            // 3. S'assurer que les <th> ont le bon scope pour l'accessibilité
            const thElements = calendarEl.querySelectorAll('th');
            thElements.forEach(function (th) {
                if (!th.hasAttribute('scope')) {
                    th.setAttribute('scope', 'col');
                }
            });

            console.log('EventCalendar: Accessibility fixes applied to', allTables.length, 'tables (', tablesWithGridCells.size, 'grids,', (allTables.length - tablesWithGridCells.size), 'presentations)');
        }, 50);

        // Observer les changements du DOM pour appliquer les corrections aux nouveaux éléments
        const observer = new MutationObserver(function (mutations) {
            let needsReapply = false;
            mutations.forEach(function (mutation) {
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    mutation.addedNodes.forEach(function (node) {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            // Vérifier si des tables ont été ajoutées
                            const newTables = node.querySelectorAll ? node.querySelectorAll('table') : [];
                            if (newTables.length > 0 || (node.tagName === 'TABLE')) {
                                needsReapply = true;
                            }
                        }
                    });
                }
            });

            if (needsReapply) {
                // Réappliquer les corrections après un court délai pour laisser FullCalendar finir
                setTimeout(function () {
                    fixCalendarAccessibility(calendarEl);
                }, 50);
            }
        });

        observer.observe(calendarEl, {
            childList: true,
            subtree: true
        });
    }

    /**
     * Gère la mise en page sur mobile
     * @param {FullCalendar.Calendar} calendar - L'instance du calendrier
     * @param {HTMLElement} calendarEl - L'élément du calendrier
     * @param {boolean} isMobile - Si l'appareil est mobile
     */
    function handleMobileLayout(calendar, calendarEl, isMobile)
    {
        const fcElement = document.querySelector('.fc');

        if (isMobile && fcElement) {
            const actualWidth = fcElement.offsetWidth;
            const expectedMinWidth = window.innerWidth - 30;
            const dayCells = document.querySelectorAll('.fc-daygrid-day');
            const firstDayCellWidth = dayCells.length > 0 ? dayCells[0].offsetWidth : null;
            const fcStylesLoaded = fcElement && window.getComputedStyle(fcElement).display !== 'none';
            const cellsTooSmall = firstDayCellWidth && firstDayCellWidth < 40;
            const widthIssue = actualWidth < expectedMinWidth;

            if (widthIssue || !fcStylesLoaded || cellsTooSmall) {
                // Forcer le re-render
                calendar.updateSize();

                // Appliquer des styles inline de secours
                applyMobileFallbackStyles();

                // Forcer le recalcul des dimensions après l'application des styles
                setTimeout(function () {
                    calendar.updateSize();
                    const containerEl = calendarEl.parentElement;
                    if (containerEl) {
                        containerEl.style.width = '100%';
                        containerEl.style.maxWidth = '100%';
                        containerEl.style.margin = '15px 0';
                    }
                    calendarEl.style.width = '100%';
                    calendarEl.style.maxWidth = '100%';
                    setTimeout(function () {
                        calendar.updateSize();
                    }, 100);
                }, 200);
            }
        }
    }

    /**
     * Applique les styles de secours pour mobile
     */
    function applyMobileFallbackStyles()
    {
        if (document.getElementById('fc-mobile-fallback-styles')) {
            return;
        }

        const style = document.createElement('style');
        style.id = 'fc-mobile-fallback-styles';

        style.textContent = '\
            @media (max-width: 767px) {\
                .event-calendar-container {\
                    width: 100% !important;\
                    max-width: 100% !important;\
                    margin: 15px 0 !important;\
                    padding: 8px !important;\
                    box-sizing: border-box !important;\
                }\
                .event-calendar-container * {\
                    box-sizing: border-box !important;\
                }\
                #event-calendar {\
                    width: 100% !important;\
                    max-width: 100% !important;\
                    box-sizing: border-box !important;\
                }\
                .fc {\
                    width: 100% !important;\
                    max-width: 100% !important;\
                    box-sizing: border-box !important;\
                }\
                .fc-view-harness {\
                    width: 100% !important;\
                    max-width: 100% !important;\
                }\
                .fc-scrollgrid {\
                    width: 100% !important;\
                    max-width: 100% !important;\
                    box-sizing: border-box !important;\
                }\
                .fc-scrollgrid-sync-table {\
                    width: 100% !important;\
                    table-layout: fixed !important;\
                }\
                .fc-dayGridMonth-view .fc-daygrid-day {\
                    min-height: 75px !important;\
                    width: 14.28% !important;\
                    min-width: 40px !important;\
                    box-sizing: border-box !important;\
                }\
                .fc-dayGridMonth-view .fc-event {\
                    font-size: 0.7rem !important;\
                    padding: 5px 6px !important;\
                    max-height: 22px !important;\
                    line-height: 1.5 !important;\
                }\
            }\
        ';

        document.head.appendChild(style);
    }

    /**
     * Affiche une modal d'événement sur mobile
     * @param {string} title - Le titre de l'événement
     * @param {Date} start - La date de début
     * @param {string} url - L'URL de l'événement
     */
    function showEventModal(title, start, url)
    {
        // Créer la modal si elle n'existe pas
        let modal = document.getElementById('event-mobile-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'event-mobile-modal';
            modal.className = 'event-mobile-modal';
            modal.setAttribute('role', 'dialog');
            modal.setAttribute('aria-modal', 'true');
            modal.setAttribute('aria-labelledby', 'event-modal-title');
            document.body.appendChild(modal);
        }

        // Formater la date
        const date = new Date(start);
        const dateStr = date.toLocaleDateString('fr-FR', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

        // Échapper le HTML pour éviter les injections XSS
        const escapeHtml = function (text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        };

        // Contenu de la modal
        modal.innerHTML = '\
            <div class="event-mobile-modal-overlay" aria-hidden="true"></div>\
            <div class="event-mobile-modal-content">\
                <div class="event-mobile-modal-header">\
                    <h3 class="event-mobile-modal-title" id="event-modal-title">' + escapeHtml(title) + '</h3>\
                    <button class="event-mobile-modal-close" aria-label="Fermer la fenêtre" type="button">×</button>\
                </div>\
                <p class="event-mobile-modal-date">' + escapeHtml(dateStr) + '</p>\
                <div class="event-mobile-modal-actions">\
                    <a href="' + escapeHtml(url) + '" class="event-mobile-modal-btn event-mobile-modal-btn-primary">Voir les détails</a>\
                </div>\
            </div>\
        ';

        // Afficher la modal
        modal.classList.add('active');
        document.body.classList.add('modal-open');
        document.documentElement.classList.add('modal-open');

        // Focus sur le bouton de fermeture pour l'accessibilité
        const closeBtn = modal.querySelector('.event-mobile-modal-close');
        if (closeBtn) {
            closeBtn.focus();
        }

        // Fermer la modal
        const closeModal = function () {
            modal.classList.remove('active');
            document.body.classList.remove('modal-open');
            document.documentElement.classList.remove('modal-open');
        };

        modal.querySelectorAll('.event-mobile-modal-close').forEach(function (btn) {
            btn.addEventListener('click', closeModal);
        });

        const overlay = modal.querySelector('.event-mobile-modal-overlay');
        overlay.addEventListener('click', closeModal);

        // Fermer avec Escape
        const handleEscape = function (e) {
            if (e.key === 'Escape') {
                closeModal();
                document.removeEventListener('keydown', handleEscape);
            }
        };
        document.addEventListener('keydown', handleEscape);

        // Empêcher le scroll tactile sur l'overlay
        overlay.addEventListener('touchmove', function (e) {
            e.preventDefault();
        }, { passive: false });
    }

    /**
     * Ajoute un sélecteur de vue sur mobile
     * @param {FullCalendar.Calendar} calendar - L'instance du calendrier
     * @param {HTMLElement} calendarEl - L'élément du calendrier
     */
    function addMobileViewSelector(calendar, calendarEl)
    {
        const toolbar = calendarEl.querySelector('.fc-toolbar');
        if (!toolbar) {
            return;
        }

        const viewSelector = document.createElement('div');
        viewSelector.className = 'fc-view-selector-mobile';
        viewSelector.setAttribute('role', 'group');
        viewSelector.setAttribute('aria-label', 'Sélection de la vue du calendrier');
        viewSelector.style.cssText = 'display: flex; gap: 0.5rem; margin-top: 0.5rem; justify-content: center; flex-wrap: wrap; padding: 0.5rem;';

        const views = [
            { key: 'dayGridMonth', label: 'Mois' },
            { key: 'timeGridWeek', label: 'Semaine' }
        ];

        views.forEach(function (view) {
            const btn = document.createElement('button');
            btn.className = 'fc-button fc-button-primary';
            btn.textContent = view.label;
            btn.setAttribute('type', 'button');
            btn.setAttribute('aria-pressed', calendar.view.type === view.key ? 'true' : 'false');
            btn.style.cssText = 'padding: 0.4rem 0.8rem; font-size: 0.8rem; border-radius: 4px; cursor: pointer;';

            if (calendar.view.type === view.key) {
                btn.classList.add('fc-button-active');
            }

            btn.addEventListener('click', function () {
                calendar.changeView(view.key);
                viewSelector.querySelectorAll('button').forEach(function (b) {
                    b.classList.remove('fc-button-active');
                    b.setAttribute('aria-pressed', 'false');
                });
                btn.classList.add('fc-button-active');
                btn.setAttribute('aria-pressed', 'true');

                // Réappliquer les corrections d'accessibilité après le changement de vue
                fixCalendarAccessibility(calendarEl);
            });

            viewSelector.appendChild(btn);
        });

        toolbar.appendChild(viewSelector);
    }

    /**
     * Gère le redimensionnement de la fenêtre
     * @param {FullCalendar.Calendar} calendar - L'instance du calendrier
     * @param {boolean} initialIsMobile - Si l'appareil était mobile à l'initialisation
     */
    function handleWindowResize(calendar, initialIsMobile)
    {
        let resizeTimer;
        window.addEventListener('resize', function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function () {
                const newIsMobile = window.innerWidth <= 767;
                if (newIsMobile !== initialIsMobile) {
                    location.reload();
                } else if (calendar) {
                    calendar.updateSize();
                }
            }, 250);
        });
    }

    // Exposer la fonction d'initialisation globalement
    window.EventCalendar = {
        init: initEventCalendar
    };
})();
