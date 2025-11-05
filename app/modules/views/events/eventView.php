<?php
/**
 * @var array<string, mixed> $events La liste des événements (passée par EventController)
 * @var Pagination $pagination L'objet pagination (passé par EventController)
 */

start_page('Liste des Événements');

// Les variables $events et $pagination sont définies par EventController 

// Images par défaut pour les événements (en attendant les images en BDD)
$defaultImages = [
    ['src' => './assets/img/carousel/events/event2.jpg'],
    ['src' => './assets/img/carousel/events/event1.svg'],
];
?>

<div class="container event-list">
    <h1 style="text-align: center;">Nos Événements</h1>

    <!-- Messages flash -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 12px; margin: 20px 0; border: 1px solid #c3e6cb; border-radius: 4px; text-align: center;">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 12px; margin: 20px 0; border: 1px solid #f5c6cb; border-radius: 4px; text-align: center;">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($events)): ?>
        <p>Aucun événement à afficher pour le moment.</p>
    <?php else: ?>
        <?php foreach ($events as $event): ?>
            <div class="event-item" style="text-align: center; margin-bottom: 30px;">
                <h2 style="text-align: center;"><?= htmlspecialchars($event['event_name']) ?></h2>
                <p style="text-align: center; color: #666; margin-bottom: 20px;">
                    Le <?= htmlspecialchars(date('d/m/Y', strtotime($event['event_date']))) ?>
                    à <?= htmlspecialchars(date('H:i', strtotime($event['event_time']))) ?>
                </p>
                <p style="text-align: center; color: #666; margin-bottom: 20px;">
                    Lieu : <?= htmlspecialchars($event['event_location']) ?>
                </p>

                <?php if (!empty($event['description'])): ?>
                    <p style="color: #666; margin-bottom: 20px; font-size: 1.2rem; text-align: center">
                        <?= htmlspecialchars($event['description']) ?>
                    </p>
                <?php endif; ?>

                <?php
                // Prepare images for carousel
                $eventImages = (new EventManager())->getEventImages($event['event_id']);

                // else, use default images
                if (empty($eventImages)) {
                    $eventImages = $defaultImages;
                }
                ?>
                
                <!-- Carousel pour chaque événement -->
                <?php useCarousel($eventImages, 'carousel-event-' . $event['event_id']) ?>
                
                <!-- Bouton de suppression (admin uniquement) -->
                <?php if (isset($_SESSION['user_status']) && $_SESSION['user_status'] === 'BDE'): ?>
                    <div style="text-align: center; margin-top: 15px;">
                        <form method="post" action="index.php?page=deleteEvent" 
                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer l\'événement \'<?= htmlspecialchars($event['event_name']) ?>\' ?\n\nCette action est irréversible.');"
                              style="display: inline;">
                            <input type="hidden" name="event_id" value="<?= $event['event_id'] ?>">
                            <?= csrfField() ?>
                            <button type="submit" 
                                    style="background-color: #dc3545; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">
                                Supprimer
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if ($pagination->getTotalPages() > 1): ?>
        <div class="pagination-info">
            Page <?= $pagination->getCurrentPage() ?> sur <?= $pagination->getTotalPages() ?> (<?= $pagination->getTotalItems() ?> événement<?= $pagination->getTotalItems() > 1 ? 's' : '' ?>)
        </div>
        
        <ul class="pagination">
            <?php if ($pagination->hasPrevious()): ?>
                <li>
                    <a href="<?= $pagination->getLink($pagination->getFirstPage()) ?>">« Premier</a>
                </li>
                <li>
                    <a href="<?= $pagination->getLink($pagination->getCurrentPage() - 1) ?>">‹ Précédent</a>
                </li>
            <?php endif; ?>

            <li>
                <a href="<?= $pagination->getLink($pagination->getCurrentPage()) ?>" aria-current="page">
                    <?= $pagination->getCurrentPage() ?>
                </a>
            </li>

            <?php if ($pagination->hasNext()): ?>
                <li>
                    <a href="<?= $pagination->getLink($pagination->getCurrentPage() + 1) ?>">Suivant ›</a>
                </li>
                <li>
                    <a href="<?= $pagination->getLink($pagination->getLastPage()) ?>">Dernier »</a>
                </li>
            <?php endif; ?>
        </ul>
    <?php endif; ?>

</div>

<?php end_page(); ?>
