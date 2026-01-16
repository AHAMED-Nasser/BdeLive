<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Pwd;

use App\Modules\Controllers\DefaultController;
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
class ForgotPasswordController extends DefaultController
{
    /**
     * Handle forgot password page requests.
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        if ($this->request->isPost()) {
            $this->sendResetEmail();
            return;
        }

        $this->render('pwd/forgotPasswordView');
    }

    /**
     * Process password reset email request.
     * @return void
     */
    private function sendResetEmail(): void
    {
        // Validate CSRF token
        $csrfToken = $this->request->post('csrf_token', '');
        if (!$this->csrf->validateToken((string) $csrfToken)) {
            $this->setError('Jeton de sécurité invalide. Veuillez réessayer.');
            $this->redirect('index.php?page=forgot_password');
        }

        $email = trim((string) $this->request->post('email', ''));

        if ($email === '') {
            $this->setError('Veuillez saisir votre adresse email.');
            $this->redirect('index.php?page=forgot_password');
        }

        try {
            // Charger le modèle
            $passwordReset = new PasswordReset();
            $user = $passwordReset->getUserByEmail($email);

            if (!$user) {
                $this->setError('Aucun compte n\'est associé à cette adresse email.');
                $this->redirect('index.php?page=forgot_password');
            }

            $token = $passwordReset->createToken($user['user_id']);

            if (!$token) {
                $this->setError('Erreur lors de la génération du code.');
                $this->redirect('index.php?page=forgot_password');
            }

            // ✅ Pas besoin de require, autoload le gère via PSR-4
            $mailer = new Mailer();

            $emailSent = $mailer->sendPasswordResetEmail(
                $user['email'],
                $user['first_name'] . ' ' . $user['last_name'],
                $token
            );

            if ($emailSent) {
                $this->session->set('reset_email', $email);
                $this->setSuccess('Un code de vérification a été envoyé à votre adresse email.');
                $this->redirect('index.php?page=verify_token');
            } else {
                $this->setError('Erreur lors de l\'envoi de l\'email. Veuillez réessayer.');
                $this->redirect('index.php?page=forgot_password');
            }
        } catch (Exception $e) {
            $this->setError('Une erreur est survenue : ' . $e->getMessage());
            $this->redirect('index.php?page=forgot_password');
        }
    }
}
