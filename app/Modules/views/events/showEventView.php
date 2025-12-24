<?php
/**
 * @var array<string, mixed> $event
 * @var int|null $userId
 * @var \App\Modules\Repositories\EventRegistrationRepository $registrationRepo
 * @var \App\Core\Security\CsrfProtection $csrf
 */
start_page($event['event_name'], true, $user ?? null);
?>

    <div class="container event-detail-page">
        <h1 class="text-center" style="padding: 40px"><?= htmlspecialchars($event['event_name']) ?></h1>

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

        <div class="event-info-banner" style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 30px; text-align: center;">
            <p><strong>📅 Date :</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($event['event_date']))) ?></p>
            <p><strong>⏰ Heure :</strong> <?= htmlspecialchars(date('H:i', strtotime($event['event_time']))) ?></p>
            <p><strong>📍 Lieu :</strong> <?= htmlspecialchars($event['event_location']) ?></p>
        </div>

        <div class="event-description" style="margin-bottom: 50px; line-height: 1.6;">
            <h3>Description</h3>
            <p><?= nl2br(htmlspecialchars($event['description'])) ?></p>
        </div>

        <div class="registration-section" style="text-align: center; margin: 30px 0; padding: 20px; border-top: 1px solid #eee;">
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
            <?php else : ?>
                <p style="color: #666;">Veuillez vous <a href="index.php?page=login" style="color: #1299ff; font-weight: bold;">connecter</a> pour vous inscrire.</p>
            <?php endif; ?>
        </div>

        <?php if (isset($user) && $user['user_status'] === 'BDE') : ?>
            <div class="admin-zone" style="background: #fff3cd; border: 1px solid #ffeeba; padding: 25px; border-radius: 8px; text-align: center; margin-top: 50px;">
                <h4 style="color: #856404;">Administration de l'événement</h4>
                <div style="margin-top: 15px;">
                    <a href="index.php?page=updateEvent&id=<?= $event['event_id'] ?>" style="background-color: #ffc107; color: #212529; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin-right: 10px; display: inline-block;">Modifier</a>

                    <form method="post" action="index.php?page=deleteEvent" style="display: inline;" onsubmit="return confirm('Supprimer ?');">
                        <input type="hidden" name="event_id" value="<?= $event['event_id'] ?>">
                        <?= $csrf->getTokenField() ?>
                        <button type="submit" style="background-color: #dc3545; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">Supprimer</button>
                    </form>

                    <form action="index.php?page=exportUserEvent" method="post" style="display: inline; margin-left: 10px;">
                        <input type="hidden" name="id" value="<?= $event['event_id'] ?>">
                        <button type="submit" style="background-color: #1299ff; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">Liste (PDF)</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

<?php end_page(); ?>