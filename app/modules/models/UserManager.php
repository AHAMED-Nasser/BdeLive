<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/Database.php';

/**
 * User Manager Model
 * 
 * Handles all user-related database operations including CRUD operations,
 * password hashing and verification, and user search functionality.
 * This class provides a data access layer for the Utilisateur table.
 * 
 * @package BdeLive\Models
 * @author Mohamed-Amine Boudhib, Thomas Palot, Amin Helali, Willem Chetioui, Nasser Ahamed, Romain Cantor
 * @version 1.0.0
 */
class UserManager
{
    /**
     * PDO database connection instance
     * 
     * @var PDO
     */
    private PDO $pdo;

    /**
     * Constructor - Initialize the UserManager
     * 
     * Retrieves the database connection from the Database singleton.
     */
    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Hash a password using SHA-1
     * 
     * Generates a hashed password of the one that is provided
     * 
     * @param string $password The plain text password to hash
     * @return string The hashed password
     */
    public function hashPassword(string $password): string
    {
        // SHA-1 generates exactly 40 hexadecimal characters
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verify a password against its hash
     * 
     * Compares a plain text password with its hashed version to verify if they match.
     * 
     * @param string $password The plain text password to verify
     * @param string $hashedPassword The hashed password to compare against
     * @return bool True if the password matches, false otherwise
     */
    public function verifyPassword(string $password, string $hashedPassword): bool
    {
        return password_hash($password, PASSWORD_DEFAULT) === $hashedPassword;
    }

    /**
     * Find a user by email address
     * 
     * Searches for a user in the database using their email address.
     * Returns all user information including the hashed password.
     * 
     * @param string $email The email address to search for
     * @return array|false Array containing user data if found, false otherwise
     * @throws PDOException If database query fails
     */
    public function findUserByEmail(string $email): array|false
    {
        try {
            $query = "SELECT utilisateur_id, nom, prenom, classe_annee, email, mdp 
                      FROM Utilisateur 
                      WHERE email = :email 
                      LIMIT 1";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['email' => $email]);
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log('UserManager::findUserByEmail - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Find a user by their ID
     * 
     * Retrieves user information from the database using the user ID.
     * Does not return the password field for security reasons.
     * 
     * @param int $utilisateurId The user ID to search for
     * @return array|false Array containing user data if found, false otherwise
     * @throws PDOException If database query fails
     */
    public function findUserById(int $utilisateurId): array|false
    {
        try {
            $query = "SELECT utilisateur_id, nom, prenom, classe_annee, email 
                      FROM Utilisateur 
                      WHERE utilisateur_id = :utilisateur_id 
                      LIMIT 1";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['utilisateur_id' => $utilisateurId]);
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log('UserManager::findUserById - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get all users from the database
     * 
     * Retrieves all registered users, ordered by last name and first name.
     * Password fields are excluded from the results (for security reasons).
     * 
     * @return array Array of user records (empty array if no users found)
     * @throws PDOException If database query fails
     */
    public function getAllUsers(): array
    {
        try {
            $query = "SELECT utilisateur_id, nom, prenom, classe_annee, email 
                      FROM Utilisateur 
                      ORDER BY nom, prenom";
            
            $stmt = $this->pdo->query($query);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('UserManager::getAllUsers - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Find users by class year
     * 
     * Retrieves all users belonging to a specific class year,
     * ordered by last name and first name.
     * 
     * @param string $classeAnnee The class year to filter by (1, 2, or 3)
     * @return array Array of user records (empty array if no users found)
     * @throws PDOException If database query fails
     */
    public function findUsersByClasseAnnee(string $classeAnnee): array
    {
        try {
            $query = "SELECT utilisateur_id, nom, prenom, classe_annee, email 
                      FROM Utilisateur 
                      WHERE classe_annee = :classe_annee 
                      ORDER BY nom, prenom";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['classe_annee' => $classeAnnee]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('UserManager::findUsersByClasseAnnee - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a new user
     * 
     * Inserts a new user record into the database with hashed password.
     * The password is automatically hashed before storage.
     * 
     * @param string $nom User's last name
     * @param string $prenom User's first name
     * @param string $classeAnnee User's class year (1, 2, or 3)
     * @param string $email User's email address
     * @param string $mdp User's password (plain text, will be hashed)
     * @return int|false The new user ID if successful, false otherwise
     * @throws PDOException If database query fails
     */
    public function createUser(string $nom, string $prenom, string $classeAnnee, string $email, string $mdp): int|false
    {
        try {
            $hashedPassword = $this->hashPassword($mdp);
            
            $query = "INSERT INTO Utilisateur (nom, prenom, classe_annee, email, mdp) 
                      VALUES (:nom, :prenom, :classe_annee, :email, :mdp)";
            
            $stmt = $this->pdo->prepare($query);
            
            $success = $stmt->execute([
                'nom' => $nom,
                'prenom' => $prenom,
                'classe_annee' => $classeAnnee,
                'email' => $email,
                'mdp' => $hashedPassword
            ]);
            
            return $success ? (int)$this->pdo->lastInsertId() : false;
        } catch (PDOException $e) {
            error_log('UserManager::createUser - ' . $e->getMessage());
            throw $e;
        }
    }


    /**
     * Update user information
     * 
     * Updates an existing user's profile information (excluding password).
     * 
     * @param int $utilisateurId The ID of the user to update
     * @param string $nom New last name
     * @param string $prenom New first name
     * @param string $classeAnnee New class year (1, 2, or 3)
     * @param string $email New email address
     * @return bool True if update successful, false otherwise
     * @throws PDOException If database query fails
     */
    public function updateUser(int $utilisateurId, string $nom, string $prenom, string $classeAnnee, string $email): bool
    {
        try {
            $query = "UPDATE Utilisateur 
                      SET nom = :nom, 
                          prenom = :prenom, 
                          classe_annee = :classe_annee, 
                          email = :email 
                      WHERE utilisateur_id = :utilisateur_id";
            
            $stmt = $this->pdo->prepare($query);
            
            return $stmt->execute([
                'utilisateur_id' => $utilisateurId,
                'nom' => $nom,
                'prenom' => $prenom,
                'classe_annee' => $classeAnnee,
                'email' => $email
            ]);
        } catch (PDOException $e) {
            error_log('UserManager::updateUser - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update a user's password
     * 
     * Changes a user's password. The new password is automatically hashed
     * before storage.
     * 
     * @param int $utilisateurId The ID of the user
     * @param string $newMdp The new password (plain text, will be hashed)
     * @return bool True if update successful, false otherwise
     * @throws PDOException If database query fails
     */
    public function updatePassword(int $utilisateurId, string $newMdp): bool
    {
        try {
            $hashedPassword = $this->hashPassword($newMdp);
            
            $query = "UPDATE Utilisateur 
                      SET mdp = :mdp 
                      WHERE utilisateur_id = :utilisateur_id";
            
            $stmt = $this->pdo->prepare($query);
            
            return $stmt->execute([
                'utilisateur_id' => $utilisateurId,
                'mdp' => $hashedPassword
            ]);
        } catch (PDOException $e) {
            error_log('UserManager::updatePassword - ' . $e->getMessage());
            throw $e;
        }
    }


    /**
     * Delete a user from the database
     * 
     * Permanently removes a user record. This operation cannot be undone.
     * 
     * @param int $utilisateurId The ID of the user to delete
     * @return bool True if deletion successful, false otherwise
     * @throws PDOException If database query fails
     */
    public function deleteUser(int $utilisateurId): bool
    {
        try {
            $query = "DELETE FROM Utilisateur WHERE utilisateur_id = :utilisateur_id";
            
            $stmt = $this->pdo->prepare($query);
            
            return $stmt->execute(['utilisateur_id' => $utilisateurId]);
        } catch (PDOException $e) {
            error_log('UserManager::deleteUser - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if an email address already exists
     * 
     * Useful for validation during user registration to prevent duplicate accounts.
     * 
     * @param string $email The email address to check
     * @return bool True if email exists, false otherwise
     * @throws PDOException If database query fails
     */
    public function emailExists(string $email): bool
    {
        try {
            $query = "SELECT COUNT(*) as count 
                      FROM Utilisateur 
                      WHERE email = :email";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['email' => $email]);
            $result = $stmt->fetch();
            
            return $result['count'] > 0;
        } catch (PDOException $e) {
            error_log('UserManager::emailExists - ' . $e->getMessage());
            throw $e;
        }
    }
}

