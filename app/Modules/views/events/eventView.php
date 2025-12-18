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

// Repository pour vérifier les inscriptions
$registrationRepo = new \App\Modules\Repositories\EventRegistrationRepository();
$userId = $user['user_id'] ?? null;
?>

    <div class="container event-list">
        <h1 style="text-align: center;">Nos Événements</h1>

        <?php foreach (['success' => '#d4edda', 'error' => '#f8d7da'] as $type => $color) : ?>
            <?php if (!empty($flash[$type])) : ?>
                <div style="background-color: <?= $color ?>; color: <?= $type === 'success' ? '#155724' : '#721c24' ?>; padding: 12px; margin: 20px 0; border: 1px solid <?= $type === 'success' ? '#c3e6cb' : '#f5c6cb' ?>; border-radius: 4px; text-align: center;">
                    <?= htmlspecialchars($flash[$type]) ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if (empty($events)) : ?>
            <p>Aucun événement à afficher pour le moment.</p>
        <?php else : ?>
            <?php foreach ($events as $event) : ?>
                <?php
                /** @var array<string, mixed> $event */ // <--- Cette ligne aide l'éditeur à ne plus souligner en rouge

                // Décoder le JSON des images Cloudinary
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

                    <?php useCarousel($event['event_name'], $carouselImages, 'carousel-event-' . $event['event_id']) ?>

                    <?php if ($userId) : ?>
                        <?php $isRegistered = $registrationRepo->isUserRegistered((int)$event['event_id'], (int)$userId); ?>
                        <?php if ($isRegistered) : ?>
                            <a href="index.php?page=registerEvent&action=unregister&event_id=<?= $event['event_id'] ?>"
                               style="background-color: #dc3545; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none; display: inline-block; margin-top: 15px;">
                                Se désinscrire
                            </a>
                        <?php else : ?>
                            <a href="index.php?page=registerEvent&action=register&event_id=<?= $event['event_id'] ?>"
                               style="background-color: #28a745; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none; display: inline-block; margin-top: 15px;">
                                S'inscrire
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if (isset($user) && $user['user_status'] === 'BDE') : ?>
                        <div style="text-align: center; margin-top: 15px;">

                            <a href="index.php?page=eventAttendees&id=<?= $event['event_id'] ?>"
                               style="background-color: #6c757d; color: white; padding: 8px 16px; border-radius: 4px; text-decoration: none; display: inline-block; margin-right: 10px;">
                                Voir les inscrits
                            </a>

                            <form method="GET" action="index.php" style="display: inline;">
                                <input type="hidden" name="page" value="updateEvent">
                                <input type="hidden" name="id" value="<?= $event['event_id'] ?>">
                                <button type="submit" style="background-color: #ffc107; color: #212529; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; margin-right: 10px;">
                                    Modifier
                                </button>
                            </form>

                            <form method="post" action="index.php?page=deleteEvent"
                                  onsubmit="return confirm('Supprimer ?');" style="display: inline;">
                                <input type="hidden" name="event_id" value="<?= $event['event_id'] ?>">
                                <?= $csrf->getTokenField() ?>
                                <button type="submit" style="background-color: #dc3545; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">
                                    Supprimer
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($pagination->getTotalPages() > 1) : ?>
            <ul class="pagination">
                <?php if ($pagination->hasPrevious()) : ?>
                    <li><a href="<?= $pagination->getLink($pagination->getFirstPage()) ?>">« Premier</a></li>
                <?php endif; ?>
                <li><a href="<?= $pagination->getLink($pagination->getCurrentPage()) ?>" aria-current="page"><?= $pagination->getCurrentPage() ?></a></li>
                <?php if ($pagination->hasNext()) : ?>
                    <li><a href="<?= $pagination->getLink($pagination->getCurrentPage() + 1) ?>">Suivant ›</a></li>
                <?php endif; ?>
            </ul>
        <?php endif; ?>
    </div>

<?php end_page(); ?>