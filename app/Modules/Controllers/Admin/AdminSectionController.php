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

        $adminCount      = $userManager->countActiveUsersByRole('admin');
        $superAdminCount = $userManager->countActiveUsersByRole('super_admin');

        if ($this->request->get('ajax') === '1') {
            $this->render("admin/partials/usersTablePartial", [
                'users'          => $users,
                'pagination'     => $pagination,
                'currentFilter'  => $filter,
                'roleFilter'     => $roleFilter,
                'search'         => $search,
                'currentUserRole' => $currentUserRole,
                'adminCount'     => $adminCount,
                'superAdminCount' => $superAdminCount,
            ]);
            exit;
        }

        $this->render("admin/adminSectionView", [
            'users'          => $users,
            'pagination'     => $pagination,
            'currentFilter'  => $filter,
            'roleFilter'     => $roleFilter,
            'search'         => $search,
            'currentUserRole' => $currentUserRole,
            'adminCount'     => $adminCount,
            'superAdminCount' => $superAdminCount,
        ]);
    }

    /**
     * Handles administrative actions performed on users.
     * Enforces strict role hierarchy:
     *  - super_admin: all actions on admin/user (including promote to super_admin), no actions on other super_admins
     *  - admin: only block/unblock on user targets
     *
     * @param UserManager $manager The user manager instance to perform operations.
     * @return void
     */
    private function handleAction(UserManager $manager): void
    {
        $id = (int) $this->request->post('user_id');
        $action = (string) $this->request->post('action');
        $success = false;

        $auth = Application::getInstance()->auth();
        $currentRole = $auth->getUserRole();
        $targetRole = $manager->getUserRoleById($id);

        // --- Permission enforcement ---
        if ($currentRole === 'super_admin') {
            // Super admin can only demote_super_admin, unblock or restore on other super admins (including self)
            if ($targetRole === 'super_admin') {
                $allowedOnSuperAdmin = ['demote_super_admin', 'unblock', 'restore'];
                if (!in_array($action, $allowedOnSuperAdmin, true)) {
                    $this->redirectWithError(
                        'index.php?page=adminSection',
                        'Action interdite : vous ne pouvez que retirer le rôle Super Admin, débloquer ou restaurer.'
                    );
                }
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

        // --- Guard: at least 1 active admin and 1 active super_admin must always remain ---
        $destructiveOnAdmin      = ['soft_delete', 'demote', 'block'];
        $destructiveOnSuperAdmin = ['soft_delete', 'demote_super_admin', 'block'];

        if ($targetRole === 'admin' && in_array($action, $destructiveOnAdmin, true)) {
            if ($manager->countActiveUsersByRole('admin') <= 1) {
                $this->redirectWithError(
                    'index.php?page=adminSection',
                    'Action impossible : il doit rester au moins un administrateur actif.'
                );
            }
        }

        if ($targetRole === 'super_admin' && in_array($action, $destructiveOnSuperAdmin, true)) {
            if ($manager->countActiveUsersByRole('super_admin') <= 1) {
                $this->redirectWithError(
                    'index.php?page=adminSection',
                    'Action impossible : il doit rester au moins un super administrateur actif.'
                );
            }
        }

        // --- Execute action ---
        switch ($action) {
            case 'promote':
                $success = $manager->updateUserRole($id, 'admin');
                break;
            case 'promote_super_admin':
                $success = $manager->updateUserRole($id, 'super_admin');
                break;
            case 'demote':
                $success = $manager->updateUserRole($id, 'user');
                break;
            case 'demote_super_admin':
                $success = $manager->updateUserRole($id, 'admin');
                break;
            case 'block':
                $success = $manager->setBlockStatus($id, 1);
                break;
            case 'unblock':
                $success = $manager->setBlockStatus($id, 0);
                break;
            case 'soft_delete':
                $success = $manager->softDeleteUser($id);
                break;
            case 'restore':
                $success = $manager->restoreUser($id);
                break;
        }

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Action effectuée avec succès.' : 'Erreur lors de l\'opération'
            ]);
            exit();
        }

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
