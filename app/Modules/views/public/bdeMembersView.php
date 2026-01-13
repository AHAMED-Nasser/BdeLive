<?php
/**
 * BDE Members View
 *
 * Displays the list of current BDE board members and honorary members
 * with their roles and descriptions (without photos).
 *
 * @package BdeLive\Views\Public
 * @version 1.0.0
 * @author BdeLive Team
 *
 * @var array<string, mixed>|null $user
 * @var array<int, array<string, string>>|null $bdeMembers
 * @var array<int, array<string, string>>|null $honorMembers
 */

start_page("Membres BDE - BDELive", true, $user ?? null);
?>

    <main style="padding: 20px;">
        <section class="Equip" aria-labelledby="bde-members-title">
            <h1 id="bde-members-title" class="bde-title">
                <strong>L'Équipe du BDE Inform'Aix</strong>
            </h1>

            <div class="bde-grid">

                <div class="bde-column-main">
                    <h2 class="bde-subtitle">Le Bureau</h2>
                    <div class="bde-members-list">
                        <?php if (!empty($bdeMembers)) : ?>
                            <?php foreach ($bdeMembers as $member) : ?>
                                <article class="bde-card">
                                    <div class="bde-card-content">
                                        <h2 class="bde-member-name">
                                            <?= htmlspecialchars($member['firstname']) . ' ' . htmlspecialchars($member['lastname']) ?>
                                        </h2>
                                        <h3 class="bde-member-role">
                                            Rôle : <strong><?= htmlspecialchars($member['role']) ?></strong>
                                        </h3>
                                        <p class="bde-member-desc">
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

                <div class="bde-column-side">
                    <div class="bde-honor-card">
                        <h2 class="bde-honor-title">Membres d'honneur</h2>
                        <ul class="bde-honor-list">
                            <?php if (!empty($honorMembers)) : ?>
                                <?php foreach ($honorMembers as $honor) : ?>
                                    <li class="bde-honor-item">
                                        <?= htmlspecialchars($honor['firstname']) . ' ' . htmlspecialchars($honor['lastname']) ?>
                                    </li>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <li>Aucun membre d'honneur.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
    </main>

<?php
end_page();
?>