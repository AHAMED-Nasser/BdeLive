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

        $filter = $_GET['filter'] ?? 'active';
        $showBlocked = ($filter === 'blocked');

        $total = $userManager->countUsersByBlockStatus($showBlocked);
        $pagination = new Pagination($total, 15);

        $users = $userManager->getAllUsersPaginated(
            $pagination->getLimit(),
            $pagination->getOffset(),
            $showBlocked
        );

        $this->render("admin/adminSectionView", [
            'users' => $users,
            'pagination' => $pagination, 
            'currentFilter' => $filter
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
        header('Location: index.php?page=adminSection&filter=' . ($_GET['filter'] ?? 'active'));
        exit;
    }
}
