<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Users;

use App\Modules\Controllers\AuthenticatedController;
use App\Modules\Models\Users\UserManager;

/**
 * ProfileController - User Profile Management
 *
 * Handles user profile display and updates (first name, last name).
 * Only accessible to authenticated users.
 *
 * Features:
 * - Display user profile with current information
 * - Update first name
 * - Update last name
 * - Form validation and CSRF protection
 *
 * @package BdeLive\Controllers\Users
 * @version 1.0.0
 * @author BdeLive Team
 * 
 * @see AuthenticatedController For authentication requirements
 * @see UserManager For database operations
 */
class ProfileController extends AuthenticatedController
{
    public function __construct()
    {
        parent::__construct();
        
        $action = $this->request->get('action', '');
        if ($action === 'processFirstName' && $this->request->isPost()) {
            $this->processFirstName();
        } elseif ($action === 'processLastName' && $this->request->isPost()) {
            $this->processLastName();
        } else {
            $this->render('users/profilePageView');
        }
    }

    public function processFirstName(): void
    {
        // Validate CSRF token
        $csrfToken = $this->request->post('csrf_token', '');
        if (!$this->csrf->validateToken((string) $csrfToken)) {
            $this->setError('Jeton de sécurité invalide. Veuillez réessayer.');
            $this->redirect('index.php?page=profile');
        }
        
        $user = $this->auth->getUser();
        if (!$user || !isset($user['user_id'])) {
            $this->setError('Utilisateur non authentifié');
            $this->redirect('index.php?page=login');
        }
        
        $userId = $user['user_id'];
        $newFirstName = trim((string) $this->request->post('first-name', ''));

        // Verify if first_name field is empty
        if (empty($newFirstName)) {
            $this->setError('Veuillez remplir le champ prénom');
            $this->redirect('index.php?page=profile');
        }

        $userModel = new UserManager();
        $userModel->updateFirstName($userId, $newFirstName);
        
        // Update session data via AuthManager
        $this->session->set('first_name', $newFirstName);
        
        $this->setSuccess('Prénom mis à jour avec succès');
        $this->redirect('index.php?page=profile');
    }

    public function processLastName(): void
    {
        // Validate CSRF token
        $csrfToken = $this->request->post('csrf_token', '');
        if (!$this->csrf->validateToken((string) $csrfToken)) {
            $this->setError('Jeton de sécurité invalide. Veuillez réessayer.');
            $this->redirect('index.php?page=profile');
        }
        
        $user = $this->auth->getUser();
        if (!$user || !isset($user['user_id'])) {
            $this->setError('Utilisateur non authentifié');
            $this->redirect('index.php?page=login');
        }
        
        $userId = $user['user_id'];
        $newLastName = trim((string) $this->request->post('last-name', ''));

        // Verify if last_name field is empty
        if (empty($newLastName)) {
            $this->setError('Veuillez remplir le champ nom');
            $this->redirect('index.php?page=profile');
        }

        $userModel = new UserManager();
        $userModel->updateLastName($userId, $newLastName);
        
        // Update session data via AuthManager
        $this->session->set('last_name', $newLastName);
        
        $this->setSuccess('Nom mis à jour avec succès');
        $this->redirect('index.php?page=profile');
    }
}
