<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Admin;

use App\Modules\Controllers\AdminController;
use App\Modules\Helpers\Pagination;
use App\Modules\Models\Users\UserManager;

class AdminSectionController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $userManager = new UserManager();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['user_id'])) {
            $this->handleAction($userManager);
        }

        // Get and validate parameters
        $filter = $_GET['filter'] ?? 'active';
        $showBlocked = ($filter === 'blocked');

        // Validate role filter (security - whitelist validation)
        $allowedRoles = ['all', 'admin', 'user'];
        $roleFilter = $_GET['role'] ?? 'all';
        if (!in_array($roleFilter, $allowedRoles, true)) {
            $roleFilter = 'all';
        }

        // Clean search term
        $search = trim($_GET['search'] ?? '');

        // Use unified methods with all filters
        $total = $userManager->countUsers($showBlocked, $roleFilter, $search);
        $pagination = new Pagination($total, 15);

        $users = $userManager->getUsers(
            $pagination->getLimit(),
            $pagination->getOffset(),
            $showBlocked,
            $roleFilter,
            $search
        );

        if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
            $this->render("admin/partials/usersTablePartial", [
                'users' => $users,
                'pagination' => $pagination,
                'currentFilter' => $filter,
                'roleFilter' => $roleFilter,
                'search' => $search
            ]);
            exit;
        }

        $this->render("admin/adminSectionView", [
            'users' => $users,
            'pagination' => $pagination,
            'currentFilter' => $filter,
            'roleFilter' => $roleFilter,
            'search' => $search
        ]);
    }

    private function handleAction(UserManager $manager): void
    {
        $id = (int)$_POST['user_id'];
        $action = $_POST['action'];

        switch ($action) {
            case 'promote':
                $manager->updateUserRole($id, 'admin');
                break;
            case 'demote':
                $manager->updateUserRole($id, 'user');
                break;
            case 'block':
                $manager->setBlockStatus($id, 1);
                $manager->updateUserRole($id, 'user');
                break;
            case 'unblock':
                $manager->setBlockStatus($id, 0);
                break;
        }

        // Préserver tous les filtres lors de la redirection
        $params = [
            'page' => 'adminSection',
            'filter' => $_GET['filter'] ?? 'active',
            'role' => $_GET['role'] ?? 'all',
        ];

        if (!empty($_GET['search'])) {
            $params['search'] = $_GET['search'];
        }

        if (!empty($_GET['p'])) {
            $params['p'] = $_GET['p'];
        }

        header('Location: index.php?' . http_build_query($params));
        exit;
    }
}
