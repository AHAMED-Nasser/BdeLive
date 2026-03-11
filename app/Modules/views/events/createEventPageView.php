<?php
/**
 * Create Event Page View
 *
 * Displays the form for creating a new event with all necessary fields
 * including name, date, time, location, description, team settings, and image upload.
 *
 * @package BdeLive\Views\Events
 * @version 1.0.0
 * @author BdeLive - Group 8
 *
 * @var \App\Core\Security\CsrfProtection $csrf
 * @var array<string, mixed>|null $user
 * @var array<string, string|null> $flash
 */
$session = \App\Core\Application::getInstance()->session();
$oldName = (string) $session->get('old_event_name', '');
$oldDate = (string) $session->get('old_event_date', '');
$oldTime = (string) $session->get('old_event_time', '');
$oldLocation = (string) $session->get('old_event_location', '');
$oldTheme = (string) $session->get('old_event_theme', '');
$oldStatusParticipating = $session->get('old_status_participating', []);
$oldStatusParticipating = is_array($oldStatusParticipating) ? $oldStatusParticipating : [];
$oldDescription = (string) $session->get('old_event_description', '');
$oldEventType = (string) $session->get('old_event_type', 'solo');
$oldTeamSize = (string) $session->get('old_team_size', '2');
$session->remove('old_event_name');
$session->remove('old_event_date');
$session->remove('old_event_time');
$session->remove('old_event_location');
$session->remove('old_event_theme');
$session->remove('old_status_participating');
$session->remove('old_event_description');
$session->remove('old_event_type');
$session->remove('old_team_size');
start_page("Créer un événement - BDELive", true, $user ?? null) ?>

<section class="createEvent">
    <div class="form-container-wide">
        <h1 class="title">Création d'un événement</h1>

        <?php if (!empty($flash['success'])) : ?>
            <article style="color: #1d7630">
                <?= htmlspecialchars($flash['success']) ?>
            </article>
        <?php endif ?>

        <?php if (!empty($flash['error'])) : ?>
            <article style="color: #922222">
                <?= htmlspecialchars($flash['error']) ?>
            </article>
        <?php endif ?>

        <form id="form" action="index.php?page=createEvent&action=submitEvent" method="POST"
            enctype="multipart/form-data">
            <?= $csrf->getTokenField() ?>
            <label for="event-name">Nom de l'événement</label>
            <input id="event-name" type="text" name="event-name" placeholder="Nom de l'événement" value="<?= htmlspecialchars($oldName) ?>" required>

            <label for="event-date">Date de l'événement</label>
            <input id="event-date" type="date" name="event-date" value="<?= htmlspecialchars($oldDate) ?>" required>

            <label for="event-time">Heure de l'événement</label>
            <input id="event-time" type="time" name="event-time" value="<?= htmlspecialchars($oldTime) ?>">

            <label for="event-location">Lieu de l'événement</label>
            <input id="event-location" name="event-location" type="text" placeholder="Entrer votre lieu" value="<?= htmlspecialchars($oldLocation) ?>">

            <label for="event-theme">Thème de l'événement</label>
            <input id="event-theme" type="text" name="event-theme"
                placeholder="Entrer le thème de l'événement (Soirée, ...)" value="<?= htmlspecialchars($oldTheme) ?>">

            <!-- Registration type -->
            <label>Type d'inscription</label>
            <!-- From Uiverse.io by Pradeepsaranbishnoi -->
            <div class="checkbox-container">
                <div class="input-container">
                    <input id="event-solo" class="radio-button" type="radio" name="event_type" value="solo" <?= $oldEventType !== 'group' ? 'checked' : '' ?> onchange="toggleTeamSize()">
                    <div class="radio-tile">
                        <div class="icon walk-icon">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <label for="event-solo" class="radio-tile-label">Seule</label>
                    </div>
                </div>

                <div class="input-container">
                    <input id="event-group" class="radio-button" type="radio" name="event_type" value="group" <?= $oldEventType === 'group' ? 'checked' : '' ?> onchange="toggleTeamSize()">
                    <div class="radio-tile">
                        <div class="icon car-icon">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <label for="event-group" class="radio-tile-label">Groupes</label>
                    </div>
                </div>
            </div>

            <!-- Team size (visible only for group events) -->
            <div id="team-size-container" style="<?= $oldEventType === 'group' ? 'display: block;' : 'display: none;' ?> margin-top: 15px;">
                <label for="team-size">Nombre de personnes par groupe</label>
                <input id="team-size" type="number" name="team_size" min="2" max="20" value="<?= htmlspecialchars($oldTeamSize) ?>" placeholder="Ex: 4">
                <small style="color: #666; display: block; margin-top: 5px;">Définissez le nombre de membres requis pour
                    former un groupe</small>
            </div>

            <!-- Checkbox -->
            <fieldset class="checkbox-container">
                <legend class="form-label">Qui peut venir</legend>
                <article>
                    <input id="but1" type="checkbox" name="status_participating[]" value="BUT 1" <?= in_array('BUT 1', $oldStatusParticipating, true) ? 'checked' : '' ?>>
                    <label for="but1">BUT 1</label>
                </article>

                <article>
                    <input id="but2" type="checkbox" name="status_participating[]" value="BUT 2" <?= in_array('BUT 2', $oldStatusParticipating, true) ? 'checked' : '' ?>>
                    <label for="but2">BUT 2</label>
                </article>

                <article>
                    <input id="but3" type="checkbox" name="status_participating[]" value="BUT 3" <?= in_array('BUT 3', $oldStatusParticipating, true) ? 'checked' : '' ?>>
                    <label for="but3">BUT 3</label>
                </article>

                <article>
                    <input id="educator" type="checkbox" name="status_participating[]" value="Personnel Enseignant" <?= in_array('Personnel Enseignant', $oldStatusParticipating, true) ? 'checked' : '' ?>>
                    <label for="educator">Personnel Enseignant</label>
                </article>

            </fieldset>

            <div class="description-event">
                <label for="markdown-editor">Description de l'événement</label>
                <textarea id="markdown-editor" data-autosave-id="event_description_create" data-autosave-enabled="false" placeholder="Décrivez votre événement de manière claire. Markdown possible." name="description"><?= htmlspecialchars($oldDescription) ?></textarea>
            </div>

            <div class="insert-image">
                <p class="form-label">Ajouter des images :</p>
                <div id="drop-area">
                    <input id="event-images" name="event-images[]" type="file" accept="image/*" multiple hidden>
                    <div id="image-view">
                        <p id="image-view-text">Glissez déposez ici <br> pour ajouter des images</p>
                    </div>
                </div>
                <!-- image recap -->
                <div id="image-recap"></div>
            </div>


            <button type="submit" data-loading-text="Création en cours...">Créer un événement</button>

        </form>

    </div>
    <script>
        // Toggle team size visibility based on event type
        function toggleTeamSize() {
            const isGroup = document.getElementById('event-group').checked;
            const container = document.getElementById('team-size-container');
            container.style.display = isGroup ? 'block' : 'none';
            if (!isGroup) {
                document.getElementById('team-size').value = '1';
            }
        }
    </script>
</section>


<?php end_page() ?>
