<?php
declare(strict_types=1);

/**
 * Delete Account Controller
 *
 * Allows a logged-in user to delete their own account.
 */
class DeleteAccountController extends AuthenticatedController {
    private $userManager;

    public function __construct() {
        parent::__construct();
        require_once __DIR__ . '/../../models/users/UserManager.php';
        $this->userManager = new UserManager();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleDelete();
            return;
        }
        $this->loadView('deleteAccountView');
    }

    protected function loadView($viewName): void {
        require_once __DIR__ . '/../../views/users/' . $viewName . '.php';
    }

    private function handleDelete(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Si l'utilisateur n'est pas connecté
        if (empty($_SESSION['utilisateur_id'])) {
            $_SESSION['error'] = 'Vous devez être connecté pour supprimer votre compte.';
            header('Location: index.php?page=login');
            exit;
        }

        $userId = (int) $_SESSION['utilisateur_id'];

        // Protection en cas d'attaque
        $token = $_POST['csrf_token'] ?? '';
        if (empty($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            $_SESSION['error'] = 'Jeton invalide. Veuillez réessayer.';
            header('Location: index.php?page=delete_account');
            exit;
        }

        try {
            $deleted = $this->userManager->deleteUser($userId);
            if ($deleted) {
                // Confirmation compte supprimé
                if (isset($_COOKIE[session_name()])) {
                    setcookie(session_name(), '', time()-42000, '/');
                }
                session_unset();
                session_destroy();
                session_start();
                $_SESSION['success'] = 'Votre compte a bien été supprimé.';
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
