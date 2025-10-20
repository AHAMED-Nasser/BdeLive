<?php
declare(strict_types=1);

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
            if (!$user) {
                return false;
            }
            
            // Step 3: Verify password using UserManager
            if (!$this->userManager->verifyPassword($pwd, $user['password'])) {
                return false;
            }
            
            // Step 4: Credentials are valid - Create session
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
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
     * Handle user logout
     * 
     * Destroys the current session and redirects to home page.
     * Clears all session data and cookies associated with the user's session.
     * 
     * @return void
     */
    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        session_unset();
        session_destroy();
        
        header('Location: index.php?page=home');
        exit;
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
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
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
     * Check if a user is currently logged in
     * 
     * Verifies the presence of required session variables to determine
     * if a user has an active authenticated session.
     * 
     * @return bool True if user is logged in, false otherwise
     */
    public function isLoggedIn(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['user_id']) && isset($_SESSION['suid']);
    }

    /**
     * Get the current logged-in user's ID
     * 
     * Retrieves the user ID from the current session if available.
     * 
     * @return int|null The user ID, or null if not logged in
     */
    public function getCurrentUserId(): ?int
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return $_SESSION['user_id'] ?? null;
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
            session_start();
        }
        
        if (isset($_SESSION['first_name']) && isset($_SESSION['last_name'])) {
            return $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
        }
        
        return null;
    }

    /**
     * Get the current logged-in user's email address
     * 
     * Retrieves the email address from the current session.
     * 
     * @return string|null The user's email, or null if not logged in
     */
    public function getCurrentUserEmail(): ?string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return $_SESSION['email'] ?? null;
    }

    /**
     * Get the current logged-in user's class year
     * 
     * Retrieves the class year information from the current session.
     * 
     * @return string|null The user's class year (1, 2, or 3), or null if not logged in
     */
    public function getCurrentUserClasseAnnee(): ?string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return $_SESSION['user_status'] ?? null;
    }


    /**
     * Get the complete user data for the currently logged-in user
     * 
     * Retrieves all user information from the database for the current session user.
     * Returns user data including ID, name, email, and class year.
     * 
     * @return array|false Array of user data if found, false otherwise
     */
    public function getCurrentUserData(): array|false
    {
        $userId = $this->getCurrentUserId();
        
        if ($userId === null) {
            return false;
        }
        
        try {
            return $this->userManager->findUserById($userId);
        } catch (PDOException $e) {
            error_log('AuthController::getCurrentUserData - ' . $e->getMessage());
            return false;
        }
    }
}
