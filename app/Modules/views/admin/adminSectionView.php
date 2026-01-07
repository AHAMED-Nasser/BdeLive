<?php

declare(strict_types=1);

/** @var $users */
/** @var $currentFilter */
/** @var $pagination */
start_page("Administration", true, $user ?? null);
?>
    <div class="admin-container">
        <aside class="admin-sidebar">
            <h3>Filtres</h3>
            <nav>
                <ul class="admin-nav-list">
                    <li class="admin-nav-item">
                        <a href="index.php?page=admin-section&filter=active"
                           class="admin-nav-link <?= $currentFilter === 'active' ? 'active' : '' ?>">
                            <i class="fas fa-user-check"></i> Utilisateurs Actifs
                        </a>
                    </li>
                    <li class="admin-nav-item">
                        <a href="index.php?page=admin-section&filter=blocked"
                           class="admin-nav-link <?= $currentFilter === 'blocked' ? 'active' : '' ?>">
                            <i class="fas fa-user-slash"></i> Utilisateurs Bloqués
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <main class="admin-main">
            <h1>Gestion des Comptes (<?= $currentFilter === 'blocked' ? 'Bloqués' : 'Actifs' ?>)</h1>

            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($users as $u) : ?>
                        <tr>
                            <td><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <span class="role-badge <?= $u['role'] === 'admin' ? 'role-admin' : 'role-user' ?>">
                                    <?= htmlspecialchars($u['role'] ?? 'user') ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">

                                    <?php if ($currentFilter !== 'blocked') : ?>
                                        <select name="action" onchange="this.form.submit()" class="admin-select">
                                            <option value="">Actions...</option>
                                            <?php if ($u['role'] === 'admin') : ?>
                                                <option value="demote">Enlever droits Admin</option>
                                            <?php else : ?>
                                                <option value="promote">Promouvoir Admin</option>
                                            <?php endif; ?>
                                            <option value="block">Bloquer l'utilisateur</option>
                                        </select>
                                    <?php else : ?>
                                        <button type="submit" name="action" value="unblock" class="btn-unblock">
                                            Débloquer
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                <?php if ($pagination->hasPrevious()) : ?>
                    <a href="<?= $pagination->getLink($pagination->getCurrentPage() - 1) ?>">&laquo; Précédent</a>
                <?php endif; ?>

                <span class="pagination-info">Page <?= $pagination->getCurrentPage() ?> / <?= $pagination->getTotalPages() ?></span>

                <?php if ($pagination->hasNext()) : ?>
                    <a href="<?= $pagination->getLink($pagination->getCurrentPage() + 1) ?>">Suivant &raquo;</a>
                <?php endif; ?>
            </div>
        </main>
    </div>

<?php
end_page();
?>