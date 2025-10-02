<?php
    require_once __DIR__ . "/../../include/include.inc.php";

    start_page("BDE Inform'Aix - Site Officiel", true)
?>
    <main>
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
                <li><a href="#" aria-label="Follow us on Instagram"><img src="../../../public/asset/img/icon-instagram.png" alt="Instagram" width="32" height="32" loading="lazy"></a></li>
                <li><a href="#" aria-label="Join our Discord server"><img src="../../../public/asset/img/icon-discord.png" alt="Discord" width="32" height="32" loading="lazy"></a></li>
                <li><a href="#" aria-label="Follow us on TikTok"><img src="../../../public/asset/img/icon-tiktok.png" alt="TikTok" width="32" height="32" loading="lazy"></a></li>
            </ul>
        </section>
    </main>
<?php
    end_page();
?>