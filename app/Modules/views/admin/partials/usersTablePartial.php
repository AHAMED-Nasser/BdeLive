<?php
/** @var array<int, array<string, mixed>> $users */
/** @var string $currentFilter */
/** @var \App\Modules\Helpers\Pagination $pagination */
?>
<div class="table-card" id="users-table-container">
    <div class="table-header">
        <h2>Liste des comptes (<?= $currentFilter === 'blocked' ? 'Bloqués' : 'Actifs' ?>)</h2>
    </div>

    <?php if (empty($users)) : ?>
        <div class="no-results-message" style="text-align: center; padding: 40px; color: var(--text-tertiary);">
            <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 15px; display: block;"></i>
            <p>Aucun utilisateur trouvé pour cette recherche.</p>
        </div>
    <?php else : ?>
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

        <div class="pagination" id="users-pagination">
            <?php if ($pagination->hasPrevious()) : ?>
                <a href="<?= $pagination->getLink($pagination->getCurrentPage() - 1) ?>">&laquo; Précédent</a>
            <?php endif; ?>
            <span class="pagination-info">Page <?= $pagination->getCurrentPage() ?> / <?= $pagination->getTotalPages() ?></span>
            <?php if ($pagination->hasNext()) : ?>
                <a href="<?= $pagination->getLink($pagination->getCurrentPage() + 1) ?>">Suivant &raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
