<?php
/** @var array<int, array<string, mixed>> $users */
/** @var string $currentFilter */
/** @var string $currentUserRole */
/** @var \App\Modules\Helpers\Pagination $pagination */

$isSuperAdmin = ($currentUserRole === 'super_admin');
$isAdminUser  = ($currentUserRole === 'admin');
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
                    $isDeleted   = !empty($u['deleted_at']);
                    $isBlocked   = (int) ($u['is_blocked'] ?? 0) === 1;
                    $targetRole  = $u['role'] ?? 'user';

                    // Determine if the current user can act on this target
                    $canShowActions = false;
                    if ($isSuperAdmin) {
                        $canShowActions = true;
                    } elseif ($isAdminUser && $targetRole === 'user') {
                        $canShowActions = true;
                    }
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></strong></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td>
                            <?php if ($targetRole === 'super_admin') : ?>
                                <span class="badge-role badge-super-admin">SUPER ADMIN</span>
                            <?php elseif ($targetRole === 'admin') : ?>
                                <span class="badge-role badge-admin">ADMIN</span>
                            <?php else : ?>
                                <span class="badge-role badge-user">MEMBRE</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($isDeleted) : ?>
                                <?php
                                $deletedAt = new \DateTime($u['deleted_at']);
                                $expiresAt = (clone $deletedAt)->modify('+30 days');
                                $daysLeft  = max(0, (int) (new \DateTime())->diff($expiresAt)->days);
                                ?>
                                <span class="badge-role badge-deleted"
                                      title="Supprimé le <?= htmlspecialchars($u['deleted_at']) ?>">
                                    EN ATTENTE (<?= $daysLeft ?> j. restant<?= $daysLeft > 1 ? 's' : '' ?>)
                                </span>
                            <?php elseif ($isBlocked) : ?>
                                <span class="badge-role badge-banned">BANNI</span>
                            <?php else : ?>
                                <span class="badge-role badge-active">ACTIF</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!$canShowActions) : ?>
                                <span style="color: var(--text-tertiary); font-size: 0.8125rem;">—</span>
                            <?php else : ?>
                                <form method="POST">
                                    <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                    <?php if ($isDeleted) : ?>
                                        <?php if ($isSuperAdmin) : ?>
                                            <button type="submit" name="action" value="restore" class="btn-unblock" style="font-size: 0.8125rem; padding: 0.35em 0.75em;">Restaurer</button>
                                        <?php endif; ?>
                                    <?php elseif ($isBlocked) : ?>
                                        <button type="submit" name="action" value="unblock" class="btn-unblock" style="font-size: 0.8125rem; padding: 0.35em 0.75em;">Débloquer</button>
                                    <?php else : ?>
                                        <div class="action-group">
                                            <label for="action-select-<?= $u['user_id'] ?>" class="sr-only">Action pour <?= htmlspecialchars($u['first_name']) ?></label>
                                            <select id="action-select-<?= $u['user_id'] ?>" name="action" class="admin-select-action js-admin-select">
                                                <option value="">Choisir...</option>
                                                <?php if ($isSuperAdmin) : ?>
                                                    <?php if ($targetRole === 'super_admin') : ?>
                                                        <option value="demote_super_admin">Retirer Super Admin</option>
                                                    <?php elseif ($targetRole === 'admin') : ?>
                                                        <option value="demote">Retirer Admin</option>
                                                        <option value="promote_super_admin">Nommer Super Admin</option>
                                                        <option value="block">Bloquer</option>
                                                        <option value="soft_delete">Supprimer</option>
                                                    <?php elseif ($targetRole === 'user') : ?>
                                                        <option value="promote">Nommer Admin</option>
                                                        <option value="promote_super_admin">Nommer Super Admin</option>
                                                        <option value="block">Bloquer</option>
                                                        <option value="soft_delete">Supprimer</option>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                <?php if ($isAdminUser && $targetRole === 'user') : ?>
                                                    <option value="block">Bloquer</option>
                                                <?php endif; ?>
                                            </select>
                                        </div>
                                    <?php endif; ?>
                                </form>
                            <?php endif; ?>
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
