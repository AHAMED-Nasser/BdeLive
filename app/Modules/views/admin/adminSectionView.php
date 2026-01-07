<?php start_page("Administration", true, $user ?? null); ?>

<div style="display: flex; gap: 20px; margin-top: 20px;">
    <aside style="width: 220px; border-right: 2px solid #eeeeee; padding: 15px;">
        <h3 style="color: #555">Filtres</h3>
        <nav style="padding: 50px 0">
            <ul style="list-style: none; padding: 0;">
                <li style="margin-bottom: 10px;">
                    <a href="index.php?page=admin-section&filter=active" 
                       style="text-decoration: none; color: <?= $currentFilter === 'active' ? '#007bff; font-weight: bold;' : '#333' ?>;">
                       Utilisateurs Actifs
                    </a>
                </li>
                <li>
                    <a href="index.php?page=admin-section&filter=blocked" 
                       style="text-decoration: none; color: <?= $currentFilter === 'blocked' ? '#dc3545; font-weight: bold;' : '#333' ?>;">
                       Utilisateurs Bloqués
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <main style="flex: 1;">
        <h1>Gestion des Comptes (<?= $currentFilter === 'blocked' ? 'Bloqués' : 'Actifs' ?>)</h1>

        <table style="width: 100%; border-collapse: collapse; background: white; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <thead>
                <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6; text-align: left;">
                    <th style="padding: 12px;">Utilisateur</th>
                    <th style="padding: 12px;">Email</th>
                    <th style="padding: 12px;">Rôle actuel</th>
                    <th style="padding: 12px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 12px;"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></td>
                        <td style="padding: 12px;"><?= htmlspecialchars($u['email']) ?></td>
                        <td style="padding: 12px;">
                            <span style="padding: 3px 8px; border-radius: 4px; font-size: 0.85em; background: <?= $u['role'] === 'admin' ? '#e3f2fd; color: #0d47a1;' : '#f5f5f5;' ?>">
                                <?= strtoupper(htmlspecialchars($u['role'] ?? 'user')) ?>
                            </span>
                        </td>
                        <td style="padding: 12px;">
                            <form method="POST">
                                <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                
                                <?php if ($currentFilter !== 'blocked'): ?>
                                    <select name="action" onchange="this.form.submit()" style="padding: 10px; border-radius: 4px; border: solid 1px black; background-color: white;">
                                        <option value="">Actions...</option>
                                        <?php if ($u['role'] === 'admin'): ?>
                                            <option value="demote">Enlever droits Admin</option>
                                        <?php else: ?>
                                            <option value="promote">Promouvoir Admin</option>
                                        <?php endif; ?>
                                        <option value="block" style="color: red;">Bloquer l'utilisateur</option>
                                    </select>
                                <?php else: ?>
                                    <button type="submit" name="action" value="unblock" style="background: #28a745; color: white; border: none; padding: 5px 12px; border-radius: 4px; cursor: pointer;">
                                        Débloquer
                                    </button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="margin-top: 25px; display: flex; align-items: center; gap: 15px;">
            <?php if ($pagination->hasPrevious()): ?>
                <a href="<?= $pagination->getLink($pagination->getCurrentPage() - 1) ?>" style="text-decoration: none; color: #007bff;">&laquo; Précédent</a>
            <?php endif; ?>

            <span style="color: #666;">Page <?= $pagination->getCurrentPage() ?> / <?= $pagination->getTotalPages() ?></span>

            <?php if ($pagination->hasNext()): ?>
                <a href="<?= $pagination->getLink($pagination->getCurrentPage() + 1) ?>" style="text-decoration: none; color: #007bff;">Suivant &raquo;</a>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php end_page(); ?>