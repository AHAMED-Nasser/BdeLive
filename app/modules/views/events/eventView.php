<?php
/**
 * @var array<string, mixed> $events La liste des événements (passée par EventController)
 * @var Pagination $pagination L'objet pagination (passé par EventController)
 */

require_once __DIR__ . '/../shared/include.inc.php';
start_page('Liste des Événements');

// Les variables $events et $pagination sont définies par EventController

// Images par défaut pour les événements (en attendant les images en BDD)
$defaultImages = [
    ['src' => './assets/img/carousel/events/event2.jpg'],
    ['src' => './assets/img/carousel/events/event1.svg'],
];

// Repository pour vérifier les inscriptions
$registrationRepo = new EventRegistrationRepository();
$userId = $_SESSION['user_id'] ?? null;
?>

<div class="container event-list">
    <h1 style="text-align: center;">Nos Événements</h1>

    <!-- Messages flash -->
    <?php foreach (['success' => '#d4edda', 'error' => '#f8d7da'] as $type => $color) : ?>
        <?php if (!empty($_SESSION[$type])) : ?>
            <div style="background-color: <?= $color ?>; color: #<?= $type === 'success' ? '155724' : '721c24' ?>; padding: 12px; margin: 20px 0; border: 1px solid #<?= $type === 'success' ? 'c3e6cb' : 'f5c6cb' ?>; border-radius: 4px; text-align: center;">
                <?= htmlspecialchars($_SESSION[$type]) ?>
            </div>
            <?php unset($_SESSION[$type]); ?>
        <?php endif; ?>
    <?php endforeach; ?>

    <?php if (empty($events)) : ?>
        <p>Aucun événement à afficher pour le moment.</p>
    <?php else : ?>
        <?php foreach ($events as $event) : ?>
            <div class="event-item" style="text-align: center; margin-bottom: 30px;">
                <h2 style="text-align: center;"><?= htmlspecialchars($event['event_name']) ?></h2>
                <p style="text-align: center; color: #666; margin-bottom: 20px;">
                    Le <?= htmlspecialchars(date('d/m/Y', strtotime($event['event_date']))) ?>
                    à <?= htmlspecialchars(date('H:i', strtotime($event['event_time']))) ?>
                </p>
                <p style="text-align: center; color: #666; margin-bottom: 20px;">
                    Lieu : <?= htmlspecialchars($event['event_location']) ?>
                </p>

                <?php if (!empty($event['description'])) : ?>
                    <p style="color: #666; margin-bottom: 20px; font-size: 1.2rem; text-align: center">
                        <?= htmlspecialchars($event['description']) ?>
                    </p>
                <?php endif; ?>
                
                <!-- Carousel pour chaque événement -->
                <?php useCarousel($event['event_name'], $defaultImages, 'carousel-event-' . $event['event_id']) ?>
                
                <!-- Boutons d'inscription (utilisateurs connectés) -->
                <?php if ($userId) : ?>
                    <?php $isRegistered = $registrationRepo->isUserRegistered((int)$event['event_id'], (int)$userId); ?>
                    <?php if ($isRegistered) : ?>
                        <a href="index.php?page=registerEvent&action=unregister&event_id=<?= $event['event_id'] ?>" 
                           style="background-color: #dc3545; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-size: 1rem; text-decoration: none; display: inline-block; margin-top: 15px;">
                            Se désinscrire
                        </a>
                    <?php else : ?>
                        <a href="index.php?page=registerEvent&action=register&event_id=<?= $event['event_id'] ?>" 
                           style="background-color: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-size: 1rem; text-decoration: none; display: inline-block; margin-top: 15px;">
                            S'inscrire
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
                
                <!-- Bouton de suppression (admin uniquement) -->
                <?php if (isset($_SESSION['user_status']) && $_SESSION['user_status'] === 'BDE') : ?>
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

    <?php if ($pagination->getTotalPages() > 1) : ?>
        <div class="pagination-info">
            Page <?= $pagination->getCurrentPage() ?> sur <?= $pagination->getTotalPages() ?> (<?= $pagination->getTotalItems() ?> événement<?= $pagination->getTotalItems() > 1 ? 's' : '' ?>)
        </div>
        
        <ul class="pagination">
            <?php if ($pagination->hasPrevious()) : ?>
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

</div>

<?php end_page(); ?>
