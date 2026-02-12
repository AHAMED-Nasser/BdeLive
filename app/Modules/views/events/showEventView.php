<?php
/**
 * Event Details View
 *
 * Displays detailed information about a specific event including
 * description, date, time, location, registration options, and admin controls.
 *
 * Refactored to use Event entity with type-safe getters.
 *
 * @package BdeLive\Views\Events
 * @version 2.0.0 - Data Mapper refactoring
 * @author BdeLive - Group 8
 *
 * @var bool $isAdmin
 * @var \App\Modules\Entities\Event $event Event entity (not array anymore)
 * @var int|null $userId
 * @var \App\Modules\Repositories\EventRegistrationRepository $registrationRepo
 * @var int $totalGroupRegistrants Total count of group registrants
 * @var \App\Core\Security\CsrfProtection $csrf
 */
start_page("BDELive - Evénement : " . $event->getName(), true, $user ?? null);

// Repository pour vérifier les inscriptions
$registrationRepo = new \App\Modules\Repositories\EventRegistrationRepository();
$userId = $user['user_id'] ?? null;
?>

<link rel="stylesheet" href="assets/css/event-show.css">

<div class="container event-detail-page">
    <h1 class="text-center" style="padding: 40px"><?= htmlspecialchars($event->getName()) ?></h1>

    <!-- Messages flash -->
    <?php if (!empty($flash['success'])) : ?>
        <div class="event-flash-success">
            <?= htmlspecialchars($flash['success']) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($flash['error'])) : ?>
        <div class="event-flash-error">
            <?= htmlspecialchars($flash['error']) ?>
        </div>
    <?php endif; ?>

    <div class="event-grid-images">
        <?php
        // Use entity method to get images array
        $images = $event->getImagesArray();
        if ($event->hasImages()) :
            foreach ($images as $index => $img) :
                $src = is_array($img) ? $img['url'] : $img;
                $altText = htmlspecialchars($event->getName()) . ' - Photo ' . ($index + 1);
                $lazyAttr = $index > 0 ? ' loading="lazy"' : '';
                echo '<img src="' . htmlspecialchars($src) . '" alt="' . $altText . '" class="event-gallery-image"' . $lazyAttr . ' decoding="async">';
            endforeach;
        else :
            // No images available
        endif; ?>
    </div>

    <div class="event-info-banner">
        <p><strong>📅 Date :</strong> <?= htmlspecialchars($event->getFormattedDate()) ?></p>
        <p><strong>⏰ Heure :</strong> <?= htmlspecialchars($event->getFormattedTime()) ?></p>
        <p><strong>📍 Lieu :</strong> <?= htmlspecialchars($event->getLocation()) ?></p>
    </div>

    <div class="event-description">
        <h2>Description</h2>
        <p><?= nl2br(htmlspecialchars($event->getDescription())) ?></p>
    </div>

    <div class="registration-section">
        <?php
        // Use entity getters for group event information
        $isGroupEvent = $event->isGroupEvent();
        $teamSize = $event->getTeamSize();
        $eventId = $event->getId();
        ?>

        <?php if ($userId) : ?>
            <?php
            // Vérification de l'inscription
            $isRegistered = $registrationRepo->isUserRegistered((int) $eventId, (int) $userId);
            ?>
            <?php if ($isRegistered) : ?>
                <a href="index.php?page=registerEvent&action=unregister&event_id=<?= $eventId ?>" class="btn-delete">
                    Se désinscrire
                </a>
            <?php else : ?>
                <?php if ($isGroupEvent) : ?>
                    <!-- Événement en groupe -->
                    <a href="index.php?page=groupRegistration&event_id=<?= $eventId ?>" class="btn-group-register">
                        <i class="fas fa-users"></i> S'inscrire en groupe (<?= $teamSize ?> personnes)
                    </a>
                <?php else : ?>
                    <!-- Événement individuel -->
                    <a href="index.php?page=registerEvent&action=register&event_id=<?= $eventId ?>" class="btn-individual-register">
                        S'inscrire à l'événement
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        <?php elseif (isset($user) && $user['user_status'] === 'BDE') : ?>
            <p style="color: var(--text-tertiary); font-size: 23px">🐐 Bien le bonjour Administrateur</p>
        <?php elseif (!isset($userId)) : ?>
            <p>Veuillez vous <a href="index.php?page=login"
                    style="color: var(--color-primary); font-weight: bold;">connecter</a> pour vous inscrire.</p>

        <?php endif; ?>
    </div>

    <?php if (!$event->isGroupEvent() && !empty($registrants)) : ?>
        <div class="registrants-list-section">
            <h2>Liste des inscrits (<?= count($registrants) ?>)</h2>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Promotion</th>
                            <th>Date d'inscription</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registrants as $registrant) : ?>
                            <tr>
                                <td><?= htmlspecialchars($registrant['last_name']) ?></td>
                                <td><?= htmlspecialchars($registrant['first_name']) ?></td>
                                <td><?= htmlspecialchars($registrant['promotion'] ?? 'N/A') ?></td>
                                <td>
                                    <?php
                                    $date = new DateTime($registrant['registration_date']);
                                    echo $date->format('d/m/Y H:i');
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($event->isGroupEvent() && !empty($groupRegistrants)) : ?>
        <div class="registrants-list-section group-registrants-section">
            <h2>Liste des inscrits (<?= $totalGroupRegistrants ?>) — <?= count($groupRegistrants) ?> groupe(s)</h2>

            <?php foreach ($groupRegistrants as $teamNumber => $members) : ?>
                <div class="group-block">
                    <h3 class="group-header">Groupe <?= (int) $teamNumber ?></h3>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Prénom</th>
                                    <th>Promotion</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($members as $member) : ?>
                                    <tr>
                                        <td><?= htmlspecialchars($member['last_name']) ?></td>
                                        <td><?= htmlspecialchars($member['first_name']) ?></td>
                                        <td><?= htmlspecialchars($member['promotion'] ?? 'N/A') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($isAdmin) : ?>
        <div class="admin-zone">
            <h2>Administration de l'événement</h2>
            <div class="admin-zone-actions">
                <a href="index.php?page=updateEvent&id=<?= $eventId ?>" class="btn-edit">Modifier</a>

                <form method="post" action="index.php?page=deleteEvent" style="margin: 0;">
                    <input type="hidden" name="event_id" value="<?= $eventId ?>">
                    <?= $csrf->getTokenField() ?>
                    <button type="submit" class="btn-delete"
                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet événement ? Cette action est irréversible.');">Supprimer</button>
                </form>

                <form action="index.php?page=exportUserEvent" method="post" style="margin: 0;">
                    <input type="hidden" name="id" value="<?= $eventId ?>">
                    <button type="submit" title="Télécharger la liste des participants au format PDF"
                        class="btn-view">Export PDF des inscriptions</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php end_page(); ?>