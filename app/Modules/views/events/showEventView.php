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
 * @var array<mixed> $registrants List of individual registrants
 * @var array<mixed> $groupRegistrants List of team/group registrants
 * @var \App\Core\Security\CsrfProtection $csrf
 */
start_page("BDELive - Evénement : " . $event->getName(), true, $user ?? null);

// Repository to check registrations
$registrationRepo = new \App\Modules\Repositories\EventRegistrationRepository();
$userId = $user['user_id'] ?? null;
$totalGroupRegistrants = $totalGroupRegistrants ?? 0;
$descriptionHtml = \App\Core\Markdown\MarkdownRenderer::toHtml($event->getDescription());
?>

<link rel="stylesheet" href="assets/css/pages/event-show.css">
<?php if ($isAdmin) : ?>
    <link rel="stylesheet" href="assets/css/pages/manage-registrants.css">
<?php endif; ?>

<?php
$backHref = !empty($returnUrl) ? htmlspecialchars($returnUrl) : 'index.php?page=event';
$backLabel = !empty($returnUrl) ? 'Retour au calendrier' : 'Retour aux événements';
?>
<div class="container event-detail-page">
    <a href="<?= $backHref ?>" class="back-link">
        <i class="fas fa-arrow-left"></i> <?= htmlspecialchars($backLabel) ?>
    </a>
    <h1 class="text-center event-page-title"><?= htmlspecialchars($event->getName()) ?></h1>

    <!-- Flash messages -->
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

    <div class="event-description-container">
        <h2>Description</h2>
        <div class="event-description-content"><?= $descriptionHtml ?></div>
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
                // Checking the registration
                $isRegistered = $registrationRepo->isUserRegistered((int) $eventId, (int) $userId);
            ?>
            <?php if ($event->getDate() < date('Y-m-d')) : ?>
                <p style="color: #e45a4e; font-weight: bold; font-size: 1.1rem; border: solid #e45a4e 1px; padding: 10px 0; border-radius: 10px">
                    <i class="fa-solid fa-triangle-exclamation" style="padding: 0 10px"></i>
                    The registrations are closed for this event.
                    <i class="fa-solid fa-triangle-exclamation" style="padding: 0 10px"></i>
                </p>
            <?php elseif ($event->getDate() === date('Y-m-d')) : ?>
                <p style="color: #f39c12; font-weight: bold; font-size: 1.1rem; border: solid #f39c12 1px; padding: 10px 0; border-radius: 10px">
                    <i class="fa-solid fa-hourglass-half" style="padding: 0 10px"></i>
                    Événement en cours ! Les inscriptions sont fermées.
                    <i class="fa-solid fa-hourglass-half" style="padding: 0 10px"></i>
                </p>
            <?php else : ?>
                <?php if ($isRegistered) : ?>
                    <a href="index.php?page=registerEvent&action=unregister&event_id=<?= $eventId ?>" class="btn-delete">
                        Se désinscrire
                    </a>
                <?php else : ?>
                    <?php if ($isGroupEvent) : ?>
                        <!-- Group event -->
                        <a href="index.php?page=groupRegistration&event_id=<?= $eventId ?>" class="btn-group-register">
                            <i class="fas fa-users"></i> S'inscrire en groupe (<?= $teamSize ?> personnes)
                        </a>
                    <?php else : ?>
                        <!-- Individual event -->
                        <a href="index.php?page=registerEvent&action=register&event_id=<?= $eventId ?>" class="btn-individual-register">
                            S'inscrire à l'événement
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif ?>
        <?php else : ?>
            <p>Veuillez vous <a href="index.php?page=login" style="color: var(--color-primary); font-weight: bold;">connecter</a> pour vous inscrire.</p>
        <?php endif ?>
    </div>

    <?php if (!$event->isGroupEvent() && (!empty($registrants) || $isAdmin)) : ?>
        <div class="registrants-list-section" id="registrants-section" data-event-id="<?= $eventId ?>">
            <h2>Liste des inscrits (<?= count($registrants) ?>)</h2>

            <?php if ($isAdmin) : ?>
                <form method="post" action="index.php?page=manageRegistrants" id="manage-registrants-form">
                    <input type="hidden" name="event_id" value="<?= $eventId ?>">
                    <?= $csrf->getTokenField() ?>
                    <?php if (!empty($registrants)) : ?>
                    <div class="manage-registrants-actions manage-registrants-actions-top">
                        <button type="submit" class="btn-remove-registrants" id="btn-remove-registrants" disabled>
                            <i class="fas fa-trash-alt"></i> Supprimer les inscrits sélectionnés
                        </button>
                    </div>
                    <?php endif; ?>

                <?php if (!empty($registrants)) : ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <?php if ($isAdmin) : ?>
                                        <th class="manage-registrants-checkbox">
                                            <input type="checkbox" id="select-all-registrants" title="Tout sélectionner">
                                        </th>
                                    <?php endif; ?>
                                    <th>Nom</th>
                                    <th>Prénom</th>
                                    <th>Promotion</th>
                                    <th>Date d'inscription</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($registrants as $registrant) : ?>
                                    <tr>
                                        <?php if ($isAdmin) : ?>
                                            <td class="manage-registrants-checkbox">
                                                <input type="checkbox" name="user_ids[]" value="<?= (int) $registrant['user_id'] ?>"
                                                    class="registrant-checkbox">
                                            </td>
                                        <?php endif; ?>
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
                <?php else : ?>
                    <p class="empty-registrants-message">Aucun inscrit pour le moment.</p>
                <?php endif; ?>

                </form>

                <!-- Add registrant panel (visible in manage mode) -->
                <div class="add-registrant-panel" id="add-registrant-panel">
                    <h3><i class="fas fa-user-plus"></i> Ajouter des inscrits</h3>

                    <div class="search-container">
                        <input type="text" id="search-user-input" class="search-input"
                            placeholder="Rechercher par nom, prénom ou ID..." autocomplete="off">
                        <div class="search-results-dropdown" id="search-results-dropdown"></div>
                    </div>

                    <div class="selected-users-chips" id="selected-users-chips"></div>

                    <form method="post" action="index.php?page=manageRegistrants" id="add-registrants-form">
                        <input type="hidden" name="event_id" value="<?= $eventId ?>">
                        <input type="hidden" name="action" value="add">
                        <?= $csrf->getTokenField() ?>
                        <div id="add-user-ids-container"></div>
                        <button type="submit" class="btn-add-registrants" id="btn-add-registrants" disabled>
                            <i class="fas fa-user-plus"></i> Ajouter les sélectionnés
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($event->isGroupEvent() && (!empty($groupRegistrants) || $isAdmin)) : ?>
        <div class="registrants-list-section group-registrants-section" id="group-registrants-section"
            data-event-id="<?= $eventId ?>">
            <h2>Liste des inscrits (<?= $totalGroupRegistrants ?>) — <?= count($groupRegistrants) ?> groupe(s)</h2>

            <?php if (!empty($groupRegistrants)) : ?>
                <?php foreach ($groupRegistrants as $teamNumber => $members) : ?>
                    <?php
                    // Get team_id from the first member
                    $currentTeamId = (int) ($members[0]['team_id'] ?? 0);
                    ?>
                    <div class="group-block" data-team-id="<?= $currentTeamId ?>" data-team-number="<?= (int) $teamNumber ?>">
                        <h3 class="group-header">Groupe <?= (int) $teamNumber ?></h3>

                        <?php if ($isAdmin) : ?>
                            <!-- Delete group form (en haut pour rester visible avec beaucoup d'inscrits) -->
                            <form method="post" action="index.php?page=manageRegistrants" class="group-delete-form"
                                style="display:none">
                                <input type="hidden" name="event_id" value="<?= $eventId ?>">
                                <input type="hidden" name="action" value="group_delete">
                                <input type="hidden" name="team_id" value="<?= $currentTeamId ?>">
                                <?= $csrf->getTokenField() ?>
                                <button type="submit" class="btn-delete-group">
                                    <i class="fas fa-trash-alt"></i> Supprimer le groupe <?= (int) $teamNumber ?>
                                </button>
                            </form>
                        <?php endif; ?>

                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <?php if ($isAdmin) : ?>
                                            <th class="manage-registrants-checkbox">
                                                <input type="checkbox" class="select-all-group" title="Tout sélectionner">
                                            </th>
                                        <?php endif; ?>
                                        <th>Nom</th>
                                        <th>Prénom</th>
                                        <th>Promotion</th>
                                        <?php if ($isAdmin) : ?>
                                            <th class="manage-group-move-col" style="display:none">Déplacer</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($members as $member) : ?>
                                        <tr data-user-id="<?= (int) $member['user_id'] ?>">
                                            <?php if ($isAdmin) : ?>
                                                <td class="manage-registrants-checkbox">
                                                    <input type="checkbox" name="user_ids[]" value="<?= (int) $member['user_id'] ?>"
                                                        class="registrant-checkbox group-registrant-checkbox"
                                                        form="group-remove-form-<?= $currentTeamId ?>">
                                                </td>
                                            <?php endif; ?>
                                            <td><?= htmlspecialchars($member['last_name']) ?></td>
                                            <td><?= htmlspecialchars($member['first_name']) ?></td>
                                            <td><?= htmlspecialchars($member['promotion'] ?? 'N/A') ?></td>
                                            <?php if ($isAdmin) : ?>
                                                <td class="manage-group-move-col" style="display:none">
                                                    <select class="group-move-select" data-user-id="<?= (int) $member['user_id'] ?>"
                                                        title="Déplacer vers">
                                                        <option value="">—</option>
                                                        <?php foreach ($groupRegistrants as $otherTeamNum => $otherMembers) : ?>
                                                            <?php if ((int) $otherTeamNum !== (int) $teamNumber) : ?>
                                                                <option value="<?= (int) ($otherMembers[0]['team_id'] ?? 0) ?>">
                                                                    Groupe <?= (int) $otherTeamNum ?>
                                                                </option>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ($isAdmin) : ?>
                            <!-- Remove from group form -->
                            <form method="post" action="index.php?page=manageRegistrants" id="group-remove-form-<?= $currentTeamId ?>"
                                class="group-manage-form" style="display:none">
                                <input type="hidden" name="event_id" value="<?= $eventId ?>">
                                <input type="hidden" name="action" value="group_remove">
                                <?= $csrf->getTokenField() ?>
                                <div class="group-actions-bar" style="display:flex">
                                    <button type="submit" class="btn-remove-registrants btn-group-remove" disabled>
                                        <i class="fas fa-user-minus"></i> Retirer les sélectionnés
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <p class="empty-registrants-message">Aucun groupe inscrit pour le moment.</p>
            <?php endif; ?>

            <?php if ($isAdmin) : ?>
                <!-- Add to group panel (visible in manage mode) -->
                <div class="add-registrant-panel" id="group-add-panel" style="display:none">
                    <h3><i class="fas fa-user-plus"></i> Ajouter des inscrits à un groupe</h3>

                    <div class="search-container">
                        <input type="text" id="group-search-user-input" class="search-input"
                            placeholder="Rechercher par nom, prénom ou ID..." autocomplete="off">
                        <div class="search-results-dropdown" id="group-search-results-dropdown"></div>
                    </div>

                    <div class="selected-users-chips" id="group-selected-users-chips"></div>

                    <form method="post" action="index.php?page=manageRegistrants" id="group-add-form">
                        <input type="hidden" name="event_id" value="<?= $eventId ?>">
                        <input type="hidden" name="action" value="group_add">
                        <?= $csrf->getTokenField() ?>
                        <div id="group-add-user-ids-container"></div>

                        <div class="group-team-selector">
                            <label for="group-team-select">Groupe de destination :</label>
                            <select name="team_id" id="group-team-select" class="group-move-select" required>
                                <option value="">— Sélectionner un groupe —</option>
                                <?php foreach ($groupRegistrants as $teamNum => $teamMembers) : ?>
                                    <option value="<?= (int) ($teamMembers[0]['team_id'] ?? 0) ?>">
                                        Groupe <?= (int) $teamNum ?>
                                    </option>
                                <?php endforeach; ?>
                                <option value="new">＋ Créer un nouveau groupe</option>
                            </select>
                        </div>

                        <button type="submit" class="btn-add-registrants" id="group-btn-add" disabled>
                            <i class="fas fa-user-plus"></i> Ajouter au groupe
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($isAdmin) : ?>
        <div class="admin-zone">
            <h2>Administration de l'événement</h2>
            <div class="admin-zone-actions">
                <a href="index.php?page=updateEvent&id=<?= $eventId ?>" class="btn-edit">Modifier</a>

                <?php if (!$event->isGroupEvent()) : ?>
                    <button type="button" class="btn-manage-registrants" id="btn-toggle-manage">
                        <i class="fas fa-user-edit"></i> Modifier les inscrits
                    </button>
                <?php endif; ?>

                <?php if ($event->isGroupEvent()) : ?>
                    <button type="button" class="btn-manage-registrants" id="btn-toggle-group-manage">
                        <i class="fas fa-users-cog"></i> Modifier les groupes
                    </button>
                <?php endif; ?>

                <form method="post" action="index.php?page=deleteEvent" style="margin: 0;">
                    <input type="hidden" name="event_id" value="<?= $eventId ?>">
                    <?= $csrf->getTokenField() ?>
                    <button type="button" class="btn-delete" data-confirm-title="Supprimer l'événement"
                        data-confirm-msg="Êtes-vous sûr de vouloir supprimer cet événement ? Cette action est irréversible.">Supprimer</button>
                </form>

                <form action="index.php?page=exportUserEvent" method="post" class="inline-form">
                    <input type="hidden" name="id" value="<?= $eventId ?>">
                    <button type="submit" title="Télécharger la liste des participants au format PDF"
                        class="btn-view">Export PDF des inscriptions</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if ($isAdmin) : ?>
    <script src="assets/js/manage-registrants.js"></script>
<?php endif; ?>

<?php end_page(); ?>
