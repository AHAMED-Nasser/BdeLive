<?php
/**
 * Vue : Liste des Membres du BDE (sans photos)
 *
 * @var array<string, mixed>|null $user
 * @var array<int, array<string, string>> $bdeMembers
 */
start_page("Membres BDE - BDE Inform'Aix", true, $user ?? null);

$bdeMembers = $bdeMembers ?? [];
?>

    <main>
        <section class="Equip" aria-labelledby="bde-members-title">
            <h1 id="bde-members-title" style="text-align: left; color: #444; font-size: 2.4rem; font-weight: 700; margin-bottom: 30px; letter-spacing: 1px; padding-left: 15px;">
                <strong>Liste des Membres du Bureau des Étudiants (BDE)</strong>
            </h1>

            <p class="bde-text" style="max-width: 100%; text-align: left; padding: 10px 20px 40px;">
                Voici la liste détaillée des membres actuels du BDE, leurs rôles et une brève description de leurs responsabilités.
            </p>

            <div class="equipe-container" style="display: flex; flex-direction: column; gap: 40px; padding: 0 20px;">
                <?php if (!empty($bdeMembers)): ?>
                    <?php foreach ($bdeMembers as $member): ?>
                        <article class="equipe-membre" style="display: block; border-left: 5px solid #0056b3; padding-left: 15px; background-color: #f9f9f9; border-radius: 5px; padding: 15px;">
                            <div class="equipe-membre-info">
                                <h2 style="font-size: 1.8rem; margin-top: 0; color: #0056b3;">
                                    <?= htmlspecialchars($member['firstname']) . ' ' . htmlspecialchars($member['lastname']) ?>
                                </h2>
                                <h3 style="font-size: 1.2rem; color: #555; margin-bottom: 10px;">
                                    Rôle : <strong><?= htmlspecialchars($member['role']) ?></strong>
                                </h3>
                                <p style="font-size: 1rem; color: #666; line-height: 1.6;">
                                    <?= htmlspecialchars($member['description']) ?>
                                </p>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Aucun membre du BDE n'est actuellement listé.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>

<?php
end_page();
?>