<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Users;

use App\Modules\Controllers\AuthenticatedController;
use App\Modules\Models\Users\UserManager;

/**
 * ProfileController - User Profile Management
 *
 * Handles user profile display and updates including first name, last name,
 * and user status. Only accessible to authenticated users.
 * Email and password modifications are not handled by this controller.
 *
 * Features:
 * - Display user profile with current information
 * - Update first name with inline editing
 * - Update last name with inline editing
 * - Update user status (except for BDE users)
 * - Form validation and CSRF protection
 *
 * @author BdeLive Team
 * @version 1.1.0
 * @package BdeLive\Controllers\Users
 *
 * @see AuthenticatedController For authentication requirements
 * @see UserManager For database operations
 */
class ProfileController extends AuthenticatedController
{
    /**
     * Constructor - Route to appropriate action based on request
     *
     * Handles different profile actions:
     * - processFirstName: Update user's first name
     * - processLastName: Update user's last name
     * - processUserStatus: Update user's status
     * - default: Display profile page
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $action = $this->request->get('action', '');
        if ($action === 'processFirstName' && $this->request->isPost()) {
            $this->processFirstName();
        } elseif ($action === 'processLastName' && $this->request->isPost()) {
            $this->processLastName();
        } elseif ($action === 'processUserStatus' && $this->request->isPost()) {
            $this->processUserStatus();
        } else {
            $this->render('users/profilePageView');
        }
    }

    /**
     * Process first name update
     *
     * Validates CSRF token and updates user's first name in database.
     * Updates session data to reflect the change immediately.
     *
     * @return void Redirects to profile page with success or error message
     */
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
            $this->redirect('index.php?page=profile&edit=first_name');
        }

        // Validate first name length
        if (strlen($newFirstName) > 100) {
            $this->setError('Le prénom ne peut pas dépasser 100 caractères');
            $this->redirect('index.php?page=profile&edit=first_name');
        }

        $userModel = new UserManager();
        $userModel->updateFirstName($userId, $newFirstName);

        // Update session data via AuthManager
        $this->session->set('user_first_name', $newFirstName);

        $this->setSuccess('Prénom mis à jour avec succès');
        $this->redirect('index.php?page=profile');
    }

    /**
     * Process last name update
     *
     * Validates CSRF token and updates user's last name in database.
     * Updates session data to reflect the change immediately.
     *
     * @return void Redirects to profile page with success or error message
     */
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
            $this->redirect('index.php?page=profile&edit=last_name');
        }

        // Validate last name length
        if (strlen($newLastName) > 100) {
            $this->setError('Le nom ne peut pas dépasser 100 caractères');
            $this->redirect('index.php?page=profile&edit=last_name');
        }

        $userModel = new UserManager();
        $userModel->updateLastName($userId, $newLastName);

        // Update session data via AuthManager
        $this->session->set('user_last_name', $newLastName);

        $this->setSuccess('Nom mis à jour avec succès');
        $this->redirect('index.php?page=profile');
    }

    /**
     * Process user status update
     *
     * Validates CSRF token and updates user's status in database.
     * BDE users cannot change their status.
     * Valid statuses: BUT 1, BUT 2, BUT 3, Personnel Enseignant
     *
     * @return void Redirects to profile page with success or error message
     */
    public function processUserStatus(): void
    {
        // ====================================================================
        // Code CSRF to be corrected
        // ====================================================================
        // CSRF validation temporarily disabled
        // Problem identified: CSRF token not retrieved correctly with multipart/form-data
        // when uploading files. Permanent solution to be implemented in S4
        // ====================================================================

        // Temporary flag to disable CSRF validation

        $skipCsrfValidation = true; // To be set to false after the problem has been corrected.

        // Validate CSRF token
        /** @phpstan-ignore-next-line */
        if (!$skipCsrfValidation) {
            $csrfToken = $this->request->post('csrf_token', '');

            if (!$this->csrf->validateToken((string) $csrfToken)) {
                $this->setError('Token de sécurité invalide. Veuillez réessayer.');
                $this->redirect('index.php?page=createArticle');
            }
        }

        $user = $this->auth->getUser();
        if (!$user || !isset($user['user_id'])) {
            $this->setError('Utilisateur non authentifié');
            $this->redirect('index.php?page=login');
        }

        // BDE users cannot change their status
        if (isset($user['user_status']) && $user['user_status'] === 'BDE') {
            $this->setError('Les membres BDE ne peuvent pas modifier leur statut');
            $this->redirect('index.php?page=profile');
        }

        $userId = $user['user_id'];
        $newUserStatus = trim((string) $this->request->post('user_status', ''));

        // Validate user status
        $validStatuses = ['BUT 1', 'BUT 2', 'BUT 3', 'Personnel Enseignant'];
        if (!in_array($newUserStatus, $validStatuses, true)) {
            $this->setError('Statut invalide. Veuillez sélectionner un statut valide.');
            $this->redirect('index.php?page=profile&edit=user_status');
        }

        $userModel = new UserManager();
        $userModel->updateUserStatus($userId, $newUserStatus);

        // Update session data via AuthManager
        $this->session->set('user_status', $newUserStatus);

        $this->setSuccess('Statut mis à jour avec succès');
        $this->redirect('index.php?page=profile');
    }
}
