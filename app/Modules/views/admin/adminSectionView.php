<?php

declare(strict_types=1);

/** @var array<int, array<string, mixed>> $users */
/** @var string $currentFilter */
/** @var \App\Modules\Helpers\Pagination $pagination */
/** @var array<string, mixed>|null $user */

start_page("Administration", true, $user ?? null);
?>

    <section class="admin-hero">
        <div class="admin-hero-content">
            <h1>Espace Administration</h1>
            <p>Gérez les utilisateurs, les rôles et les accès à la plateforme BdeLive.</p>
        </div>
    </section>

    <div class="admin-layout">
        <aside class="admin-sidebar">
            <h3>Navigation</h3>
            <nav>
                <ul class="admin-nav-list">
                    <li>
                        <a href="index.php?page=admin-section&filter=active"
                           class="admin-nav-link <?= $currentFilter === 'active' ? 'active' : '' ?>">
                            <i class="fas fa-users"></i> Actifs
                        </a>
                    </li>
                    <li>
                        <a href="index.php?page=admin-section&filter=blocked"
                           class="admin-nav-link <?= $currentFilter === 'blocked' ? 'active' : '' ?>">
                            <i class="fas fa-user-slash"></i> Bloqués
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <main class="admin-main-content">
            <div class="table-card">
                <div class="table-header">
                    <h2>Liste des comptes (<?= $currentFilter === 'blocked' ? 'Bloqués' : 'Actifs' ?>)</h2>
                </div>

                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                        <tr>
                            <th>Nom complet</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($users as $u) : ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></strong></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td>
                                    <span class="badge-role <?= $u['role'] === 'admin' ? 'badge-admin' : 'badge-user' ?>">
                                        <?= strtoupper(htmlspecialchars($u['role'] ?? 'user')) ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST">
                                        <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                        <?php if ($currentFilter !== 'blocked') : ?>
                                            <select name="action" onchange="this.form.submit()" class="admin-select-action">
                                                <option value="">Choisir...</option>
                                                <option value="<?= $u['role'] === 'admin' ? 'demote' : 'promote' ?>">
                                                    <?= $u['role'] === 'admin' ? 'Retirer Admin' : 'Nommer Admin' ?>
                                                </option>
                                                <option value="block" class="text-danger">Bloquer</option>
                                            </select>
                                        <?php else : ?>
                                            <button type="submit" name="action" value="unblock" class="btn-unblock">Réactiver</button>
                                        <?php endif; ?>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
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

<?php end_page(); ?>
