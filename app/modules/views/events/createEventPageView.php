<?php start_page("Créer un événement") ?>

<section class="createEvent">
    <div class="forgot-container">
        <h1 class="title">Création d'un événement</h1>

        <form id="form" action="index.php?page=event" method="POST">
            <label for="event-name">Nom de l'événement</label>
            <input id="event-name" type="text" name="event-name" placeholder="Nom de l'événement" required>

            <label for="event-date">Date de l'événement</label>
            <input id="event-date" type="date" name="event-date" required>

            <label for="event-time">Heure de l'événement</label>
            <input id="event-time" type="time" name="event-time">

            <label for="event-place">Lieu de l'événement</label>
            <input id="event-place" type="text" placeholder="Entrer votre lieu">

            <label for="event-theme">Thème de l'événement</label>
            <input id="event-theme" type="text" placeholder="Entrer le thème de l'événement (Soirée, ...)">

            <label for="event-who">Qui peut venir</label>
            <input id="event-who" type="checkbox" name="event-who" value="BUT1">

            <label>Insérer des images d'illustration</label>
            <label for="event-images" id="drop-area">
                <input id="event-images" type="file" accept="image/*" hidden>
                <div id="image-view">
                    <p>Glissez dépossé ici <br> pour ajouter une image</p>
                </div>
            </label>
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