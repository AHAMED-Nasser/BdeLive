<?php
/**
 * Vue : Histoire du BDE - Version Peaufinée
 * @var array<string, mixed>|null $user
 */
start_page("Notre Histoire - BDE Inform'Aix", true, $user ?? null);
?>

    <main style="padding: 20px; max-width: 900px; margin: 0 auto; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
        <section class="Equip">
            <h1 style="text-align: left; color: #444; font-size: 2.6rem; font-weight: 800; margin-bottom: 30px; border-left: 10px solid #0056b3; padding-left: 20px; line-height: 1.2;">
                L'Épopée du <span style="color: #0056b3;">BDE Inform'Aix</span>
            </h1>

            <div class="bde-text" style="line-height: 1.8; color: #333; font-size: 1.1rem;">
                <p style="margin-bottom: 40px; font-style: italic; color: #555;">
                    "Plus qu'une association, une lignée de passionnés qui transmettent le flambeau (et les scripts) depuis des années."
                </p>

                <div style="margin-bottom: 50px; position: relative; padding-left: 40px; border-left: 3px solid #0056b3;">
                    <div style="position: absolute; left: -11px; top: 0; width: 20px; height: 20px; background: #0056b3; border-radius: 50%; box-shadow: 0 0 10px rgba(0,86,179,0.5);"></div>
                    <h2 style="color: #0056b3; margin-top: 0; font-size: 1.6rem;">2018 : Le "Hello World"</h2>
                    <p>
                        Tout a commencé dans une petite salle de TD avec cinq étudiants et une idée folle : créer une structure pour que les informaticiens ne soient plus seulement des lignes de code dans un terminal, mais une vraie communauté.
                        <br><strong>L'anecdote :</strong> Le premier logo du BDE a été dessiné sur un coin de table pendant un cours de Réseau !
                    </p>
                </div>

                <div style="margin-bottom: 50px; position: relative; padding-left: 40px; border-left: 3px solid #0056b3;">
                    <div style="position: absolute; left: -11px; top: 0; width: 20px; height: 20px; background: #0056b3; border-radius: 50%;"></div>
                    <h2 style="color: #0056b3; margin-top: 0; font-size: 1.6rem;">2020 : Le virage du Discord</h2>
                    <p>
                        Face à la distance, le BDE a migré toute sa vie sociale en ligne. C'est l'année où notre serveur Discord est devenu le QG officiel, accueillant les premières compétitions d'E-sport inter-promos.
                        C'est là que l'esprit de cohésion s'est réellement forgé, entre deux parties de League of Legends et des sessions d'entraide nocturnes pour les projets de C.
                    </p>
                </div>

                <div style="margin-bottom: 50px; position: relative; padding-left: 40px; border-left: 3px solid #0056b3;">
                    <div style="position: absolute; left: -11px; top: 0; width: 20px; height: 20px; background: #0056b3; border-radius: 50%;"></div>
                    <h2 style="color: #0056b3; margin-top: 0; font-size: 1.6rem;">2022 : Plus qu'un bureau, une famille</h2>
                    <p>
                        Le BDE s'agrandit. Les partenariats se multiplient et les événements deviennent légendaires : du premier "Barbecue des Codeurs" aux sorties laser-game. Le pôle design voit le jour, donnant enfin une identité visuelle forte à nos couleurs.
                    </p>
                </div>

                <div style="margin-bottom: 50px; position: relative; padding-left: 40px; border-left: 3px solid #0056b3;">
                    <div style="position: absolute; left: -11px; top: 0; width: 20px; height: 20px; background: #0056b3; border-radius: 50%;"></div>
                    <h2 style="color: #0056b3; margin-top: 0; font-size: 1.6rem;">2024-2025 : L'ère BDE Live</h2>
                    <p>
                        Sous l'impulsion de l'équipe actuelle, le projet <strong>BDE Live</strong> est lancé. L'objectif ? Digitaliser totalement l'expérience membre. Inscriptions aux événements, gestion du profil, accès à l'histoire... le BDE devient une véritable plateforme technologique à l'image de notre formation.
                    </p>
                </div>
            </div>

            <div style="background: linear-gradient(135deg, #f9f9f9 0%, #e9ecef 100%); padding: 30px; border-radius: 15px; text-align: center; margin-top: 60px; border: 1px solid #dee2e6;">
                <h3 style="margin-top: 0; color: #333;">L'aventure continue avec vous !</h3>
                <p style="color: #666; margin-bottom: 25px;">Chaque membre, chaque rire et chaque ligne de code contribue à écrire les prochaines pages.</p>
                <a href="index.php?page=bde_members" style="display: inline-block; padding: 12px 25px; background: #0056b3; color: white; text-decoration: none; border-radius: 30px; font-weight: bold; transition: 0.3s; box-shadow: 0 4px 15px rgba(0,86,179,0.3);">
                    Découvrir l'équipe actuelle
                </a>
            </div>
        </section>
    </main>

<?php
end_page();
?>