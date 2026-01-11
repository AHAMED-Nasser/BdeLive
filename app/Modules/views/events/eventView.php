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
        <!-- Ressources FullCalendar -->
        <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' />
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
        <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/locales/fr.global.min.js'></script>
        
        <!-- Styles du calendrier des événements -->
        <link rel="stylesheet" href="/assets/css/event-calendar.css">
        
        <div class="event-calendar-container">
            <div id="event-calendar" role="region" aria-label="Calendrier des événements"></div>
        </div>
        
        <!-- Données des événements en JSON pour le calendrier -->
        <script id="event-calendar-data" type="application/json">
            <?php
            $calendarEvents = [];
            foreach ($events as $event) {
                $calendarEvents[] = [
                    'id' => $event['event_id'],
                    'title' => $event['event_name'],
                    'start' => $event['event_date'] . 'T' . $event['event_time'],
                    'url' => 'index.php?page=showEvent&id=' . $event['event_id'],
                    'backgroundColor' => '#4c51bf',
                    'borderColor' => '#4c51bf',
                    'textColor' => '#ffffff'
                ];
            }
            echo json_encode($calendarEvents, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
            ?>
        </script>
        
        <!-- Script JS externe pour le calendrier -->
        <script src="/assets/js/event-calendar.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Récupérer les données des événements depuis le script JSON
                var dataScript = document.getElementById('event-calendar-data');
                var events = [];
                if (dataScript) {
                    try {
                        events = JSON.parse(dataScript.textContent);
                    } catch (e) {
                        console.error('EventCalendar: Failed to parse events data', e);
                    }
                }
                
                // Initialiser le calendrier
                if (window.EventCalendar) {
                    window.EventCalendar.init('event-calendar', events);
                }
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
                    <a href="index.php?page=showEvent&id=<?= $event['event_id'] ?>" class="btn-more" style="color: var(--color-primary); font-weight: bold; text-decoration: none;">
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
