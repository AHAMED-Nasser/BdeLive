<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Admin;

use App\Modules\Controllers\AdminController;
use App\Modules\Models\Users\UserManager;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Controller responsible for exporting the user list to PDF.
 *
 * Reuses the same search and status filters as the admin section,
 * and generates a PDF containing all matching users.
 *
 * @author BDELIVE - Group 8
 */
class ExportUserListController extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        $userManager = new UserManager();

        // 1. Retrieve and validate filters (same logic as AdminSectionController)
        $filter = $_REQUEST['filter'] ?? 'active';
        $roleFilter = $_REQUEST['role'] ?? 'all';
        $search = trim((string) ($_REQUEST['search'] ?? ''));

        $showBlocked = ($filter === 'blocked');

        // 2. Retrieve all matching users without pagination (full export)
        // Use a very high limit or a dedicated method without limit/offset
        if ($filter === 'deleted') {
            $users = $userManager->getDeletedUsers(9999, 0, $roleFilter, $search);
        } else {
            $users = $userManager->getUsers(9999, 0, $showBlocked, $roleFilter, $search);
        }

        $this->generatePdf($users, $filter, $roleFilter, $search);
    }

    /**
     * Generates the PDF and streams it to the browser.
     *
     * @param array<int, array<string, mixed>> $users List of users to export
     * @param string $filter Current status filter
     * @param string $role Current role filter
     * @param string $search Current search query
     */
    private function generatePdf(array $users, string $filter, string $role, string $search): void
    {
        if (!class_exists('Dompdf\Options')) {
            $autoloadPath = __DIR__ . '/../../../../../vendor/autoload.php';
            if (file_exists($autoloadPath)) {
                require_once $autoloadPath;
            } else {
                throw new \Exception("Composer autoloader not found. Please run 'composer install'.");
            }
        }

        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $dompdf = new Dompdf($options);

        // Load external CSS for PDF styling
        $cssPath = __DIR__ . '/../../../assets/css/pages/pdf-user-list.css';
        $cssContent = file_exists($cssPath) ? (string) file_get_contents($cssPath) : '';

        ob_start();
        ?>
        <html>
        <head>
            <style>
                <?= $cssContent ?>
            </style>
        </head>
        <body>
        <div class="header">
            <h1>Liste des Utilisateurs</h1>
        </div>

        <div class="filter-info">
            <strong>Filtres appliqués :</strong> Statut: <?= $filter ?> | Rôle: <?= $role ?>
            <?php if ($search) : ?>
                | Recherche: "<?= htmlspecialchars($search) ?>"
            <?php endif; ?>
            <br>Exporté le : <?= date('d/m/Y H:i') ?>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Nom / Prénom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($users)) : ?>
                <tr><td colspan="5" style="text-align:center;">Aucun utilisateur trouvé.</td></tr>
            <?php else : ?>
                <?php foreach ($users as $user) : ?>
                    <tr>
                        <td><?= htmlspecialchars(strtoupper($user['last_name'] ?? '')) ?> <?= htmlspecialchars($user['first_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($user['email'] ?? '') ?></td>
                        <td class="role-badge"><?= htmlspecialchars($user['role'] ?? 'user') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>

        <div class="footer">Système d'administration BDELIVE</div>
        </body>
        </html>
        <?php
        $html = ob_get_clean();

        if ($html === false) {
            throw new \Exception("Erreur lors de la capture du flux HTML.");
        }

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream("export_utilisateurs_" . date('Y-m-d') . ".pdf", ["Attachment" => true]);
        exit;
    }
}