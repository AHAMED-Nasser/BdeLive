<?php
/**
 * Create Event Page View
 *
 * Displays the form for creating a new event with all necessary fields
 * including name, date, time, location, description, team settings, and image upload.
 *
 * @package BdeLive\Views\Events
 * @version 1.0.0
 * @author BdeLive Team
 *
 * @var \App\Core\Security\CsrfProtection $csrf
 * @var array<string, mixed>|null $user
 * @var array<string, string|null> $flash
 */
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
            <input id="event-name" type="text" name="event-name" placeholder="Nom de l'événement" required>

            <label for="event-date">Date de l'événement</label>
            <input id="event-date" type="date" name="event-date" required>

            <label for="event-time">Heure de l'événement</label>
            <input id="event-time" type="time" name="event-time">

            <label for="event-location">Lieu de l'événement</label>
            <input id="event-location" name="event-location" type="text" placeholder="Entrer votre lieu">

            <label for="event-theme">Thème de l'événement</label>
            <input id="event-theme" type="text" name="event-theme"
                placeholder="Entrer le thème de l'événement (Soirée, ...)">

            <!-- Type d'inscription -->
            <label>Type d'inscription</label>
            <div class="checkbox-container">
                <article>
                    <input id="event-solo" type="radio" name="event_type" value="solo" checked
                        onchange="toggleTeamSize()">
                    <label for="event-solo">Inscription individuelle</label>
                </article>
                <article>
                    <input id="event-group" type="radio" name="event_type" value="group" onchange="toggleTeamSize()">
                    <label for="event-group">Inscription en groupe</label>
                </article>
            </div>

            <!-- Taille de l'équipe (visible uniquement pour les événements en groupe) -->
            <div id="team-size-container" style="display: none; margin-top: 15px;">
                <label for="team-size">Nombre de personnes par groupe</label>
                <input id="team-size" type="number" name="team_size" min="2" max="20" value="2" placeholder="Ex: 4">
                <small style="color: #666; display: block; margin-top: 5px;">Définissez le nombre de membres requis pour
                    former un groupe</small>
            </div>

            <!-- checkbox -->
            <!-- checkbox -->
            <fieldset class="checkbox-container">
                <legend class="form-label">Qui peut venir</legend>
                <article>
                    <input id="but1" type="checkbox" name="status_participating[]" value="BUT 1">
                    <label for="but1">BUT 1</label>
                </article>

                <article>
                    <input id="but2" type="checkbox" name="status_participating[]" value="BUT 2">
                    <label for="but2">BUT 2</label>
                </article>

                <article>
                    <input id="but3" type="checkbox" name="status_participating[]" value="BUT 3">
                    <label for="but3">BUT 3</label>
                </article>

                <article>
                    <input id="educator" type="checkbox" name="status_participating[]" value="Personnel Enseignant">
                    <label for="educator">Personnel Enseignant</label>
                </article>

            </fieldset>


            <label for="description">Description de l'événement</label>
            <textarea id="description" placeholder="Venez à notre événement pour ..." name="description"></textarea>

            <div class="insert-image">
                <p class="form-label">Insérer des images d'illustration</p>
                <label for="event-images" id="drop-area">
                    <input id="event-images" name="event-images[]" type="file" accept="image/*" multiple hidden>
                    <div id="image-view">
                        <p id="image-view-text">Glissez dépossé ici <br> pour ajouter une image</p>
                    </div>
                </label>
                <!-- image recap -->
                <div id="image-recap"></div>
            </div>


            <button type="submit" data-loading-text="Création en cours...">Créer un événement</button>

        </form>

    </div>
    <script>
        const date = new Date();
        // Get the current date (jj:mm:AAAA)
        const today = date.toISOString().split("T")[0];
        document.getElementById('event-date').setAttribute("value", today)

        // Get the current time (hh:mm)
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        document.getElementById('event-time').value = `${hours}:${minutes}`;

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
