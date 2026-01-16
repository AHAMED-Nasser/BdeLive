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
                                    <div class="action-group">
                                        <label for="action-select-<?= $u['user_id'] ?>" class="sr-only">Action pour <?= htmlspecialchars($u['first_name']) ?></label>
                                        <select id="action-select-<?= $u['user_id'] ?>" name="action" class="admin-select-action">
                                            <option value="">Choisir...</option>
                                            <option value="<?= $u['role'] === 'admin' ? 'demote' : 'promote' ?>">
                                                <?= $u['role'] === 'admin' ? 'Retirer Admin' : 'Nommer Admin' ?>
                                            </option>
                                            <option value="block" class="text-danger">Bloquer</option>
                                        </select>
                                        <button type="submit" class="btn-apply-action" title="Appliquer l'action">OK</button>
                                    </div>
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

        <nav class="pagination-container" aria-label="Pagination des utilisateurs">
            <ul class="pagination" id="users-pagination">
                <?php if ($pagination->hasPrevious()) : ?>
                    <li>
                        <a href="<?= $pagination->getLink($pagination->getCurrentPage() - 1) ?>" class="pagination-link">&laquo; Précédent</a>
                    </li>
                <?php endif; ?>
                
                <li>
                    <span class="pagination-info" aria-current="page">Page <?= $pagination->getCurrentPage() ?> / <?= $pagination->getTotalPages() ?></span>
                </li>

                <?php if ($pagination->hasNext()) : ?>
                    <li>
                        <a href="<?= $pagination->getLink($pagination->getCurrentPage() + 1) ?>" class="pagination-link">Suivant &raquo;</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>
