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

// Les variables $events et $pagination sont définies par EventController

// Repository pour vérifier les inscriptions
$registrationRepo = new \App\Modules\Repositories\EventRegistrationRepository();
$userId = $user['user_id'] ?? null;
?>

<div class="container event-list">
    <h1 style="text-align: center;">Nos Événements</h1>

    <!-- Messages flash -->
    <?php foreach (['success' => '#d4edda', 'error' => '#f8d7da'] as $type => $color) : ?>
        <?php if (!empty($flash[$type])) : ?>
            <div style="background-color: <?= $color ?>; color: #<?= $type === 'success' ? '155724' : '721c24' ?>; padding: 12px; margin: 20px 0; border: 1px solid #<?= $type === 'success' ? 'c3e6cb' : 'f5c6cb' ?>; border-radius: 4px; text-align: center;">
                <?= htmlspecialchars($flash[$type]) ?>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <?php if (empty($events)) : ?>
        <p>Aucun événement à afficher pour le moment.</p>
    <?php else : ?>
        <?php foreach ($events as $event) : ?>
            <?php
            // Décoder le JSON des images Cloudinary
            $eventImages = !empty($event['images']) ? json_decode($event['images'], true) : [];

            // Convertir en format attendu par useCarousel()
            $carouselImages = [];
            if (!empty($eventImages) && is_array($eventImages)) {
                foreach ($eventImages as $image) {
                    // Support des deux formats: string simple ou objet {url, public_id}
                    if (is_string($image)) {
                        // Nouveau format: ["url1", "url2", ...]
                        $carouselImages[] = ['src' => $image];
                    } elseif (is_array($image) && isset($image['url'])) {
                        // Ancien format: [{"url": "...", "public_id": "..."}, ...]
                        $carouselImages[] = ['src' => $image['url']];
                    }
                }
            }

            // Fallback vers images par défaut si vide
            if (empty($carouselImages)) {
                $carouselImages = [
                    ['src' => './assets/img/carousel/events/event2.jpg'],
                    ['src' => './assets/img/carousel/events/event1.svg'],
                ];
            }
            ?>

            <div class="event-item" style="text-align: center; margin-bottom: 50px; border-bottom: 1px solid #eee; padding-bottom: 20px;">

                <?php
                // ... (Logique de décodage des images déjà présente dans votre fichier) ...
                useCarousel($event['event_name'], $carouselImages, 'carousel-event-' . $event['event_id']);
                ?>

                <div style="margin-top: 15px;">
                    <a href="index.php?page=showEvent&id=<?= $event['event_id'] ?>" class="btn-more" style="color: #1299ff; font-weight: bold;">
                        Voir les détails et s'inscrire →
                    </a>
                </div>
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
