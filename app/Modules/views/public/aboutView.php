<?php
/**
 * Vue "À Propos" (membre uniquement).
 * Structure similaire à la page de l'équipe (teamView.php) mais sans affichage des photos.
 *
 * @var array<string, mixed>|null $user
 */
start_page("À propos - BDE Inform'Aix", true, $user ?? null);
?>

    <main>
        <section class="Equip" aria-labelledby="about-title">
            <h1 id="about-title" style="text-align: left; color: #444; font-size: 2.4rem; font-weight: 700; margin-bottom: 20px; letter-spacing: 1px; padding-left: 15px;">
                <strong>À propos du BDE Inform'Aix</strong>
            </h1>
            <div class="bde-text" style="max-width: 100%; text-align: left; padding: 10px 20px;">
                <h2>Qui sommes-nous ?</h2>
                <p>
                    Le <strong>BDE Informatique d’Aix</strong> est l'association étudiante dédiée à la
                    vie de la filière informatique. Nous sommes des étudiants bénévoles
                    qui travaillons pour améliorer l'expérience de tous.
                    <br><br>
                    En tant que membre, vous avez accès à des informations exclusives
                    et soutenez directement nos initiatives.
                </p>
            </div>

            <hr style="width: 80%; margin: 30px auto; border-color: #ddd;">

            <div class="equip-text" style="max-width: 100%; text-align: left; padding: 10px 20px;">
                <h2>Notre Mission</h2>
                <p>
                    Notre mission est d'assurer la cohésion, l'entraide et le bien-être des étudiants
                    en organisant des événements, des ateliers thématiques et des projets.
                </p>
                <ul>
                    <li><a href="index.php?page=bde_members">Liste des membres du BDE</a></li>
                </ul>
            </div>
        </section>
    </main>

<?php
end_page();
?>
