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

<link rel="stylesheet" href="/assets/css/event.css">

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
        <!-- Calendrier natif pour les événements -->
        <?php
        $nativeCalendar = $nativeCalendar ?? null;
        $calYear = $calYear ?? (int)date('Y');
        $calMonth = $calMonth ?? (int)date('n');

        if ($nativeCalendar) {
            $calendar = $nativeCalendar;
            $pageUrl = 'index.php?page=event';
            $extraParams = ['view' => 'calendar'];
            include __DIR__ . '/../components/event-calendar.php';
        } else {
            echo '<p style="text-align: center; margin-top: 50px;">Erreur lors du chargement du calendrier.</p>';
        }
        ?>

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
