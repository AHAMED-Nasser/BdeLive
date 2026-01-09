<?php
/**
 * @var bool $isAdmin
 * @var array<string, mixed> $event
 * @var int|null $userId
 * @var \App\Modules\Repositories\EventRegistrationRepository $registrationRepo
 * @var \App\Core\Security\CsrfProtection $csrf
 */
start_page("Evénement: " . $event['event_name'], true, $user ?? null);
// Les variables $events et $pagination sont définies par EventController

// Repository pour vérifier les inscriptions
$registrationRepo = new \App\Modules\Repositories\EventRegistrationRepository();
$userId = $user['user_id'] ?? null;
?>

    <div class="container event-detail-page">
        <h1 class="text-center" style="padding: 40px"><?= htmlspecialchars($event['event_name']) ?></h1>

        <!-- Messages flash -->
        <?php foreach (['success' => '#d4edda', 'error' => '#f8d7da'] as $type => $color) : ?>
            <?php if (!empty($flash[$type])) : ?>
                <div style="background-color: <?= $color ?>; color: #<?= $type === 'success' ? '155724' : '721c24' ?>; padding: 12px; margin: 20px 0; border: 1px solid #<?= $type === 'success' ? 'c3e6cb' : 'f5c6cb' ?>; border-radius: 4px; text-align: center;">
                    <?= htmlspecialchars($flash[$type]) ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>

        <div class="event-grid-images" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 30px 0;">
            <?php
            $images = !empty($event['images']) ? json_decode($event['images'], true) : [];
            if (!empty($images)) :
                foreach ($images as $img) :
                    $src = is_array($img) ? $img['url'] : $img;
                    echo '<img src="' . htmlspecialchars($src) . '" style="width: 100%; height: 300px; object-fit: cover; border-radius: 8px;">';
                endforeach;
            else : ?>
                <img src="./assets/img/carousel/events/event2.jpg" alt="Default" style="width: 100%; border-radius: 8px;">
            <?php endif; ?>
        </div>

        <div class="event-info-banner">
            <p><strong>📅 Date :</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($event['event_date']))) ?></p>
            <p><strong>⏰ Heure :</strong> <?= htmlspecialchars(date('H:i', strtotime($event['event_time']))) ?></p>
            <p><strong>📍 Lieu :</strong> <?= htmlspecialchars($event['event_location']) ?></p>
        </div>

        <div class="event-description">
            <h3>Description</h3>
            <p><?= nl2br(htmlspecialchars($event['description'])) ?></p>
        </div>

        <div class="registration-section">
            <?php if ($userId) : ?>
                <?php
                    // Vérification de l'inscription
                    $isRegistered = $registrationRepo->isUserRegistered((int)$event['event_id'], (int)$userId);
                ?>
                <?php if ($isRegistered) : ?>
                    <a href="index.php?page=registerEvent&action=unregister&event_id=<?= $event['event_id'] ?>"
                       style="background-color: #dc3545; color: white; border: none; padding: 12px 25px; border-radius: 4px; text-decoration: none; display: inline-block; font-weight: bold;">
                        Se désinscrire
                    </a>
                <?php else : ?>
                    <a href="index.php?page=registerEvent&action=register&event_id=<?= $event['event_id'] ?>"
                       style="background-color: #28a745; color: white; border: none; padding: 12px 25px; border-radius: 4px; text-decoration: none; display: inline-block; font-weight: bold;">
                        S'inscrire à l'événement
                    </a>
                <?php endif; ?>
            <?php elseif (isset($user) && $user['user_status'] === 'BDE') : ?>
                <p style="color: var(--text-tertiary); font-size: 23px">🐐 Bien le bonjour Administrateur</p>
            <?php elseif (!isset($userId)) : ?>
                <p>Veuillez vous <a href="index.php?page=login" style="color: var(--color-primary); font-weight: bold;">connecter</a> pour vous inscrire.</p>

            <?php endif; ?>
        </div>

        <?php if ($isAdmin) : ?>
            <div class="admin-zone" style="background: #fff3cd; border: 1px solid #ffeeba; padding: 25px; border-radius: 8px; text-align: center; margin-top: 50px;">
                <h4 style="color: #856404;">Administration de l'événement</h4>
                <div style="margin-top: 15px; display: flex; flex-wrap: wrap; gap: 10px; justify-content: center;">
                    <a href="index.php?page=updateEvent&id=<?= $event['event_id'] ?>" style="background-color: #ffc107; color: #212529; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block;">Modifier</a>

                    <form method="post" action="index.php?page=deleteEvent" onsubmit="return confirm('Supprimer ?');" style="margin: 0;">
                        <input type="hidden" name="event_id" value="<?= $event['event_id'] ?>">
                        <?= $csrf->getTokenField() ?>
                        <button type="submit" style="background-color: #dc3545; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">Supprimer</button>
                    </form>

                    <form action="index.php?page=exportUserEvent" method="post" style="margin: 0;">
                        <input type="hidden" name="id" value="<?= $event['event_id'] ?>">
                        <button type="submit" title="Télécharger la liste des participants au format PDF" style="background-color: #1299ff; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">Export PDF des inscriptions</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

<?php end_page(); ?>
