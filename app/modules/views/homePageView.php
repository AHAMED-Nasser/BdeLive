<?php
    start_page("BDE Inform'Aix - Site Officiel", true);
?>
        <?php
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['utilisateur_id'])): ?>
            <div class="alert alert-info">
                Bienvenue, <?= htmlspecialchars($_SESSION['prenom']) ?> <?= htmlspecialchars($_SESSION['nom']) ?> (BUT <?= htmlspecialchars($_SESSION['classe_annee']) ?>) ! 
                <a href="index.php?page=logout">Se déconnecter</a>
            </div>
        <?php endif; ?>
        <section class="hero">
            <h1 id="hero-title">BDE INFORM'AIX</h1>
            <p>Site officiel du BDE, BUT Informatique Aix-en-Provence</p>
        </section>

        <section class="events" aria-labelledby="events-title">
            <h3 id="big-title"><strong>Bienvenue au BDE Informatique d’Aix !</strong></h3>
            <h2 id="events-title">Événements à venir</h2>
            <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
                <ul class="carousel-indicators">
                    <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
                    <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
                    <li data-target="#carouselExampleIndicators" data-slide-to="2"></li>
                </ul>
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img class="d-block w-100" src="./assets/img/event1.png" alt="Premier événement" width="800" height="600" loading="lazy">
                    </div>
                    <div class="carousel-item">
                        <img class="d-block w-100" src=".//assets/img/event2.png" alt="Deuxième événement" width="800" height="600" loading="lazy">
                    </div>
                    <div class="carousel-item">
                        <img class="d-block w-100" src="./assets/img/event3.png" alt="Troisième événement" width="800" height="600" loading="lazy">
                    </div>
                </div>
                <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="sr-only">Précédent</span>
                </a>
                <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="sr-only">Suivant</span>
                </a>
            </div>
        </section>


        <section class="BDE" aria-labelledby="bde-title">
            <h3> <strong>Qui sommes nous ?</strong></h3>
            <div class="bde-text">
                <p>
                    <h2>Le <strong>BDE Informatique d’Aix</strong> est une association étudiante qui rassemble les passionnés de
                    technologies, de programmation et de cybersécurité.
                    Nous organisons des événements, des ateliers et des soirées pour créer du lien entre les étudiants
                    en informatique et favoriser l’entraide au sein du campus.

                    Nos valeurs : <strong>convivialité, partage et innovation</strong>.
                    Que tu sois développeur, gamer ou simplement curieux, le BDE Info Aix est fait pour toi !
                </p> </h2>
            </div>
        </section>
            <a href="index.php" class="btn-image-right">
                <img src="./assets/img/fleche-accueil.png" alt="Aller à l'accueil" class="bde-image">
            </a>


</main>
<?php end_page() ?>
