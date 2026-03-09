<?php

declare(strict_types=1);

/**
 * Admin Section View
 *
 * Displays the admin dashboard with user management options including
 * search, filtering by status (active/blocked), and role-based filtering.
 *
 * @package BdeLive\Views\Admin
 * @version 1.0.0
 * @author BdeLive Team
 *
 * @var array<int, array<string, mixed>> $users
 * @var string $currentFilter
 * @var string $roleFilter
 * @var string $search
 * @var \App\Modules\Helpers\Pagination $pagination
 * @var array<string, mixed>|null $user
 */

start_page("Administration | BDE Live", true, $user ?? null);
?>

    <section class="admin-hero">
        <div class="admin-hero-content">
            <h1>Administration | BDE Live</h1>
            <p>Gérez les utilisateurs, les rôles et les accès à la plateforme BdeLive.</p>
        </div>
    </section>

    <div class="admin-layout">
        <aside class="admin-sidebar">
        <h2>Navigation</h2>


        <!-- Formulaire de recherche -->
        <form method="GET" class="admin-search-form">
            <input type="hidden" name="page" value="adminSection">
            <input type="hidden" name="filter" value="<?= $currentFilter ?>">
            <input type="hidden" name="role" value="<?= $roleFilter ?>">

            <div class="search-group">
                <label for="admin-search-input" class="sr-only">Rechercher</label>
                <input type="text"
                       id="admin-search-input"
                       name="search"
                       placeholder="Rechercher par nom, email..."
                       value="<?= htmlspecialchars($search) ?>"
                       class="admin-search-input">
                <button type="submit" class="admin-search-btn" title="Rechercher">
                    <i class="fas fa-search"></i>
                </button>

                <?php if (!empty($search)) : ?>
                    <a href="index.php?page=adminSection&filter=<?= $currentFilter ?>&role=<?= $roleFilter ?>"
                       class="admin-reset-btn"
                       title="Effacer la recherche">
                        <i class="fas fa-times"></i>
                    </a>
                <?php endif; ?>
            </div>

            <?php if (!empty($search)) : ?>
                <div class="search-indicator">
                    Recherche : <strong><?= htmlspecialchars($search) ?></strong>
                </div>
            <?php endif; ?>
        </form>

        <!-- Actions d'exportation-->
        <nav class="filter-section">
            <h3 class="filter-title">Actions</h3>
            <ul class="admin-nav-list">
                <li>
                    <a href="javascript:void(0);"
                       onclick="submitExport()"
                       class="admin-nav-link">
                        <i class="fas fa-file-pdf"></i> Exporter la liste
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Filtres de Statut -->
        <nav class="filter-section">
            <h3 class="filter-title">Statut</h3>
            <ul class="admin-nav-list">
                <li>
                    <a href="index.php?page=adminSection&filter=active&role=<?= $roleFilter ?>&search=<?= urlencode($search) ?>"
                       class="admin-nav-link <?= $currentFilter === 'active' ? 'active' : '' ?>">
                        <i class="fas fa-users"></i> Actifs
                    </a>
                </li>
                <li>
                    <a href="index.php?page=adminSection&filter=blocked&role=<?= $roleFilter ?>&search=<?= urlencode($search) ?>"
                       class="admin-nav-link <?= $currentFilter === 'blocked' ? 'active' : '' ?>">
                        <i class="fas fa-user-slash"></i> Bloqués
                    </a>
                </li>
                <li>
                    <a href="index.php?page=adminSection&filter=deleted&role=<?= $roleFilter ?>&search=<?= urlencode($search) ?>"
                       class="admin-nav-link <?= $currentFilter === 'deleted' ? 'active' : '' ?>">
                        <i class="fas fa-user-times"></i> Supprimés
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Filtres de Rôle -->
        <nav class="filter-section">
            <h3 class="filter-title">Rôle</h3>
            <ul class="admin-nav-list role-filters">
                <li>
                    <a href="index.php?page=adminSection&filter=<?= $currentFilter ?>&role=all&search=<?= urlencode($search) ?>"
                       class="admin-nav-link <?= $roleFilter === 'all' ? 'active' : '' ?>">
                        <i class="fas fa-users"></i> Tous
                    </a>
                </li>
                <li>
                    <a href="index.php?page=adminSection&filter=<?= $currentFilter ?>&role=admin&search=<?= urlencode($search) ?>"
                       class="admin-nav-link <?= $roleFilter === 'admin' ? 'active' : '' ?>">
                        <i class="fas fa-user-shield"></i> Admins
                    </a>
                </li>
                <li>
                    <a href="index.php?page=adminSection&filter=<?= $currentFilter ?>&role=super_admin&search=<?= urlencode($search) ?>"
                       class="admin-nav-link <?= $roleFilter === 'super_admin' ? 'active' : '' ?>">
                        <i class="fas fa-crown"></i> Super Admins
                    </a>
                </li>
                <li>
                    <a href="index.php?page=adminSection&filter=<?= $currentFilter ?>&role=user&search=<?= urlencode($search) ?>"
                       class="admin-nav-link <?= $roleFilter === 'user' ? 'active' : '' ?>">
                        <i class="fas fa-user"></i> Membres
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

        <main class="admin-main-content" id="admin-content-area">
            <?php require __DIR__ . '/partials/usersTablePartial.php'; ?>
        </main>
    </div>

    <script src="./assets/js/admin/admin-filters.js"></script>
    <script src="./assets/js/admin/admin-section.js"></script>

    <form id="exportForm" action="index.php?page=exportUserList" method="POST" target="downloadFrame" style="display:none;">
        <input type="hidden" name="filter" id="hidden-filter">
        <input type="hidden" name="role" id="hidden-role">
        <input type="hidden" name="search" id="hidden-search">
    </form>

    <iframe id="downloadFrame" style="display:none;"></iframe>

    <script>
        function submitExport() {
            const form = document.getElementById('exportForm');
            if (form) {
                form.submit();
            } else {
                console.error("Le formulaire d'export est introuvable.");
            }
        }
    </script>


<?php end_page(); ?>
