<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Pwd;

use App\Config\Mailer;
use Exception;
use App\Modules\Models\Pwd\PasswordReset;

/**
 * Forgot Password Controller
 *
 * Handles the password reset request process. Validates user email,
 * generates a secure reset token, and sends it via email.
 * First step in the password recovery workflow.
 *
 * @package BdeLive\Controllers
 * @author Mohamed-Amine Boudhib, ...
 * @version 1.0.0
 */
class ForgotPasswordController
{
    /**
     * Handle forgot password page requests.
     */
    public function __construct()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->sendResetEmail();
            return;
        }

        $this->loadView('forgotPasswordView');
    }

    /**
     * Process password reset email request.
     */
    private function sendResetEmail(): void
    {
        // Validate CSRF token
        if (! isset($_POST['csrf_token']) || ! validateCsrfToken($_POST['csrf_token'])) {
            $_SESSION['error'] = 'Jeton de sécurité invalide. Veuillez réessayer.';
            header('Location: index.php?page=forgot_password');
            exit;
        }

        $email = trim($_POST['email'] ?? '');

        if ($email === '') {
            $_SESSION['error'] = 'Veuillez saisir votre adresse email.';
            header('Location: index.php?page=forgot_password');
            exit;
        }

        try {
            // Charger le modèle
            $passwordReset = new PasswordReset();
            $user = $passwordReset->getUserByEmail($email);

            if (! $user) {
                $_SESSION['error'] = 'Aucun compte n\'est associé à cette adresse email.';
                header('Location: index.php?page=forgot_password');
                exit;
            }

            $token = $passwordReset->createToken($user['user_id']);

            if (! $token) {
                $_SESSION['error'] = 'Erreur lors de la génération du code.';
                header('Location: index.php?page=forgot_password');
                exit;
            }

            // ✅ Pas besoin de require, autoload le gère via PSR-4
            $mailer = new Mailer();

            $emailSent = $mailer->sendPasswordResetEmail(
                $user['email'],
                $user['first_name'] . ' ' . $user['last_name'],
                $token
            );

            if ($emailSent) {
                $_SESSION['reset_email'] = $email;
                $_SESSION['success'] = 'Un code de vérification a été envoyé à votre adresse email.';
                header('Location: index.php?page=verify_token');
            } else {
                $_SESSION['error'] = 'Erreur lors de l\'envoi de l\'email. Veuillez réessayer.';
                header('Location: index.php?page=forgot_password');
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Une erreur est survenue : ' . $e->getMessage();
            header('Location: index.php?page=forgot_password');
        }

        exit;
    }

    /**
     * Load a view file.
     */
    private function loadView(string $viewName): void
    {
        require_once __DIR__ . '/../../views/pwd/' . $viewName . '.php';
    }
}
