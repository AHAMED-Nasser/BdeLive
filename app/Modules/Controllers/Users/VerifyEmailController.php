<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Users;

use App\Modules\Controllers\DefaultController;
use App\Modules\Models\Users\UserManager;
use Exception;

/**
 * Verify Email Controller
 *
 * Handles email verification via token. When a user clicks the verification
 * link in their email, this controller validates the token and activates
 * their account.
 *
 * @package BdeLive\Controllers
 * @author Système de vérification d'email
 * @version 1.0.0
 */
class VerifyEmailController extends DefaultController
{
    /**
     * Handle email verification requests
     *
     * Receives the token via GET parameter, validates it, and activates
     * the user's account if the token is valid.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Get the token from the URL
        $token = trim((string) $this->request->get('token', ''));

        if (empty($token)) {
            $this->setError('Token de vérification manquant');
            $this->render('users/verifyEmailView');
            return;
        }

        try {
            $userManager = new UserManager();
            $result = $userManager->verifyEmailToken($token);

            if ($result['success']) {
                $this->setSuccess(
                    'Votre adresse email a été vérifiée avec succès ! ' .
                    'Vous pouvez maintenant vous connecter à votre compte.'
                );
            } elseif (($result['message'] ?? '') === 'expired') {
                $this->setError(
                    'Votre lien de vérification a expiré. Veuillez vous réinscrire ' .
                    'avec la même adresse email et vérifier votre compte dans les 24 heures.'
                );
            } else {
                $this->setError($result['message'] ?? 'Token de vérification invalide');
            }
        } catch (Exception $e) {
            error_log('VerifyEmailController::__construct - ' . $e->getMessage());
            $this->setError('Une erreur est survenue lors de la vérification. Veuillez réessayer.');
        }

        $this->render('users/verifyEmailView');
    }
}
