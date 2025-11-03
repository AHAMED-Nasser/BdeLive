<?php start_page("Modifier un événement") ?>

<section class="createEvent">
    <div class="forgot-container">
        <h1 class="title">Modification d'un événement</h1>

        <?php if(isset($_SESSION['success'])): ?>
            <article style="color: #1d7630">
                <?= htmlspecialchars($_SESSION['success']) ?>
            </article>
            <?php unset($_SESSION['success']) ?>
        <?php endif ?>

        <?php if(isset($_SESSION['error'])): ?>
            <article style="color: #922222">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </article>
            <?php unset($_SESSION['error']) ?>
        <?php endif ?>

        <form id="form" action="index.php?page=modifyEvent&action=submitModify" method="POST">
            <input type="hidden" name="event_id" value="<?= htmlspecialchars($eventData['event_id'] ?? '') ?>">
            
            <label for="event-name">Nom de l'événement</label>
            <input id="event-name" type="text" name="event-name" placeholder="Nom de l'événement" 
                   value="<?= htmlspecialchars($eventData['event_name'] ?? '') ?>" required>

            <label for="event-date">Date de l'événement</label>
            <input id="event-date" type="date" name="event-date" 
                   value="<?= htmlspecialchars($eventData['event_date'] ?? '') ?>" required>

            <label for="event-time">Heure de l'événement</label>
            <input id="event-time" type="time" name="event-time" 
                   value="<?= htmlspecialchars($eventData['event_time'] ?? '') ?>">

            <label for="event-location">Lieu de l'événement</label>
            <input id="event-location" name="event-location" type="text" placeholder="Entrer votre lieu"
                   value="<?= htmlspecialchars($eventData['event_location'] ?? '') ?>">

            <label for="event-theme">Thème de l'événement</label>
            <input id="event-theme" type="text" name="event-theme" placeholder="Entrer le thème de l'événement (Soirée, ...)"
                   value="<?= htmlspecialchars($eventData['event_theme'] ?? '') ?>">

            <!-- checkbox -->
            <?php 
                $statusParticipatingValues = isset($eventData['status_participating']) 
                    ? explode(',', $eventData['status_participating']) 
                    : [];
            ?>
            <label for="status_participating">Qui peut venir</label>
            <div class="checkbox-container">
                <article>
                    <input id="but1" type="checkbox" name="status_participating[]" value="BUT 1"
                           <?= in_array('BUT 1', $statusParticipatingValues) ? 'checked' : '' ?>>
                    <label for="but1">BUT 1</label>
                </article>

                <article>
                    <input id="but2" type="checkbox" name="status_participating[]" value="BUT 2"
                           <?= in_array('BUT 2', $statusParticipatingValues) ? 'checked' : '' ?>>
                    <label for="but2">BUT 2</label>
                </article>

                <article>
                    <input id="but3" type="checkbox" name="status_participating[]" value="BUT 3"
                           <?= in_array('BUT 3', $statusParticipatingValues) ? 'checked' : '' ?>>
                    <label for="but3">BUT 3</label>
                </article>

                <article>
                    <input id="educator" type="checkbox" name="status_participating[]" value="Personnel Enseignant"
                           <?= in_array('Personnel Enseignant', $statusParticipatingValues) ? 'checked' : '' ?>>
                    <label for="educator">Personnel Enseignant</label>
                </article>

            </div>


            <label for="description">Description de l'événement</label>
            <textarea id="description" placeholder="Venez à notre événement pour ..." name="description"><?= htmlspecialchars($eventData['description'] ?? '') ?></textarea>

            <div class="insert-image">
                <label>Insérer des images d'illustration</label>
                <label for="event-images" id="drop-area">
                    <input id="event-images" type="file" accept="image/*" hidden>
                    <div id="image-view">
                        <p id="image-view-text">Glissez dépossé ici <br> pour ajouter une image</p>
                    </div>
                </label>
                <!-- image recap -->
                <div id="image-recap"></div>
            </div>


            <button type="submit">Modifier l'événement</button>
            <a href="index.php?page=pagination" style="display: block; text-align: center; margin-top: 15px; color: #666;">Annuler</a>

        </form>

    </div>
</section>


<?php end_page() 

?>
