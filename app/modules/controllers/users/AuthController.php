<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../config/config.php';

/**
 * Authentication Controller
 *
 * Handles user authentication, registration, session management and user data retrieval.
 * This controller acts as a bridge between the user interface (the views) and the UserManager model,
 * managing the authentication workflow and session state.
 *
 * @package BdeLive\Controllers
 * @author Mohamed-Amine Boudhib, Thomas Palot, Amin Helali, Willem Chetioui
 * @version 1.0.0
 */
class AuthController
{
    /**
     * User manager instance for database operations
     *
     * @var UserManager
     */
    private UserManager $userManager;

    /**
     * Constructor - Initialize the AuthController
     *
     * Creates a new UserManager instance for handling user-related database operations.
     */
    public function __construct()
    {
        $this->userManager = new UserManager();
    }

    /**
     * Authenticate a user with email and password
     *
     * Validates user credentials against the database. If successful, creates
     * a new session and stores user information in session variables.
     *
     * @param string $email The user's email address
     * @param string $pwd The user's password
     * @return bool True if authentication successful, false otherwise
     */
    public function login(string $email, string $pwd): bool
    {
        try {
            $user = $this->userManager->findUserByEmail($email);

            // Step 2: Check if user exists
            if (! $user) {
                return false;
            }

            // Step 3: Verify password using UserManager
            if (! $this->userManager->verifyPassword($pwd, $user['password'])) {
                return false;
            }

            // Step 4: Credentials are valid - Create session
            if (session_status() === PHP_SESSION_NONE) {
            }

            // Store user information in session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['user_status'] = $user['user_status'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['suid'] = session_id();

            return true;

        } catch (PDOException $e) {
            error_log('AuthController::login - ' . $e->getMessage());

            return false;
        }
    }


    /**
     * Register a new user
     *
     * Creates a new user account after validating the email format and checking
     * for duplicate email addresses. The password is hashed before storage.
     *
     * @param string $last_name User's last name
     * @param string $first_name User's first name
     * @param string $user_status User's class year (1, 2, or 3)
     * @param string $email User's email address
     * @param string $pwd User's password (will be hashed)
     * @return int|false The new user ID if successful, false otherwise
     */
    public function register(string $last_name, string $first_name, string $user_status, string $email, string $pwd): int|false
    {
        try {
            // Step 1: Check if email already exists using UserManager
            if ($this->userManager->emailExists($email)) {
                return false;
            }

            // Step 2: Validate email format
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return false;
            }

            // Step 3: Create new user via UserManager
            return $this->userManager->createUser($last_name, $first_name, $user_status, $email, $pwd);

        } catch (PDOException $e) {
            error_log('AuthController::register - ' . $e->getMessage());

            return false;
        }
    }



    /**
     * Get the current logged-in user's full name
     *
     * Returns the user's first name and last name concatenated.
     *
     * @return string|null The user's full name, or null if not logged in
     */
    public function getCurrentUserFullName(): ?string
    {
        if (session_status() === PHP_SESSION_NONE) {
        }

        if (isset($_SESSION['first_name']) && isset($_SESSION['last_name'])) {
            return $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
        }

        return null;
    }




}
