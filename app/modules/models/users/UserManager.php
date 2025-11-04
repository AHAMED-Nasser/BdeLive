<?php

declare(strict_types=1);

namespace App\Modules\Models\Users;

use PDO;
use PDOException;
use App\Core\Database;

/**
 * User Manager Model
 *
 * Handles all user-related database operations including CRUD operations,
 * password hashing and verification, and user search functionality.
 * This class provides a data access layer for the USERS table.
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
        return password_verify($password, $hashedPassword);
    }

    /**
     * Find a user by email address
     *
     * Searches for a user in the database using their email address.
     * Returns all user information including the hashed password.
     *
     * @param string $email The email address to search for
     * @return array{user_id: int, last_name: string, first_name: string, user_status: string, email: string, password: string}|false
     * Array containing user data if found, false otherwise
     * @throws PDOException If database query fails
     */
    public function findUserByEmail(string $email): array|false
    {
        try {
            $query = "SELECT user_id, last_name, first_name, user_status, email, password 
                      FROM USERS 
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
     * Create a new user
     *
     * Inserts a new user record into the database with hashed password.
     * The password is automatically hashed before storage.
     *
     * @param string $last_name User's last name
     * @param string $first_name User's first name
     * @param string $user_status User's status (BUT 1, BUT 2, BUT 3, Personnel Enseignant)
     * @param string $email User's email address
     * @param string $password User's password (plain text, will be hashed)
     * @return int|false The new user ID if successful, false otherwise
     * @throws PDOException If database query fails
     */
    public function createUser(string $last_name, string $first_name, string $user_status, string $email, string $password): int|false
    {
        try {
            $hashedPassword = $this->hashPassword($password);

            $query = "INSERT INTO USERS (last_name, first_name, user_status, email, password) 
                      VALUES (:last_name, :first_name, :user_status, :email, :password)";

            $stmt = $this->pdo->prepare($query);
            //Execute the statement
            $success = $stmt->execute([
                'last_name' => $last_name,
                'first_name' => $first_name,
                'user_status' => $user_status,
                'email' => $email,
                'password' => $hashedPassword,
            ]);

            //Return the new user ID if successful, false otherwise
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
     * @param int $user_id The ID of the user to update
     * @param string $last_name New last name
     * @param string $first_name New first name
     * @param string $user_status New user status (BUT 1, BUT 2, BUT 3, Personnel Enseignant)
     * @param string $email New email address
     * @return bool True if update successful, false otherwise
     * @throws PDOException If database query fails
     */
    public function updateUser(int $user_id, string $last_name, string $first_name, string $user_status, string $email): bool
    {
        try {
            $query = "UPDATE USERS 
                      SET last_name = :last_name, 
                          first_name = :first_name, 
                          user_status = :user_status, 
                          email = :email 
                      WHERE user_id = :user_id";

            $stmt = $this->pdo->prepare($query);

            return $stmt->execute([
                'user_id' => $user_id,
                'last_name' => $last_name,
                'first_name' => $first_name,
                'user_status' => $user_status,
                'email' => $email,
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
     * @param int $user_id The ID of the user
     * @param string $new_password The new password (plain text, will be hashed)
     * @return bool True if update successful, false otherwise
     * @throws PDOException If database query fails
     */
    public function updatePassword(int $user_id, string $new_password): bool
    {
        try {
            $hashedPassword = $this->hashPassword($new_password);

            $query = "UPDATE USERS 
                      SET password = :password 
                      WHERE user_id = :user_id";

            $stmt = $this->pdo->prepare($query);

            return $stmt->execute([
                'user_id' => $user_id,
                'password' => $hashedPassword,
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
     * @param int $user_id The ID of the user to delete
     * @return bool True if deletion successful, false otherwise
     * @throws PDOException If database query fails
     */
    public function deleteUser(int $user_id): bool
    {
        try {
            $query = "DELETE FROM USERS WHERE user_id = :user_id";

            $stmt = $this->pdo->prepare($query);

            return $stmt->execute(['user_id' => $user_id]);
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
                      FROM USERS 
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

    public function updateFirstName(int $user_id, String $newFirstName) : void{
        try {
            $query = 'UPDATE `USERS`
                SET first_name = :newFirstName 
                WHERE user_id = :user_id';

            $stmt = $this->pdo->prepare($query);
            $result = $stmt->execute(['newFirstName' => $newFirstName, 'user_id' => $user_id]);
            error_log('UserManager::updateFirstName - ' . $result);
        }
        catch (PDOException $e) {
            error_log('UserManager::updateFirstName - ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateLastName(int $user_id, String $newLastName): void{
        try {
            $query = 'UPDATE `USERS`
                SET last_name = :newLastName 
                WHERE user_id = :user_id';
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['newLastName' => $newLastName, 'user_id' => $user_id]);
        }
        catch (PDOException $e) {
            error_log('UserManager::updateLastName - ' . $e->getMessage());
            throw $e;
        }
    }
}

\class_alias(__NAMESPACE__ . '\\UserManager', 'UserManager');
