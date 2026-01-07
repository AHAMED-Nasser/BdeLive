<?php
/**
 * Vue : Liste des Membres du BDE (sans photos)
 *
 * @var array<string, mixed>|null $user
 * @var array<int, array<string, string>>|null $bdeMembers
 * @var array<int, array<string, string>>|null $honorMembers
 */

start_page("Membres BDE - BDE Inform'Aix", true, $user ?? null);
?>

    <main style="padding: 20px;">
        <section class="Equip" aria-labelledby="bde-members-title">
            <h1 id="bde-members-title" style="text-align: left; color: #444; font-size: 2.4rem; font-weight: 700; margin-bottom: 30px; letter-spacing: 1px; padding-left: 15px;">
                <strong>L'Équipe du BDE Inform'Aix</strong>
            </h1>

            <div style="display: flex; flex-wrap: wrap; gap: 30px; align-items: flex-start;">

                <div style="flex: 2; min-width: 300px;">
                    <h2 style="color: #0056b3; border-bottom: 2px solid #0056b3; padding-bottom: 10px; margin-bottom: 20px;">Le Bureau</h2>
                    <div class="equipe-container" style="display: flex; flex-direction: column; gap: 20px;">
                        <?php if (!empty($bdeMembers)) : ?>
                            <?php foreach ($bdeMembers as $member) : ?>
                                <article class="equipe-membre" style="display: block; border-left: 5px solid #0056b3; background-color: #f9f9f9; border-radius: 5px; padding: 15px;">
                                    <div class="equipe-membre-info">
                                        <h2 style="font-size: 1.5rem; margin: 0; color: #0056b3;">
                                            <?= htmlspecialchars($member['firstname']) . ' ' . htmlspecialchars($member['lastname']) ?>
                                        </h2>
                                        <h3 style="font-size: 1rem; color: #555; margin: 5px 0;">
                                            Rôle : <strong><?= htmlspecialchars($member['role']) ?></strong>
                                        </h3>
                                        <p style="font-size: 0.95rem; color: #666; line-height: 1.4; margin-bottom: 0;">
                                            <?= htmlspecialchars($member['description']) ?>
                                        </p>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <p>Aucun membre actif listé.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="flex: 1; min-width: 250px; background-color: #fffaf0; padding: 20px; border-radius: 8px; border: 1px solid #ffeeba;">
                    <h2 style="color: #856404; border-bottom: 2px solid #ffc107; padding-bottom: 10px; margin-bottom: 20px;">Membres d'honneur</h2>
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <?php if (!empty($honorMembers)) : ?>
                            <?php foreach ($honorMembers as $honor) : ?>
                                <li style="padding: 10px 0; border-bottom: 1px solid #ffeeba; color: #856404; font-weight: bold; font-size: 1.1rem;">
                                    <?= htmlspecialchars($honor['firstname']) . ' ' . htmlspecialchars($honor['lastname']) ?>
                                </li>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <li>Aucun membre d'honneur.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </section>
    </main>

<?php
end_page();
?>