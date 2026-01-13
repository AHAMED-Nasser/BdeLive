<?php
/**
 * Vue : Histoire du BDE - Version Peaufinée
 * @var array<string, mixed>|null $user
 */
start_page("Notre Histoire - BDELive", true, $user ?? null);
?>

<main class="history-container">
    <section class="Equip">
        <h1 class="history-title">
            L'Épopée du <span>BDE Inform'Aix</span>
        </h1>

        <div class="bde-text">
            <p class="history-intro">
                "Plus qu'une association, une lignée de passionnés qui transmettent le flambeau (et les scripts) depuis
                des années."
            </p>

            <div class="history-timeline-item">
                <div class="history-marker"></div>
                <h2 class="history-year-title">2018 : Le "Hello World"</h2>
                <p class="history-text">
                    Tout a commencé dans une petite salle de TD avec cinq étudiants et une idée folle : créer une
                    structure pour que les informaticiens ne soient plus seulement des lignes de code dans un terminal,
                    mais une vraie communauté.
                    <br><strong>L'anecdote :</strong> Le premier logo du BDE a été dessiné sur un coin de table pendant
                    un cours de Réseau !
                </p>
            </div>

            <div class="history-timeline-item">
                <div class="history-marker"></div>
                <h2 class="history-year-title">2020 : Le virage du Discord</h2>
                <p class="history-text">
                    Face à la distance, le BDE a migré toute sa vie sociale en ligne. C'est l'année où notre serveur
                    Discord est devenu le QG officiel, accueillant les premières compétitions d'E-sport inter-promos.
                    C'est là que l'esprit de cohésion s'est réellement forgé, entre deux parties de League of Legends et
                    des sessions d'entraide nocturnes pour les projets de C.
                </p>
            </div>

            <div class="history-timeline-item">
                <div class="history-marker"></div>
                <h2 class="history-year-title">2022 : Plus qu'un bureau, une famille</h2>
                <p class="history-text">
                    Le BDE s'agrandit. Les partenariats se multiplient et les événements deviennent légendaires : du
                    premier "Barbecue des Codeurs" aux sorties laser-game. Le pôle design voit le jour, donnant enfin
                    une identité visuelle forte à nos couleurs.
                </p>
            </div>

            <div class="history-timeline-item">
                <div class="history-marker"></div>
                <h2 class="history-year-title">2024-2025 : L'ère BDE Live</h2>
                <p class="history-text">
                    Sous l'impulsion de l'équipe actuelle, le projet <strong>BDE Live</strong> est lancé. L'objectif ?
                    Digitaliser totalement l'expérience membre. Inscriptions aux événements, gestion du profil, accès à
                    l'histoire... le BDE devient une véritable plateforme technologique à l'image de notre formation.
                </p>
            </div>
        </div>

        <div class="history-cta-card">
            <h3 class="history-cta-title">L'aventure continue avec vous !</h3>
            <p class="history-cta-text">Chaque membre, chaque rire et chaque ligne de code contribue à écrire les
                prochaines pages.</p>
            <a href="index.php?page=bde_members" class="history-cta-button">
                Découvrir l'équipe actuelle
            </a>
        </div>
    </section>
</main>

<?php
end_page();
?>