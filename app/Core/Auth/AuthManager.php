<?php

declare(strict_types=1);

namespace App\Core\Auth;

use App\Core\Session\SessionManager;
use App\Core\Exception\AuthenticationException;
use App\Core\Exception\AuthorizationException;

/**
 * AuthManager - Authentication and Authorization Management
 *
 * Replaces procedural functions from app/include/auth.php
 * Manages authentication state in an object-oriented manner.
 *
 * SRP (Single Responsibility Principle):
 * This class ONLY manages authentication state.
 * It does NOT handle HTTP redirections (that's the Application/Router role).
 *
 * Features:
 * - User login/logout
 * - Authentication checks
 * - Authorization checks (admin vs regular user)
 * - Session regeneration for security
 * - User data retrieval
 *
 * @package App\Core\Auth
 * @version 1.0.0
 */
class AuthManager
{
    private const USER_ID_KEY = 'user_id';
    private const USER_STATUS_KEY = 'user_status';
    private const USER_EMAIL_KEY = 'user_email';
    private const USER_FIRST_NAME_KEY = 'user_first_name';
    private const USER_LAST_NAME_KEY = 'user_last_name';

    public function __construct(private SessionManager $session)
    {
    }


    /**
     * Check if user is authenticated
     *
     * @return bool True if user is logged in
     */
    public function isAuthenticated(): bool
    {
        return $this->session->has(self::USER_ID_KEY);
    }

    public function isBlocked(): bool
    {
        $user = $this->session->get('user');
        return $user !== null
            && isset($user['is_blocked'])
            && (int) $user['is_blocked'] === 1;
    }

    /**
     * Get the authenticated user's ID
     *
     * @return int|null User ID or null if not authenticated
     */
    public function getUserId(): ?int
    {
        $userId = $this->session->get(self::USER_ID_KEY);
        return $userId ? (int) $userId : null;
    }

    /**
     * Get the user's status
     *
     * Possible values: BUT 1, BUT 2, BUT 3, Personnel Enseignant, BDE
     *
     * @return string|null User status or null if not authenticated
     */
    public function getUserStatus(): ?string
    {
        return $this->session->get(self::USER_STATUS_KEY);
    }

    /**
     * Get the user's email address
     *
     * @return string|null Email or null if not authenticated
     */
    public function getUserEmail(): ?string
    {
        return $this->session->get(self::USER_EMAIL_KEY);
    }

    /**
     * Get the user's first name
     *
     * @return string|null First name or null if not set/authenticated
     */
    public function getUserFirstName(): ?string
    {
        return $this->session->get(self::USER_FIRST_NAME_KEY);
    }

    /**
     * Get the user's last name
     *
     * @return string|null Last name or null if not set/authenticated
     */
    public function getUserLastName(): ?string
    {
        return $this->session->get(self::USER_LAST_NAME_KEY);
    }

    /**
     * Check if the user is an administrator (admin)
     *
     * @return bool True if user has admin rights and not blocked
     */
    public function isAdmin(): bool
    {
        $user = $this->session->get('user');

        return $user !== null
            && isset($user['role'])
            && $user['role'] === 'admin'
            && (int) ($user['is_blocked'] ?? 0) === 0;
    }

    /**
     * Log in a user
     *
     * Stores user information in session and regenerates session ID
     * to prevent session fixation attacks.
     *
     * @param int $userId User ID from database
     * @param string $userStatus User status (BUT 1, BUT 2, BUT 3, Personnel Enseignant, BDE)
     * @param string $email User email address
     * @param string $firstName User first name (optional)
     * @param string $lastName User last name (optional)
     * @return void
     */
    public function login(
        int $userId,
        string $userStatus,
        string $email,
        string $role = 'user',
        int $isBlocked = 0,
        string $firstName = '',
        string $lastName = ''
    ): void {
        $this->session->regenerate();

        $this->session->set(self::USER_ID_KEY, $userId);
        $this->session->set(self::USER_STATUS_KEY, $userStatus);
        $this->session->set(self::USER_EMAIL_KEY, $email);

        $this->session->set('user', [
            'role' => $role,
            'is_blocked' => $isBlocked,
        ]);

        if ($firstName !== '') {
            $this->session->set(self::USER_FIRST_NAME_KEY, $firstName);
        }

        if ($lastName !== '') {
            $this->session->set(self::USER_LAST_NAME_KEY, $lastName);
        }
    }

    /**
     * Log out the current user
     *
     * Removes all user data from session. Session itself remains active.
     *
     * @return void
     */
    public function logout(): void
    {
        $this->session->remove(self::USER_ID_KEY);
        $this->session->remove(self::USER_STATUS_KEY);
        $this->session->remove(self::USER_EMAIL_KEY);
        $this->session->remove(self::USER_FIRST_NAME_KEY);
        $this->session->remove(self::USER_LAST_NAME_KEY);
    }

    /**
     * Require user to be authenticated
     *
     * Throws an exception if user is not logged in.
     * Redirect handling is delegated to the Application/Router layer.
     *
     * @return void
     * @throws AuthenticationException If user is not authenticated
     */
    public function requireAuthentication(): void
    {
        if (!$this->isAuthenticated()) {
            throw new AuthenticationException('Vous devez être connecté pour accéder à cette page.');
        }

        if ($this->isBlocked()) {
            $this->logout();
            throw new AuthenticationException('Votre compte a été bloqué. Veuillez contacter l\'administrateur.');
        }
    }

    /**
     * Require user to be an administrator (BDE)
     *
     * Throws an exception if user doesn't have admin rights.
     * Redirect handling is delegated to the Application/Router layer.
     *
     * @return void
     * @throws AuthenticationException If user is not logged in
     * @throws AuthorizationException If user is not an admin
     */
    public function requireAdmin(): void
    {
        $this->requireAuthentication();

        if (!$this->isAdmin()) {
            throw new AuthorizationException('You do not have the necessary permissions to access this page');
        }
    }

    /**
     * Get all user data as an array
     *
     * Returns a complete array of user information suitable for passing to views.
     * Returns null if user is not authenticated.
     *
     * @return array<string, mixed>|null User data array or null if not authenticated
     */
    public function getUser(): ?array
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        return [
            'user_id' => $this->getUserId(),
            'email' => $this->getUserEmail(),
            'user_status' => $this->getUserStatus(),
            'first_name' => $this->getUserFirstName(),
            'last_name' => $this->getUserLastName(),
            'is_admin' => $this->isAdmin(),
        ];
    }
}
