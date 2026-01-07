<?php
/**
 * @var \App\Core\Security\CsrfProtection $csrf
 * @var array<string, mixed>|null $user
 * @var array<string, string|null> $flash
 * @var array<int, array<string, mixed>> $events La liste des événements (passée par EventController)
 * @var \App\Modules\Helpers\Pagination $pagination L'objet pagination (passé par EventController)
 */

require_once __DIR__ . '/../shared/include.inc.php';
start_page('Liste des Événements', true, $user ?? null);

$viewMode = $viewMode ?? 'list'; ?>

<div class="container" style="margin-bottom: 20px; margin-top: 20px; text-align: center;">
    <div class="btn-group" style="display: flex; justify-content: center; gap: 0;">
        <a href="index.php?page=event&view=list" class="btn <?= $viewMode === 'list' ? 'active' : '' ?>"
           style="padding: 10px 20px; background: <?= $viewMode === 'list' ? '#1299ff' : '#ccc' ?>; color: white; text-decoration: none; border-radius: 5px 0 0 5px; transition: all 0.3s ease;">Liste</a>
        <a href="index.php?page=event&view=calendar" class="btn <?= $viewMode === 'calendar' ? 'active' : '' ?>"
           style="padding: 10px 20px; background: <?= $viewMode === 'calendar' ? '#1299ff' : '#ccc' ?>; color: white; text-decoration: none; border-radius: 0 5px 5px 0; transition: all 0.3s ease;">Calendrier</a>
    </div>
</div>
<style>
    /* Responsive pour les boutons Liste/Calendrier */
    @media (max-width: 767px) {
        .btn-group {
            flex-wrap: wrap;
            gap: 0.5rem !important;
        }
        
        .btn-group .btn {
            flex: 1;
            min-width: 120px;
            padding: 0.75rem 1rem !important;
            font-size: 0.95rem;
            border-radius: 6px !important;
        }
    }
    
    @media (max-width: 480px) {
        .btn-group .btn {
            padding: 0.6rem 0.8rem !important;
            font-size: 0.85rem;
            min-width: 100px;
        }
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

            /* Responsive pour le calendrier des événements */
            @media (max-width: 767px) {
                .event-calendar-container {
                    margin: 20px auto;
                    padding: 10px;
                }

                #event-calendar {
                    overflow-x: auto;
                    -webkit-overflow-scrolling: touch;
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
                    min-height: 60px;
                }

                .fc-dayGridMonth-view .fc-daygrid-day-number {
                    font-size: 0.85rem;
                    padding: 3px 5px;
                }

                .fc-dayGridMonth-view .fc-event {
                    font-size: 0.7rem;
                    padding: 2px 4px;
                    margin: 1px 0;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }

                .fc-dayGridMonth-view .fc-event-title {
                    font-size: 0.7rem;
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
                    min-height: 50px;
                }

                .fc-dayGridMonth-view .fc-daygrid-day-number {
                    font-size: 0.8rem;
                }

                .fc-dayGridMonth-view .fc-event {
                    font-size: 0.65rem;
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
                        if (info.event.url) {
                            window.location.href = info.event.url;
                            info.jsEvent.preventDefault();
                        }
                    }
                });
                
                calendar.render();
                
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
                    if (is_string($image)) {
                        $carouselImages[] = ['src' => $image];
                    } elseif (is_array($image) && isset($image['url'])) {
                        $carouselImages[] = ['src' => $image['url']];
                    }
                }
            }

            if (empty($carouselImages)) {
                $carouselImages = [
                    ['src' => './assets/img/carousel/events/event2.jpg'],
                    ['src' => './assets/img/carousel/events/event1.svg'],
                ];
            }
            ?>

            <div class="event-item" style="text-align: center; margin-bottom: 50px; border-bottom: 1px solid #eee; padding-bottom: 20px;">
                <?php useCarousel($event['event_name'], $carouselImages, 'carousel-event-' . $event['event_id']); ?>

                <div style="margin-top: 15px;">
                    <a href="index.php?page=showEvent&id=<?= $event['event_id'] ?>" class="btn-more" style="color: #1299ff; font-weight: bold;">
                        Voir les détails et s'inscrire →
                    </a>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if ($pagination->getTotalPages() > 1) : ?>
            <div class="pagination-info" style="text-align: center; margin-bottom: 10px;">
                Page <?= $pagination->getCurrentPage() ?> sur <?= $pagination->getTotalPages() ?> (<?= $pagination->getTotalItems() ?> événement<?= $pagination->getTotalItems() > 1 ? 's' : '' ?>)
            </div>

            <ul class="pagination" style="display: flex; justify-content: center; list-style: none; gap: 10px; padding: 0;">
                <?php if ($pagination->hasPrevious()) : ?>
                    <li>
                        <a href="<?= $pagination->getLink($pagination->getFirstPage()) ?>">« Premier</a>
                    </li>
                    <li>
                        <a href="<?= $pagination->getLink($pagination->getCurrentPage() - 1) ?>">‹ Précédent</a>
                    </li>
                <?php endif; ?>

                <li>
                    <a href="<?= $pagination->getLink($pagination->getCurrentPage()) ?>" aria-current="page" style="font-weight: bold; text-decoration: underline;">
                        <?= $pagination->getCurrentPage() ?>
                    </a>
                </li>

                <?php if ($pagination->hasNext()) : ?>
                    <li>
                        <a href="<?= $pagination->getLink($pagination->getCurrentPage() + 1) ?>">Suivant ›</a>
                    </li>
                    <li>
                        <a href="<?= $pagination->getLink($pagination->getLastPage()) ?>">Dernier »</a>
                    </li>
                <?php endif; ?>
            </ul>
        <?php endif; ?>

    <?php endif; ?>
</div>

<?php end_page(); ?>
