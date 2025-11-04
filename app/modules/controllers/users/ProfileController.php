<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Users;

use App\Modules\Controllers\AuthenticatedController;
use App\Modules\Models\Users\UserManager;

class ProfileController extends AuthenticatedController
{
    public function __construct()
    {
        parent::__construct();
        $this->render('users/profilePageView');
        $action = $_GET['action'] ?? '';
        if ($action === 'processFirstName' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $this -> processFirstName();
        } else if ($action === 'processLastName' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $this -> processLastName();
        }
    }

    public function processFirstName() : void {
        $userId = $_SESSION['user_id'];
        $newFirstName = $_POST['first-name'] ?? '';

        // Verify if first_name field is empty
        if (empty($newFirstName)) {
            $_SESSION['error'] = 'Veuillez remplir le champ first_name';
            header("Location: index.php?page=profile");
            exit;
        }

        $userModel = new UserManager();
        $userModel->updateFirstName($userId, $newFirstName);
        $_SESSION['first_name'] = $_POST['first-name'] ?? '';


        header("Location: index.php?page=profile");
        exit;
    }

    public function processLastName() : void {
        $userId = $_SESSION['user_id'];
        $newLastName = $_POST['last-name'] ?? '';

        // Verify if first_name field is empty
        if (empty($newLastName)) {
            $_SESSION['error'] = 'Veuillez remplir le champ first_name';
            header("Location: index.php?page=profile");
            exit;
        }

        $userModel = new UserManager();
        $userModel->updateLastName($userId, $newLastName);
        $_SESSION['last_name'] = $_POST['last-name'] ?? '';


        header("Location: index.php?page=profile");
        exit;
    }

    protected function loadView(string $viewName): void
    {
        $this->render('users/' . $viewName);
    }
}
