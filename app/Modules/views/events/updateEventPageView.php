<?php
/**
 * Vue pour la modification d'un événement
 *
 * @var \App\Core\Security\CsrfProtection $csrf
 * @var array<string, mixed>|null $user
 * @var array<string, string|null> $flash
 * @var array<string, mixed> $event L'événement à modifier (passé par UpdateEventController)
 */
start_page("Modifier l'événement : " . htmlspecialchars($event['event_name']), true, $user ?? null);

// Prépare le tableau des statuts participants pour les cases à cocher
$statusParticipatingArray = explode(',', $event['status_participating'] ?? '');

// Assurez-vous que les dates sont au format YYYY-MM-DD pour les inputs HTML
$eventDateValue = date('Y-m-d', strtotime($event['event_date']));
$eventTimeValue = date('H:i', strtotime($event['event_time']));
?>

    <section class="createEvent">
        <div class="forgot-container">
            <h1 class="title">Modifier l'événement : <?= htmlspecialchars($event['event_name']) ?></h1>

            <?php if (!empty($flash['success'])) : ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($flash['success']) ?>
                </div>
            <?php endif ?>

            <?php if (!empty($flash['error'])) : ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($flash['error']) ?>
                </div>
            <?php endif ?>

            <form id="form" action="index.php?page=updateEvent&id=<?= $event['event_id'] ?>" method="POST" enctype="multipart/form-data">
                <?= $csrf->getTokenField() ?>
                <input type="hidden" name="action" value="submitUpdate">
                <input type="hidden" name="event_id" value="<?= $event['event_id'] ?>">

                <label for="event-name">Nom de l'événement</label>
                <input id="event-name" type="text" name="event-name" placeholder="Nom de l'événement"
                       value="<?= htmlspecialchars($event['event_name']) ?>" required>

                <label for="event-date">Date de l'événement</label>
                <input id="event-date" type="date" name="event-date"
                       value="<?= htmlspecialchars($eventDateValue) ?>" required>

                <label for="event-time">Heure de l'événement</label>
                <input id="event-time" type="time" name="event-time"
                       value="<?= htmlspecialchars($eventTimeValue) ?>">

                <label for="event-location">Lieu de l'événement</label>
                <input id="event-location" name="event-location" type="text" placeholder="Entrer votre lieu"
                       value="<?= htmlspecialchars($event['event_location']) ?>">

                <label for="event-theme">Thème de l'événement</label>
                <input id="event-theme" type="text" name="event-theme" placeholder="Entrer le thème de l'événement (Soirée, ...)"
                       value="<?= htmlspecialchars($event['event_theme']) ?>">

                <!-- Type d'inscription -->
                <?php 
                $isGroupEvent = !empty($event['is_group_event']) && $event['is_group_event'] == 1;
                $teamSize = (int) ($event['team_size'] ?? 1);
                ?>
                <label>Type d'inscription</label>
                <div class="checkbox-container">
                    <article>
                        <input id="event-solo" type="radio" name="event_type" value="solo" <?= !$isGroupEvent ? 'checked' : '' ?> onchange="toggleTeamSizeUpdate()">
                        <label for="event-solo">Inscription individuelle</label>
                    </article>
                    <article>
                        <input id="event-group" type="radio" name="event_type" value="group" <?= $isGroupEvent ? 'checked' : '' ?> onchange="toggleTeamSizeUpdate()">
                        <label for="event-group">Inscription en groupe</label>
                    </article>
                </div>

                <!-- Taille de l'équipe -->
                <div id="team-size-container" style="<?= $isGroupEvent ? 'display: block;' : 'display: none;' ?> margin-top: 15px;">
                    <label for="team-size">Nombre de personnes par groupe</label>
                    <input id="team-size" type="number" name="team_size" min="2" max="20" value="<?= $teamSize > 1 ? $teamSize : 2 ?>" placeholder="Ex: 4">
                    <small style="color: #666; display: block; margin-top: 5px;">Définissez le nombre de membres requis pour former un groupe</small>
                </div>

                <label for="status_participating">Qui peut venir</label>
                <div class="checkbox-container">
                    <?php
                    $statuses = ['BUT 1', 'BUT 2', 'BUT 3', 'Personnel Enseignant'];
                    foreach ($statuses as $status) :
                        $isChecked = in_array($status, $statusParticipatingArray);
                        ?>
                        <article>
                            <input id="<?= strtolower(str_replace(' ', '', $status)) ?>"
                                   type="checkbox"
                                   name="status_participating[]"
                                   value="<?= htmlspecialchars($status) ?>"
                                <?= $isChecked ? 'checked' : '' ?>>
                            <label for="<?= strtolower(str_replace(' ', '', $status)) ?>"><?= htmlspecialchars($status) ?></label>
                        </article>
                    <?php endforeach; ?>
                </div>

                <label for="description">Description de l'événement</label>
                <textarea id="description" placeholder="Venez à notre événement pour ..." name="description" required><?= htmlspecialchars($event['description']) ?></textarea>

                <div class="image-management" style="margin-top: 20px;">
                    <label>Images actuelles (cocher pour supprimer) :</label>
                    <div class="current-images" style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 15px;">
                        <?php
                        $images = json_decode($event['images'] ?? '[]', true) ?: [];
                        foreach ($images as $img) :
                            $url = is_array($img) ? $img['url'] : $img;
                            $pId = is_array($img) ? $img['public_id'] : '';
                            ?>
                            <div class="img-item" style="text-align: center; width: 120px;">
                                <img src="<?= htmlspecialchars($url) ?>" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;">
                                <?php if ($pId) : ?>
                                    <label style="font-size: 0.8em; color: #dc3545; cursor: pointer;">
                                        <input type="checkbox" name="delete_images[]" value="<?= htmlspecialchars($pId) ?>"> Supprimer
                                    </label>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <label for="event_images">Ajouter de nouvelles images :</label>
                    <input type="file" name="event_images[]" id="event_images" multiple accept="image/*" class="form-control">
                </div>

                <div class="form-actions" style="margin-top: 30px; display: flex; flex-direction: column; gap: 10px;">
                    <button type="submit" name="action" value="submitUpdate" style="width: 100%; padding: 12px; background-color: #5a6fd8; color: white; border: none; border-radius: 5px; cursor: pointer;">
                        Enregistrer les modifications
                    </button>

                    <button type="submit" formaction="index.php?page=event" formmethod="POST" formnovalidate style="width: 100%; background-color: #6c757d; color: white; padding: 12px; border-radius: 5px; border: none; cursor: pointer;">
                        Annuler et Retour à la liste
                    </button>
                </div>

            </form>

        </div>
    </section>

<script>
    // Toggle team size visibility based on event type
    function toggleTeamSizeUpdate() {
        const isGroup = document.getElementById('event-group').checked;
        const container = document.getElementById('team-size-container');
        container.style.display = isGroup ? 'block' : 'none';
        if (!isGroup) {
            document.getElementById('team-size').value = '1';
        }
    }
</script>

<?php end_page() ?>
