<?php
declare(strict_types=1);

/**
 * Delete Account Controllerr/
 *
 * Allows a logged-in user to delete their own account.
 */
class DeleteAccountController {
    private UserManager $userManager;

    public function __construct() {
        require_once __DIR__ . '/../../models/users/UserManager.php';
        $this->userManager = new UserManager();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleDelete();
        } else {
            $this->loadView('deleteAccountView');
        }
    }

    private function loadView(string $viewName): void {
        require_once __DIR__ . '/../../views/users/' . $viewName . '.php';
    }

    private function handleDelete(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }


        if (empty($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Vous devez être connecté pour supprimer votre compte.';
            header('Location: index.php?page=login');
            exit;
        }

        $userId = (int) $_SESSION['user_id'];

        $token = $_POST['csrf_token'] ?? '';
        if (empty($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            $_SESSION['error'] = 'Jeton invalide. Veuillez réessayer.';
            header('Location: index.php?page=delete_account');
            exit;
        }

        try {
            $deleted = $this->userManager->deleteUser($userId);

            if ($deleted) {
                session_unset();
                session_destroy();

                session_start();
                $_SESSION['success'] = 'Votre compte a bien été supprimé ! <br>
                  <a href="index.php?page=register">Cliquez ici pour créer un nouveau compte</a>';

                header('Location: index.php?page=home');
                exit;

            } else {
                $_SESSION['error'] = 'Impossible de supprimer le compte.';
                header('Location: index.php?page=delete_account');
                exit;
            }
        } catch (Exception $e) {
            error_log('DeleteAccountController::handleDelete - ' . $e->getMessage());
            $_SESSION['error'] = 'Erreur serveur. Contactez un administrateur.';
            header('Location: index.php?page=delete_account');
            exit;
        }
    }
}