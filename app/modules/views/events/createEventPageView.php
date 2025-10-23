<?php start_page("Créer un événement") ?>

<section class="createEvent">
    <div class="forgot-container">
        <h1 class="title">Création d'un événement</h1>

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

        <form id="form" action="index.php?page=createEvent&action=submitEvent" method="POST">
            <label for="event-name">Nom de l'événement</label>
            <input id="event-name" type="text" name="event-name" placeholder="Nom de l'événement" required>

            <label for="event-date">Date de l'événement</label>
            <input id="event-date" type="date" name="event-date" required>

            <label for="event-time">Heure de l'événement</label>
            <input id="event-time" type="time" name="event-time">

            <label for="event-location">Lieu de l'événement</label>
            <input id="event-location" name="event-location" type="text" placeholder="Entrer votre lieu">

            <label for="event-theme">Thème de l'événement</label>
            <input id="event-theme" type="text" name="event-theme" placeholder="Entrer le thème de l'événement (Soirée, ...)">

            <!-- checkbox -->
            <label for="status_participating">Qui peut venir</label>
            <div class="checkbox-container">
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

            </div>


            <label for="description">Description de l'événement</label>
            <textarea id="description" placeholder="Venez à notre événement pour ..." name="description"></textarea>

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


            <button type="submit">Créer un événement</button>

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
    </script>
</section>


<?php end_page() ?>