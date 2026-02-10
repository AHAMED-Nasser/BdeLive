<?php
/** @var array<int, array<string, mixed>> $users */
/** @var string $currentFilter */
/** @var \App\Modules\Helpers\Pagination $pagination */
?>
<div class="table-card" id="users-table-container">
    <div class="table-header">
        <h2>
            <?php if ($currentFilter === 'blocked') : ?>
                Liste des comptes (Bloqués)
            <?php elseif ($currentFilter === 'deleted') : ?>
                Liste des comptes (Supprimés)
            <?php else : ?>
                Liste des comptes (Actifs)
            <?php endif; ?>
        </h2>
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
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $u) : ?>
                    <?php
                    $isDeleted = !empty($u['deleted_at']);
                    $isBlocked = (int) ($u['is_blocked'] ?? 0) === 1;
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></strong></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td>
                            <span class="badge-role <?= $u['role'] === 'admin' ? 'badge-admin' : 'badge-user' ?>" style="font-size: 0.75rem; padding: 0.25em 0.5em; display: inline-block;"><?= strtoupper(htmlspecialchars($u['role'] ?? 'user')) ?></span>
                        </td>
                        <td>
                            <?php if ($isDeleted) : ?>
                                <span class="badge-role badge-deleted" style="font-size: 0.75rem; padding: 0.25em 0.5em; display: inline-block; background-color: #6c757d; color: #fff;">SUPPRIMÉ</span>
                            <?php elseif ($isBlocked) : ?>
                                <span class="badge-role badge-banned" style="font-size: 0.75rem; padding: 0.25em 0.5em; display: inline-block; background-color: #dc3545; color: #fff;">BANNI</span>
                            <?php else : ?>
                                <span class="badge-role badge-active" style="font-size: 0.75rem; padding: 0.25em 0.5em; display: inline-block; background-color: #28a745; color: #fff;">ACTIF</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                <?php if ($isDeleted) : ?>
                                    <button type="submit" name="action" value="restore" class="btn-unblock" style="font-size: 0.8125rem; padding: 0.35em 0.75em;">Restaurer</button>
                                <?php elseif ($isBlocked) : ?>
                                    <button type="submit" name="action" value="unblock" class="btn-unblock" style="font-size: 0.8125rem; padding: 0.35em 0.75em;">Débloquer</button>
                                <?php else : ?>
                                    <div class="action-group">
                                        <label for="action-select-<?= $u['user_id'] ?>" class="sr-only">Action pour <?= htmlspecialchars($u['first_name']) ?></label>
                                        <select id="action-select-<?= $u['user_id'] ?>" name="action" class="admin-select-action">
                                            <option value="">Choisir...</option>
                                            <option value="<?= $u['role'] === 'admin' ? 'demote' : 'promote' ?>">
                                                <?= $u['role'] === 'admin' ? 'Retirer Admin' : 'Nommer Admin' ?>
                                            </option>
                                            <option value="block">Bloquer</option>
                                            <option value="soft_delete">Supprimer</option>
                                        </select>
                                        <button type="submit" class="btn-apply-action" title="Appliquer l'action">OK</button>
                                    </div>
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
                        <a href="<?= $pagination->getLink($pagination->getCurrentPage() - 1) ?>" class="pagination-link" style="font-size: 0.875rem; padding: 0.35em 0.75em;">&laquo; Précédent</a>
                    </li>
                <?php endif; ?>
                
                <li>
                    <span class="pagination-info" aria-current="page">Page <?= $pagination->getCurrentPage() ?> / <?= $pagination->getTotalPages() ?></span>
                </li>

                <?php if ($pagination->hasNext()) : ?>
                    <li>
                        <a href="<?= $pagination->getLink($pagination->getCurrentPage() + 1) ?>" class="pagination-link" style="font-size: 0.875rem; padding: 0.35em 0.75em;">Suivant &raquo;</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>
