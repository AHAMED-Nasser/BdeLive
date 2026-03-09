<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Admin;

use App\Modules\Controllers\AdminController;
use App\Modules\Models\Users\UserManager;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Contrôleur responsable de l'exportation de la liste des utilisateurs en PDF.
 * Reprend les filtres de recherche et de statut de l'administration.
 * * @author BDELIVE - Groupe 8
 */
class ExportUserListController extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        $userManager = new UserManager();

        // 1. Récupération et validation des filtres (Logique identique à AdminSectionController)
        $filter = $_REQUEST['filter'] ?? 'active';
        $roleFilter = $_REQUEST['role'] ?? 'all';
        $search = trim((string) ($_REQUEST['search'] ?? ''));

        $showBlocked = ($filter === 'blocked');

        // 2. Récupération des données sans pagination (on veut tout l'export d'un coup)
        // On passe une limite très haute ou on utilise une méthode dédiée sans limit/offset
        if ($filter === 'deleted') {
            $users = $userManager->getDeletedUsers(9999, 0, $roleFilter, $search);
        } else {
            $users = $userManager->getUsers(9999, 0, $showBlocked, $roleFilter, $search);
        }

        $this->generatePdf($users, $filter, $roleFilter, $search);
    }

    /**
     * @param array<int, array<string, mixed>> $users
     * @param string $filter
     * @param string $role
     * @param string $search
     */
    private function generatePdf(array $users, string $filter, string $role, string $search): void
    {
        if (!class_exists('Dompdf\Options')) {
            $autoloadPath = __DIR__ . '/../../../../../vendor/autoload.php';
            if (file_exists($autoloadPath)) {
                require_once $autoloadPath;
            } else {
                throw new \Exception("L'autoloader de Composer est introuvable. Veuillez lancer 'composer install'.");
            }
        }

        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $dompdf = new Dompdf($options);

        ob_start();
        ?>
        <html>
        <head>
            <style>
                body { font-family: Helvetica, sans-serif; font-size: 10pt; color: #333; }
                .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #444; padding-bottom: 10px; }
                .filter-info { font-style: italic; font-size: 9pt; margin-bottom: 10px; color: #666; }
                table { width: 100%; border-collapse: collapse; }
                th { background-color: #f2f2f2; border: 1px solid #ccc; padding: 8px; text-align: left; }
                td { border: 1px solid #ccc; padding: 6px; }
                tr:nth-child(even) { background-color: #fafafa; }
                .role-badge { font-weight: bold; color: #007bff; }
                .status-active { color: #28a745; font-weight: bold; }
                .status-deleted { color: #dc3545; }
                .footer { position: fixed; bottom: 0; width: 100%; text-align: right; font-size: 8pt; }
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