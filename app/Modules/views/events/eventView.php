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
    <div class="btn-group">
        <a href="index.php?page=event&view=list" class="btn <?= $viewMode === 'list' ? 'active' : '' ?>"
           style="padding: 10px 20px; background: <?= $viewMode === 'list' ? '#1299ff' : '#ccc' ?>; color: white; text-decoration: none; border-radius: 5px 0 0 5px;">Liste</a>
        <a href="index.php?page=event&view=calendar" class="btn <?= $viewMode === 'calendar' ? 'active' : '' ?>"
           style="padding: 10px 20px; background: <?= $viewMode === 'calendar' ? '#1299ff' : '#ccc' ?>; color: white; text-decoration: none; border-radius: 0 5px 5px 0;">Calendrier</a>
    </div>
</div>
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
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
        <style>
            /* Augmente le contraste des bordures du calendrier */
            #calendar {
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
                content: "‹" !important; /* Force une flèche lisible */
                font-family: sans-serif !important;
            }

            .fc-icon-chevron-right::before {
                content: "›" !important; /* Force une flèche lisible */
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
            .dark-mode #calendar {
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
        </style> <!-- CSS du calendrier -->
        <div id="calendar" style="max-width: 900px; margin: 40px auto; background: var(--bg-card); padding: 20px; border-radius: 8px; box-shadow: 0 0 10px var(--shadow-sm);"></div>
        <!-- SCRIPT JS pour implémenter le calendrier avec la bibliothèque FullCalendar -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let calendarEl = document.getElementById('calendar');
                let calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    locale: 'fr',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek'
                    },
                        // Fichier : app/Modules/views/events/eventView.php

                    events: [
                        <?php foreach ($events as $event) : ?>
                        {
                            id: <?= json_encode($event['event_id']) ?>,
                            title: <?= json_encode($event['event_name']) ?>,
                            start: <?= json_encode($event['event_date'] . 'T' . $event['event_time']) ?>,
                            url: <?= json_encode('index.php?page=showEvent&id=' . $event['event_id']) ?>,
                            // Les couleurs seront gérées par CSS en mode sombre
                            backgroundColor: '#667eea',
                            borderColor: '#667eea',
                            textColor: '#ffffff',
                            borderColor: '#0056b3'
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
