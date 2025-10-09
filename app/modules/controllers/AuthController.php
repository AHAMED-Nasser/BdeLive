<?php
declare(strict_types=1);

class AuthController
{
    private $userManager;

    public function __construct()
    {
        $this->userManager = new UserManager();
    }


    public function login(string $email, string $mdp): bool
    {
        try {
            // Step 1: Find user by email using UserManager
            $user = $this->userManager->findUserByEmail($email);
            
            // Step 2: Check if user exists
            if (!$user) {
                return false;
            }
            
            // Step 3: Verify password using UserManager
            if (!$this->userManager->verifyPassword($mdp, $user['mdp'])) {
                return false;
            }
            
            // Step 4: Credentials are valid - Create session
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // Store user information in session
            $_SESSION['utilisateur_id'] = $user['utilisateur_id'];
            $_SESSION['nom'] = $user['nom'];
            $_SESSION['prenom'] = $user['prenom'];
            $_SESSION['classe_annee'] = $user['classe_annee'];
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
     * Destroys session and redirects to home page
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

    public function register(string $nom, string $prenom, string $classeAnnee, string $email, string $mdp): int|false
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
            return $this->userManager->createUser($nom, $prenom, $classeAnnee, $email, $mdp);
            
        } catch (PDOException $e) {
            error_log('AuthController::register - ' . $e->getMessage());
            return false;
        }
    }


    public function isLoggedIn(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['utilisateur_id']) && isset($_SESSION['suid']);
    }

    public function getCurrentUserId(): ?int
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return $_SESSION['utilisateur_id'] ?? null;
    }


    public function getCurrentUserFullName(): ?string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['prenom']) && isset($_SESSION['nom'])) {
            return $_SESSION['prenom'] . ' ' . $_SESSION['nom'];
        }
        
        return null;
    }

    public function getCurrentUserEmail(): ?string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return $_SESSION['email'] ?? null;
    }


    public function getCurrentUserClasseAnnee(): ?string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return $_SESSION['classe_annee'] ?? null;
    }


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
