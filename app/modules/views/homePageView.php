<?php
    require_once __DIR__ . "/../../include/include.inc.php";

    start_page("BDE Inform'Aix - Site Officiel", true);
?>
    <main>
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
            <h2 id="events-title">Événements à venir</h2>
            <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
                <ul class="carousel-indicators">
                    <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
                    <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
                    <li data-target="#carouselExampleIndicators" data-slide-to="2"></li>
                </ul>
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img class="d-block w-100" src="/assets/img/event1.png" alt="Premier événement" width="800" height="400" loading="lazy">
                    </div>
                    <div class="carousel-item">
                        <img class="d-block w-100" src="/assets/img/event2.png" alt="Deuxième événement" width="800" height="400" loading="lazy">
                    </div>
                    <div class="carousel-item">
                        <img class="d-block w-100" src="/assets/img/event3.png" alt="Troisième événement" width="800" height="400" loading="lazy">
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

        <section class="social-media" aria-labelledby="social-title">
            <h2 id="social-title">Réseaux sociaux</h2>
            <ul>
                <li><a href="#" aria-label="Follow us on Instagram"><i class="fa-brands fa-instagram"></i></a></li>
                <li><a href="#" aria-label="Join our Discord server"><i class="fa-brands fa-discord"></i></a></li>
                <li><a href="#" aria-label="Follow us on TikTok"><i class="fa-brands fa-tiktok"></i></a></li>
            </ul>
        </section>
    </main>
<?php
    end_page();
?>