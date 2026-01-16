<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Admin;

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
        $showBlocked = ($filter === 'blocked');

        // Validate role filter (security - whitelist validation)
        $allowedRoles = ['all', 'admin', 'user'];
        $roleFilter = $this->request->get('role', 'all');
        if (!in_array($roleFilter, $allowedRoles, true)) {
            $roleFilter = 'all';
        }

        // Clean search term
        $search = trim((string) $this->request->get('search', ''));

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

        if ($this->request->get('ajax') === '1') {
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

    /**
     * Handles administrative actions performed on users.
     * managed actions: promote, demote, block, unblock.
     *
     * @param UserManager $manager The user manager instance to perform operations.
     * @return void
     */
    private function handleAction(UserManager $manager): void
    {
        $id = (int) $this->request->post('user_id');
        $action = (string) $this->request->post('action');

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
