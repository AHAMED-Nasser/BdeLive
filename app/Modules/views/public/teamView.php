<?php
start_page("Team - BDE Inform'Aix", true, $user ?? null);
?>

    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Notre Équipe – BDE Informatique d’Aix</title>
        <link rel="stylesheet" href="app/assets/css/member.css">
    </head>
    <body>

    <div class="content-background">
        <section class="team" aria-labelledby="team-title">
            <h1 id="team-title"><strong>Notre Équipe</strong></h1>

            <div class="team-box">
                <div class="team-box1">
                    <div class="member">
                        <img src="assets/img/Nasser.jpg" alt="Photo Nasser – Scrum Master" loading="lazy" width="240" height="240" decoding="async">
                        <div class="member-info">
                            <h3 class="member-name">Nasser AHAMED</h3>
                            <p class="member-role"><strong>Scrum Master/Développeur</strong></p>
                        </div>
                    </div>
                    <div class="member">
                        <img src="assets/img/Mohamed-Amine.jpg" alt="Photo Mohamed – Product Owner" loading="lazy" width="240" height="240" decoding="async">
                        <div class="member-info">
                            <h3 class="member-name">Mohamed-Amine BOUDHIB</h3>
                            <p class="member-role"><strong>Product owner/Dévelopeur</strong></p>
                        </div>
                    </div>
                    <div class="member">
                        <img src="assets/img/Thomas.jpg" alt="Photo Thomas – Développeur" loading="lazy" width="240" height="240" decoding="async">
                        <div class="member-info">
                            <h3 class="member-name">Thomas PALOT</h3>
                            <p class="member-role"><strong>Développeur</strong></p>
                        </div>
                    </div>
                </div>
                <div class="team-box2">
                    <div class="member">
                        <img src="assets/img/Romain.jpg" alt="Photo Romain – Développeur" loading="lazy" width="240" height="240" decoding="async">
                        <div class="member-info">
                            <h3 class="member-name">Romain CANTOR</h3>
                            <p class="member-role"><strong>Développeur</strong></p>
                        </div>
                    </div>
                    <div class="member">
                        <img src="assets/img/WIllem.jpg" alt="Photo Willem – Développeur" loading="lazy" width="240" height="240" decoding="async">
                        <div class="member-info">
                            <h3 class="member-name">Willem CHETIOUI</h3>
                            <p class="member-role"><strong>Développeur</strong></p>
                        </div>
                    </div>
                    <div class="member">
                        <img src="assets/img/amin.jpg" alt="Photo Amin – Développeur" loading="lazy" width="240" height="240" decoding="async">
                        <div class="member-info">
                            <h3 class="member-name">Amin HELALI</h3>
                            <p class="member-role"><strong>Développeur</strong></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="btn-image-right">
                <a href="index.php?page=team" aria-label="Retour à l'accueil">
                    <svg class="bde-image" width="60" height="60" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <circle cx="12" cy="12" r="10" fill="currentColor"/>
                        <path d="M12 8v8M8 12l4-4 4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                    </svg>
                </a>
        </section>
    </div>


    </body>
    </html>

<?php
end_page();
