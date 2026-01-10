<?php
/**
 * @var \App\Core\Security\CsrfProtection $csrf
 * @var array<string, mixed>|null $user
 * @var array<string, string|null> $flash
 * @var array<int, array<string, mixed>> $events La liste des événements (passée par EventController)
 * @var \App\Modules\Helpers\Pagination $pagination L'objet pagination (passé par EventController)
 */
start_page('Liste des Événements', true, $user ?? null);

$viewMode = $viewMode ?? 'list'; ?>

<div class="event-view-toggle-container">
    <div class="event-view-toggle-buttons">
        <a href="index.php?page=event&view=list" 
           class="event-view-btn <?= $viewMode === 'list' ? 'active' : '' ?>"
           aria-label="Vue liste">
            <i class="fas fa-list"></i>
            <span>Liste</span>
        </a>
        <a href="index.php?page=event&view=calendar" 
           class="event-view-btn <?= $viewMode === 'calendar' ? 'active' : '' ?>"
           aria-label="Vue calendrier">
            <i class="fas fa-calendar-alt"></i>
            <span>Calendrier</span>
        </a>
    </div>
</div>
<style>
    /* Boutons Liste/Calendrier - Style desktop (par défaut) */
    .event-view-toggle-container {
        margin: 20px auto;
        text-align: center;
        max-width: 400px;
    }

    .event-view-toggle-buttons {
        display: inline-flex;
        gap: 0;
        background: var(--bg-secondary);
        padding: 4px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .event-view-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 20px;
        background: transparent;
        color: var(--text-secondary);
        text-decoration: none;
        border-radius: 6px;
        font-size: 1rem;
        font-weight: 500;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        position: relative;
    }

    .event-view-btn i {
        font-size: 1rem;
    }

    .event-view-btn:hover {
        background: var(--bg-hover);
        color: var(--text-primary);
        text-decoration: none !important;
    }

    .event-view-btn.active {
        background: var(--color-primary);
        color: var(--text-inverse);
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        font-weight: 600;
    }

    .event-view-btn.active:hover {
        background: var(--color-primary-hover);
        color: var(--text-inverse);
    }

    .event-view-btn.active i {
        color: var(--text-inverse);
    }

    /* Mode téléphone - Améliorations */
    @media (max-width: 767px) {
        .event-view-toggle-container {
            margin: 1rem auto;
            padding: 0 1rem;
            max-width: 100%;
        }

        .event-view-toggle-buttons {
            display: flex;
            width: 100%;
            max-width: 500px;
            gap: 0.75rem;
            background: var(--bg-secondary);
            padding: 0.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .event-view-btn {
            flex: 1;
            flex-direction: column;
            gap: 0.4rem;
            padding: 0.75rem 1rem;
            min-height: 65px;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            background: var(--bg-card);
            color: var(--text-secondary);
            border: 2px solid transparent;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            text-decoration: none !important;
        }

        .event-view-btn i {
            font-size: 1.2rem;
            margin-bottom: 0.2rem;
        }

        .event-view-btn span {
            font-size: 0.85rem;
        }

        .event-view-btn:hover {
            background: var(--bg-hover);
            color: var(--text-primary);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            text-decoration: none !important;
        }

        .event-view-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: var(--text-inverse);
            border-color: rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 16px rgba(102, 126, 234, 0.4);
            transform: translateY(-2px);
        }

        .event-view-btn.active:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
            text-decoration: none !important;
        }

        .event-view-btn.active i {
            color: var(--text-inverse);
            transform: scale(1.1);
        }
    }

    /* Très petits écrans */
    @media (max-width: 480px) {
        .event-view-toggle-container {
            padding: 0 0.75rem;
            margin: 0.75rem auto;
        }

        .event-view-toggle-buttons {
            gap: 0.5rem;
            padding: 0.4rem;
        }

        .event-view-btn {
            padding: 0.6rem 0.8rem;
            min-height: 60px;
            font-size: 0.8rem;
            text-decoration: none !important;
        }

        .event-view-btn i {
            font-size: 1.1rem;
        }

        .event-view-btn span {
            font-size: 0.75rem;
        }
    }

    /* Style pour les liens "Voir les détails et s'inscrire" */
    .btn-more {
        text-decoration: none !important;
        transition: all 0.3s ease;
    }

    .btn-more:hover {
        text-decoration: none !important;
        opacity: 0.8;
        transform: translateX(4px);
    }
</style>
<?php

// Les variables $events et $pagination sont définies par EventController

// Repository pour vérifier les inscriptions
$registrationRepo = new \App\Modules\Repositories\EventRegistrationRepository();
$userId = $user['user_id'] ?? null;
?>

<div class="container event-list">
    <h1 style="text-align: center;">Nos Événements</h1>

    <?php foreach (['success' => '#d4edda', 'error' => '#f8d7da'] as $type => $color) : ?>
        <?php if (!empty($flash[$type])) : ?>
            <div style="background-color: <?= $color ?>; color: #<?= $type === 'success' ? '155724' : '721c24' ?>; padding: 12px; margin: 20px 0; border: 1px solid #<?= $type === 'success' ? 'c3e6cb' : 'f5c6cb' ?>; border-radius: 4px; text-align: center;">
                <?= htmlspecialchars($flash[$type]) ?>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <?php if (empty($events)) : ?>
        <p style="text-align: center; margin-top: 50px;">Aucun événement à afficher pour le moment.</p>

    <?php elseif ($viewMode === 'calendar') : ?>
        <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' />
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
        <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/locales/fr.global.min.js'></script>
        <link rel="stylesheet" href="/assets/css/schedule.css">
        <style>
            /* Styles spécifiques pour le calendrier des événements */
            .event-calendar-container {
                max-width: 900px;
                margin: 40px auto;
                background: var(--bg-card);
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 0 10px var(--shadow-sm);
                width: 100%;
                box-sizing: border-box;
            }

            /* Augmente le contraste des bordures du calendrier */
            #event-calendar {
                --fc-border-color: var(--border-color);
                --fc-page-bg-color: var(--bg-card);
            }

            /* Force l'épaisseur des lignes de la grille */
            .fc-theme-standard td,
            .fc-theme-standard th,
            .fc-theme-standard .fc-scrollgrid {
                border: 1.5px solid var(--border-color) !important;
            }

            /* Améliore la visibilité des noms des jours (Lundi, Mardi...) */
            .fc-col-header-cell {
                background-color: var(--bg-secondary);
                color: var(--text-primary);
                font-weight: bold;
            }

            /* Rend le numéro du jour plus visible */
            .fc-daygrid-day-number {
                color: var(--text-primary) !important;
                font-weight: bold;
                padding: 5px !important;
            }

            /* Cache les codes texte E900/E901 s'ils apparaissent en brut */
            .fc-icon-chevron-left::before {
                content: "‹" !important;
                font-family: sans-serif !important;
            }

            .fc-icon-chevron-right::before {
                content: "›" !important;
                font-family: sans-serif !important;
            }

            /* Améliore l'apparence des boutons de navigation */
            .fc-prev-button, .fc-next-button {
                background-color: var(--color-primary) !important;
                border: none !important;
                opacity: 1 !important;
                color: var(--text-inverse) !important;
            }

            .fc-button-primary:hover {
                background-color: var(--color-primary-hover) !important;
            }

            /* Adaptation pour le mode sombre */
            .dark-mode #event-calendar {
                background: var(--bg-card) !important;
            }

            .dark-mode .fc-event {
                background-color: var(--color-primary) !important;
                border-color: var(--color-primary) !important;
                color: var(--text-inverse) !important;
            }

            .dark-mode .fc-event-title,
            .dark-mode .fc-event-time {
                color: var(--text-inverse) !important;
            }

            /* Modal pour afficher les détails d'événement sur mobile */
            .event-mobile-modal {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                z-index: 10000;
                pointer-events: none;
            }

            .event-mobile-modal.active {
                display: block;
                pointer-events: auto;
            }

            .event-mobile-modal-overlay {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.7);
                backdrop-filter: blur(4px);
            }

            .event-mobile-modal-content {
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                background: var(--bg-card);
                border-radius: 20px 20px 0 0;
                padding: 1.5rem;
                padding-top: 1rem;
                max-height: 80vh;
                overflow-y: auto;
                box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.3);
                animation: slideUp 0.3s ease-out;
            }

            @keyframes slideUp {
                from {
                    transform: translateY(100%);
                }
                to {
                    transform: translateY(0);
                }
            }

            .event-mobile-modal-header {
                position: relative;
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                margin-bottom: 1rem;
                padding-top: 0.5rem;
            }

            .event-mobile-modal-close {
                position: relative;
                top: 0;
                right: 0;
                background: var(--bg-secondary);
                border: 1px solid var(--border-color);
                font-size: 1.25rem;
                line-height: 1;
                color: var(--text-secondary);
                cursor: pointer;
                padding: 0.5rem;
                width: 2.5rem;
                height: 2.5rem;
                min-width: 2.5rem;
                flex-shrink: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                transition: all 0.3s ease;
                margin-left: 1rem;
            }

            .event-mobile-modal-close:hover {
                background: var(--bg-hover);
                color: var(--text-primary);
                border-color: var(--color-primary);
            }

            .event-mobile-modal-title {
                font-size: 1.25rem;
                font-weight: 600;
                color: var(--text-primary);
                margin: 0;
                flex: 1;
                word-wrap: break-word;
                overflow-wrap: break-word;
                padding-right: 0.5rem;
            }

            .event-mobile-modal-date {
                font-size: 0.95rem;
                color: var(--text-secondary);
                margin: 0 0 1.5rem 0;
            }

            .event-mobile-modal-actions {
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
            }

            .event-mobile-modal-btn {
                padding: 0.875rem 1.5rem;
                border-radius: 8px;
                font-size: 1rem;
                font-weight: 500;
                text-align: center;
                text-decoration: none;
                border: none;
                cursor: pointer;
                transition: all 0.3s ease;
                display: block;
                width: 100%;
            }

            .event-mobile-modal-btn-primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
            }

            .event-mobile-modal-btn-primary:hover {
                background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
                text-decoration: none;
            }

            .event-mobile-modal-btn-secondary {
                background: var(--bg-secondary);
                color: var(--text-primary);
                border: 1px solid var(--border-color);
            }

            .event-mobile-modal-btn-secondary:hover {
                background: var(--bg-hover);
                text-decoration: none;
            }

            /* Désactiver l'agrandissement des événements au clic */
            .fc-event {
                cursor: pointer;
            }

            .fc-event:hover {
                opacity: 0.9;
            }

            /* Bloquer le scroll de la page quand la modal est ouverte */
            body.modal-open,
            html.modal-open {
                overflow: hidden !important;
                position: fixed !important;
                width: 100% !important;
                height: 100% !important;
            }

            /* Responsive pour le calendrier des événements */
            @media (max-width: 767px) {
                .event-calendar-container {
                    margin: 15px auto;
                    padding: 8px;
                    max-width: 100%;
                    width: 100%;
                    box-sizing: border-box;
                }

                #event-calendar {
                    overflow-x: auto;
                    -webkit-overflow-scrolling: touch;
                    width: 100% !important;
                    max-width: 100% !important;
                    box-sizing: border-box;
                }
                
                /* Assurer que le calendrier prend toute la largeur */
                .fc-view-harness {
                    width: 100% !important;
                    max-width: 100% !important;
                }
                
                .fc-scrollgrid {
                    width: 100% !important;
                    max-width: 100% !important;
                }
                
                .fc {
                    width: 100% !important;
                    max-width: 100% !important;
                }

                /* FullCalendar toolbar - Mobile */
                .fc-toolbar {
                    flex-direction: column;
                    gap: 0.5rem;
                }

                .fc-toolbar-chunk {
                    width: 100%;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                }

                .fc-toolbar-title {
                    font-size: 1.1rem !important;
                    margin: 0.5rem 0;
                    text-align: center;
                }

                .fc-button-group {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 0.25rem;
                    justify-content: center;
                }

                .fc-button {
                    font-size: 0.75rem !important;
                    padding: 0.4rem 0.6rem !important;
                    border-radius: 4px !important;
                }

                .fc-prev-button,
                .fc-next-button,
                .fc-today-button {
                    font-size: 0.7rem !important;
                    padding: 0.35rem 0.5rem !important;
                }

                /* Vue Mois (dayGridMonth) - Mobile */
                .fc-dayGridMonth-view .fc-daygrid-day {
                    min-height: 75px !important;
                    padding: 2px !important;
                }

                .fc-dayGridMonth-view .fc-daygrid-day-number {
                    font-size: 0.85rem !important;
                    padding: 3px 4px !important;
                    font-weight: 600;
                }

                .fc-dayGridMonth-view .fc-event {
                    font-size: 0.7rem !important;
                    padding: 5px 6px !important;
                    margin: 3px 0 !important;
                    white-space: nowrap !important;
                    overflow: hidden !important;
                    text-overflow: ellipsis !important;
                    max-height: 22px !important;
                    line-height: 1.5 !important;
                    display: block !important;
                    width: calc(100% - 4px) !important;
                    box-sizing: border-box !important;
                    border-radius: 4px;
                    font-weight: 500;
                }
                
                /* Empêcher l'agrandissement des cases d'événements */
                .fc-dayGridMonth-view .fc-daygrid-day-events {
                    margin-top: 3px !important;
                    max-height: 55px !important;
                    overflow: hidden !important;
                }
                
                .fc-dayGridMonth-view .fc-daygrid-event {
                    margin: 2px 0 !important;
                }

                .fc-dayGridMonth-view .fc-event-title {
                    font-size: 0.7rem !important;
                    display: block !important;
                    overflow: hidden !important;
                    text-overflow: ellipsis !important;
                    white-space: nowrap !important;
                    width: 100% !important;
                    font-weight: 500;
                }
                
                /* Masquer l'heure dans les événements sur mobile pour plus d'espace */
                .fc-dayGridMonth-view .fc-event-time {
                    display: none !important;
                }
                
                /* Ajuster les colonnes pour plus d'espace */
                .fc-dayGridMonth-view .fc-col-header-cell {
                    padding: 0.6rem 0.15rem !important;
                    font-size: 0.7rem !important;
                    min-width: 40px;
                }
                
                .fc-dayGridMonth-view .fc-scrollgrid-sync-table {
                    width: 100% !important;
                    table-layout: fixed !important;
                }
                
                .fc-dayGridMonth-view .fc-daygrid-day-frame {
                    min-height: 75px !important;
                }
                
                .fc-dayGridMonth-view .fc-daygrid-day-top {
                    flex-direction: row;
                    justify-content: flex-start;
                }
                
                /* S'assurer que les cellules ont une largeur fixe */
                .fc-dayGridMonth-view .fc-daygrid-day {
                    width: 14.28% !important;
                    min-width: 40px !important;
                }

                /* Vue Semaine (timeGridWeek) - Mobile */
                .fc-timeGridWeek-view {
                    overflow-x: auto;
                    -webkit-overflow-scrolling: touch;
                }

                .fc-timeGridWeek-view .fc-timegrid-col-header {
                    min-width: 80px;
                }

                .fc-timeGridWeek-view .fc-timegrid-col {
                    min-width: 80px;
                }

                .fc-timeGridWeek-view .fc-timegrid-slot-label {
                    font-size: 0.65rem;
                    padding: 0.2rem 0.25rem;
                    width: 2.5rem;
                }

                .fc-timeGridWeek-view .fc-timegrid-event {
                    font-size: 0.7rem;
                    padding: 1px 2px;
                    margin: 0.5px 0;
                }

                .fc-timeGridWeek-view .fc-event-title {
                    font-size: 0.65rem;
                    line-height: 1.1;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }

                .fc-timeGridWeek-view .fc-event-time {
                    font-size: 0.6rem;
                    display: none;
                }

                .fc-timeGridWeek-view .fc-col-header-cell {
                    font-size: 0.75rem;
                    padding: 0.5rem 0.25rem;
                }

                .fc-timeGridWeek-view .fc-col-header-cell-cushion {
                    padding: 0.25rem;
                    font-weight: 600;
                }

                /* Scrollbar personnalisée pour mobile */
                #event-calendar::-webkit-scrollbar {
                    height: 6px;
                    width: 6px;
                }

                #event-calendar::-webkit-scrollbar-track {
                    background: rgba(0, 0, 0, 0.1);
                }

                #event-calendar::-webkit-scrollbar-thumb {
                    background: rgba(0, 0, 0, 0.3);
                    border-radius: 3px;
                }

                /* Sélecteur de vue mobile */
                .fc-view-selector-mobile {
                    width: 100%;
                    margin-top: 0.5rem;
                    padding: 0.5rem;
                    background: var(--bg-secondary);
                    border-radius: 8px;
                }

                .fc-view-selector-mobile button {
                    flex: 1;
                    min-width: 80px;
                    padding: 0.5rem 0.75rem;
                    font-size: 0.85rem;
                    font-weight: 500;
                    border-radius: 6px;
                    transition: all 0.3s ease;
                }

                .fc-view-selector-mobile button.fc-button-active {
                    background-color: var(--color-primary) !important;
                    border-color: var(--color-primary) !important;
                    color: var(--text-inverse) !important;
                    font-weight: 600;
                    transform: scale(1.05);
                }

                .fc-view-selector-mobile button:hover {
                    transform: translateY(-1px);
                }
            }

            @media (max-width: 480px) {
                .event-calendar-container {
                    padding: 0.5rem;
                }

                .fc-toolbar-title {
                    font-size: 1rem !important;
                }

                .fc-button {
                    font-size: 0.7rem !important;
                    padding: 0.35rem 0.5rem !important;
                }

                .fc-timeGridWeek-view .fc-timegrid-col-header,
                .fc-timeGridWeek-view .fc-timegrid-col {
                    min-width: 70px;
                }

                .fc-timeGridWeek-view .fc-col-header-cell {
                    font-size: 0.7rem;
                    padding: 0.4rem 0.2rem;
                }

                .fc-dayGridMonth-view .fc-daygrid-day {
                    min-height: 65px !important;
                    padding: 1px !important;
                }

                .fc-dayGridMonth-view .fc-daygrid-day-number {
                    font-size: 0.8rem !important;
                    padding: 2px 3px !important;
                }

                .fc-dayGridMonth-view .fc-event {
                    font-size: 0.6rem !important;
                    padding: 3px 5px !important;
                    max-height: 18px !important;
                    line-height: 1.3 !important;
                }
                
                .fc-dayGridMonth-view .fc-daygrid-day-events {
                    max-height: 50px !important;
                }
                
                .fc-dayGridMonth-view .fc-col-header-cell {
                    padding: 0.5rem 0.1rem !important;
                    font-size: 0.65rem !important;
                }
            }
        </style>
        <div class="event-calendar-container">
            <div id="event-calendar"></div>
        </div>
        <!-- SCRIPT JS pour implémenter le calendrier avec la bibliothèque FullCalendar -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let calendarEl = document.getElementById('event-calendar');
                
                // Détection mobile
                const isMobile = window.innerWidth <= 767;
                
                // Configuration responsive de la toolbar
                const getHeaderToolbar = () => {
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
                const getInitialView = () => {
                    if (isMobile) {
                        return 'dayGridMonth'; // Vue mois par défaut sur mobile
                    }
                    return 'dayGridMonth';
                };
                
                let calendar = new FullCalendar.Calendar(calendarEl, {
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
                    events: [
                        <?php foreach ($events as $event) : ?>
                        {
                            id: <?= json_encode($event['event_id']) ?>,
                            title: <?= json_encode($event['event_name']) ?>,
                            start: <?= json_encode($event['event_date'] . 'T' . $event['event_time']) ?>,
                            url: <?= json_encode('index.php?page=showEvent&id=' . $event['event_id']) ?>,
                            backgroundColor: '#667eea',
                            borderColor: '#667eea',
                            textColor: '#ffffff'
                        },
                        <?php endforeach; ?>
                    ],
                    eventClick: function(info) {
                        info.jsEvent.preventDefault();
                        
                        // Sur mobile : afficher une modal avec les détails
                        // Sur PC : rediriger vers la page de détails
                        if (isMobile) {
                            // Récupérer les détails de l'événement
                            const eventId = info.event.id;
                            const eventTitle = info.event.title;
                            const eventStart = info.event.start;
                            const eventUrl = info.event.url;
                            
                            // Créer et afficher la modal
                            showEventModal(eventTitle, eventStart, eventUrl);
                        } else {
                            // Sur PC : redirection directe
                            if (info.event.url) {
                                window.location.href = info.event.url;
                            }
                        }
                    },
                    // Désactiver le popover/tooltip par défaut qui agrandit les cases
                    eventDisplay: 'block',
                    dayMaxEvents: true,
                    moreLinkClick: 'popover'
                });
                
                calendar.render();
                
                // Vérification et correction des dimensions sur mobile
                setTimeout(() => {
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
                            
                            // Appliquer des styles inline de secours pour forcer les bonnes dimensions
                            const style = document.createElement('style');
                            style.id = 'fc-mobile-fallback-styles';
                            
                            style.textContent = `
                                @media (max-width: 767px) {
                                    .event-calendar-container {
                                        width: 100% !important;
                                        max-width: 100% !important;
                                        margin: 15px 0 !important;
                                        padding: 8px !important;
                                        box-sizing: border-box !important;
                                    }
                                    .event-calendar-container * {
                                        box-sizing: border-box !important;
                                    }
                                    #event-calendar {
                                        width: 100% !important;
                                        max-width: 100% !important;
                                        box-sizing: border-box !important;
                                    }
                                    .fc {
                                        width: 100% !important;
                                        max-width: 100% !important;
                                        box-sizing: border-box !important;
                                    }
                                    .fc-view-harness {
                                        width: 100% !important;
                                        max-width: 100% !important;
                                    }
                                    .fc-scrollgrid {
                                        width: 100% !important;
                                        max-width: 100% !important;
                                        box-sizing: border-box !important;
                                    }
                                    .fc-scrollgrid-sync-table {
                                        width: 100% !important;
                                        table-layout: fixed !important;
                                    }
                                    .fc-dayGridMonth-view .fc-daygrid-day {
                                        min-height: 75px !important;
                                        width: 14.28% !important;
                                        min-width: 40px !important;
                                        box-sizing: border-box !important;
                                    }
                                    .fc-dayGridMonth-view .fc-event {
                                        font-size: 0.7rem !important;
                                        padding: 5px 6px !important;
                                        max-height: 22px !important;
                                        line-height: 1.5 !important;
                                    }
                                }
                            `;
                            if (!document.getElementById('fc-mobile-fallback-styles')) {
                                document.head.appendChild(style);
                            }
                            
                            // Forcer le recalcul des dimensions après l'application des styles
                            setTimeout(() => {
                                calendar.updateSize();
                                const containerEl = calendarEl.parentElement;
                                if (containerEl) {
                                    containerEl.style.width = '100%';
                                    containerEl.style.maxWidth = '100%';
                                    containerEl.style.margin = '15px 0';
                                }
                                calendarEl.style.width = '100%';
                                calendarEl.style.maxWidth = '100%';
                                setTimeout(() => {
                                    calendar.updateSize();
                                }, 100);
                            }, 200);
                        }
                    }
                }, 500);
                
                // Fonction pour afficher la modal d'événement sur mobile
                function showEventModal(title, start, url) {
                    // Créer la modal si elle n'existe pas
                    let modal = document.getElementById('event-mobile-modal');
                    if (!modal) {
                        modal = document.createElement('div');
                        modal.id = 'event-mobile-modal';
                        modal.className = 'event-mobile-modal';
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
                    
                    // Contenu de la modal
                    modal.innerHTML = `
                        <div class="event-mobile-modal-overlay"></div>
                        <div class="event-mobile-modal-content">
                            <div class="event-mobile-modal-header">
                                <h3 class="event-mobile-modal-title">${title}</h3>
                                <button class="event-mobile-modal-close" aria-label="Fermer">×</button>
                            </div>
                            <p class="event-mobile-modal-date">${dateStr}</p>
                            <div class="event-mobile-modal-actions">
                                <a href="${url}" class="event-mobile-modal-btn event-mobile-modal-btn-primary">Voir les détails</a>
                            </div>
                        </div>
                    `;
                    
                    // Afficher la modal
                    modal.classList.add('active');
                    document.body.classList.add('modal-open');
                    document.documentElement.classList.add('modal-open');
                    
                    // Fermer la modal
                    const closeModal = () => {
                        modal.classList.remove('active');
                        document.body.classList.remove('modal-open');
                        document.documentElement.classList.remove('modal-open');
                    };
                    
                    modal.querySelectorAll('.event-mobile-modal-close').forEach(btn => {
                        btn.addEventListener('click', closeModal);
                    });
                    
                    const overlay = modal.querySelector('.event-mobile-modal-overlay');
                    overlay.addEventListener('click', closeModal);
                    
                    // Empêcher le scroll tactile sur l'overlay
                    overlay.addEventListener('touchmove', (e) => {
                        e.preventDefault();
                    }, { passive: false });
                }
                
                // Ajouter un menu de sélection de vue sur mobile
                if (isMobile) {
                    const toolbar = calendarEl.querySelector('.fc-toolbar');
                    if (toolbar) {
                        const viewSelector = document.createElement('div');
                        viewSelector.className = 'fc-view-selector-mobile';
                        viewSelector.style.cssText = 'display: flex; gap: 0.5rem; margin-top: 0.5rem; justify-content: center; flex-wrap: wrap; padding: 0.5rem;';
                        
                        const views = [
                            { key: 'dayGridMonth', label: 'Mois' },
                            { key: 'timeGridWeek', label: 'Semaine' }
                        ];
                        
                        views.forEach(view => {
                            const btn = document.createElement('button');
                            btn.className = 'fc-button fc-button-primary';
                            btn.textContent = view.label;
                            btn.style.cssText = 'padding: 0.4rem 0.8rem; font-size: 0.8rem; border-radius: 4px; cursor: pointer;';
                            
                            if (calendar.view.type === view.key) {
                                btn.classList.add('fc-button-active');
                            }
                            
                            btn.addEventListener('click', () => {
                                calendar.changeView(view.key);
                                viewSelector.querySelectorAll('button').forEach(b => b.classList.remove('fc-button-active'));
                                btn.classList.add('fc-button-active');
                            });
                            
                            viewSelector.appendChild(btn);
                        });
                        
                        toolbar.appendChild(viewSelector);
                    }
                }
                
                // Adapter la toolbar lors du redimensionnement
                let resizeTimer;
                window.addEventListener('resize', () => {
                    clearTimeout(resizeTimer);
                    resizeTimer = setTimeout(() => {
                        const newIsMobile = window.innerWidth <= 767;
                        if (newIsMobile !== isMobile) {
                            location.reload();
                        } else if (calendar) {
                            // Forcer le re-render si on reste dans le même mode
                            calendar.updateSize();
                        }
                    }, 250);
                });
            });
        </script>

    <?php else : ?>
        <?php foreach ($events as $event) : ?>
            <?php
            // Logique de décodage des images
            $eventImages = !empty($event['images']) ? json_decode($event['images'], true) : [];
            $carouselImages = [];
            if (!empty($eventImages) && is_array($eventImages)) {
                foreach ($eventImages as $image) {
                    if (is_array($image) && isset($image['url'])) {
                        $carouselImages[] = ['src' => $image['url']];
                    } elseif (is_string($image)) {
                        $carouselImages[] = ['src' => $image];
                    }
                }
            }

            // Si aucune image disponible, le carousel sera vide
            ?>

            <div class="event-item" style="text-align: center; margin-bottom: 50px; border-bottom: 1px solid #eee; padding-bottom: 20px;">
                <?php useCarousel($event['event_name'], $carouselImages, 'carousel-event-' . $event['event_id']); ?>

                <div style="margin-top: 15px;">
                    <a href="index.php?page=showEvent&id=<?= $event['event_id'] ?>" class="btn-more" style="color: #1299ff; font-weight: bold; text-decoration: none;">
                        Voir les détails
                    </a>
                </div>
            </div>
        <?php endforeach; ?>

            <nav class="pagination-container" aria-label="Navigation des événements">
                <div class="pagination-info" style="text-align: center; margin-bottom: 10px;">
                    Page <?= $pagination->getCurrentPage() ?> sur <?= $pagination->getTotalPages() ?> (<?= $pagination->getTotalItems() ?> événement<?= $pagination->getTotalItems() > 1 ? 's' : '' ?>)
                </div>

                <ul class="pagination">
                    <?php if ($pagination->hasPrevious()) : ?>
                        <li>
                            <a href="<?= $pagination->getLink($pagination->getFirstPage()) ?>">« Premier</a>
                        </li>
                        <li>
                            <a href="<?= $pagination->getLink($pagination->getCurrentPage() - 1) ?>&src=prev">‹ Précédent</a>
                        </li>
                    <?php endif; ?>

                    <li>
                        <span class="page-number active" aria-current="page" aria-label="Page <?= $pagination->getCurrentPage() ?>, page actuelle">
                            <?= $pagination->getCurrentPage() ?>
                        </span>
                    </li>

                    <?php if ($pagination->hasNext()) : ?>
                        <li>
                            <a href="<?= $pagination->getLink($pagination->getCurrentPage() + 1) ?>&src=next">Suivant ›</a>
                        </li>
                        <li>
                            <a href="<?= $pagination->getLink($pagination->getLastPage()) ?>">Dernier »</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>

    <?php endif; ?>
</div>

<?php end_page(); ?>
