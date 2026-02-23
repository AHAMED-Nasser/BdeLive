<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Admin;

use App\Core\Application;
use App\Modules\Controllers\AdminController;
use App\Modules\Helpers\Pagination;
use App\Modules\Models\Users\UserManager;

/**
 * Controller responsible for managing the administration section users.
 * Handles listing, filtering, and performing actions (promote, demote, block, unblock) on users.
 *
 * @author BDELIVE - Groupe 8
 * @package App\Modules\Controllers\Admin
 * @version 1.0.0
 */
class AdminSectionController extends AdminController
{
    /**
     * Initializes the controller, handles user actions, and renders the administration view.
     * Processes filters, search, and pagination for the user list.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $userManager = new UserManager();

        if ($this->request->isPost() && $this->request->has('action') && $this->request->has('user_id')) {
            $this->handleAction($userManager);
        }

        // Get and validate parameters
        $filter = $this->request->get('filter', 'active');
        $allowedFilters = ['active', 'blocked', 'deleted'];
        if (!in_array($filter, $allowedFilters, true)) {
            $filter = 'active';
        }
        $showBlocked = ($filter === 'blocked');

        // Validate role filter (security - whitelist validation)
        $allowedRoles = ['all', 'admin', 'user', 'super_admin'];
        $roleFilter = $this->request->get('role', 'all');
        if (!in_array($roleFilter, $allowedRoles, true)) {
            $roleFilter = 'all';
        }

        // Get current user role for view permissions
        $currentUserRole = Application::getInstance()->auth()->getUserRole();

        // Clean search term
        $search = trim((string) $this->request->get('search', ''));

        // Use unified methods with all filters
        if ($filter === 'deleted') {
            $total = $userManager->countDeletedUsers($roleFilter, $search);
        } else {
            $total = $userManager->countUsers($showBlocked, $roleFilter, $search);
        }
        $pagination = new Pagination($total, 15);

        if ($filter === 'deleted') {
            $users = $userManager->getDeletedUsers(
                $pagination->getLimit(),
                $pagination->getOffset(),
                $roleFilter,
                $search
            );
        } else {
            $users = $userManager->getUsers(
                $pagination->getLimit(),
                $pagination->getOffset(),
                $showBlocked,
                $roleFilter,
                $search
            );
        }

        if ($this->request->get('ajax') === '1') {
            $this->render("admin/partials/usersTablePartial", [
                'users' => $users,
                'pagination' => $pagination,
                'currentFilter' => $filter,
                'roleFilter' => $roleFilter,
                'search' => $search,
                'currentUserRole' => $currentUserRole,
            ]);
            exit;
        }

        $this->render("admin/adminSectionView", [
            'users' => $users,
            'pagination' => $pagination,
            'currentFilter' => $filter,
            'roleFilter' => $roleFilter,
            'search' => $search,
            'currentUserRole' => $currentUserRole,
        ]);
    }

    /**
     * Handles administrative actions performed on users.
     * Enforces strict role hierarchy:
     *  - super_admin: all actions on admin/user, no actions on other super_admins
     *  - admin: only block/unblock on user targets
     *
     * @param UserManager $manager The user manager instance to perform operations.
     * @return void
     */
    private function handleAction(UserManager $manager): void
    {
        $id = (int) $this->request->post('user_id');
        $action = (string) $this->request->post('action');

        $auth = Application::getInstance()->auth();
        $currentRole = $auth->getUserRole();
        $targetRole = $manager->getUserRoleById($id);

        // --- Permission enforcement ---
        if ($currentRole === 'super_admin') {
            // Super admin cannot act on other super admins
            if ($targetRole === 'super_admin') {
                $this->redirectWithError(
                    'index.php?page=adminSection',
                    'Action interdite : vous ne pouvez pas agir sur un autre Super Admin.'
                );
            }
        } elseif ($currentRole === 'admin') {
            // Admin can ONLY block/unblock, and ONLY on 'user' targets
            $allowedAdminActions = ['block', 'unblock'];
            if (!in_array($action, $allowedAdminActions, true) || $targetRole !== 'user') {
                $this->redirectWithError(
                    'index.php?page=adminSection',
                    'Action interdite : vous n\'avez pas les droits nécessaires.'
                );
            }
        } else {
            // Regular users should never reach here (AdminController blocks them),
            // but just in case:
            $this->redirectWithError(
                'index.php?page=home',
                'Accès refusé.'
            );
        }

        // --- Execute action ---
        switch ($action) {
            case 'promote':
                $manager->updateUserRole($id, 'admin');
                break;
            case 'demote':
                $manager->updateUserRole($id, 'user');
                break;
            case 'block':
                $manager->setBlockStatus($id, 1);
                break;
            case 'unblock':
                $manager->setBlockStatus($id, 0);
                break;
            case 'soft_delete':
                $manager->softDeleteUser($id);
                break;
            case 'restore':
                $manager->restoreUser($id);
                break;
        }

        // Préserver tous les filtres lors de la redirection
        $params = [
            'page' => 'adminSection',
            'filter' => $this->request->get('filter', 'active'),
            'role' => $this->request->get('role', 'all'),
        ];

        $search = $this->request->get('search');
        if (!empty($search)) {
            $params['search'] = $search;
        }

        $p = $this->request->get('p');
        if (!empty($p)) {
            $params['p'] = $p;
        }

        header('Location: index.php?' . http_build_query($params));
        exit;
    }
}
