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
        // Assurer l'encodage UTF-8 comme spécifié dans les contraintes du cours
        $this->pdo->exec('SET CHARACTER SET utf8');
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
     * @return array{
     * user_id: int,
     * last_name: string,
     * first_name: string,
     * user_status: string,
     * email: string,
     * password: string,
     * role: string,
     * is_blocked: int|string,
     * is_verified: int
     * }|false
     */
    public function findUserByEmail(string $email): array|false
    {
        try {
            $query = "SELECT user_id, last_name, first_name, user_status, email, password, is_verified, role, is_blocked
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
    public function createUser(
        string $last_name,
        string $first_name,
        string $user_status,
        string $email,
        string $password
    ): int|false {
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
    public function updateUser(
        int $user_id,
        string $last_name,
        string $first_name,
        string $user_status,
        string $email
    ): bool {
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

    /**
     * Update a user's first name
     *
     * Changes a user's first name in the database.
     *
     * @param int $user_id The ID of the user
     * @param string $newFirstName The new first name
     * @return void
     * @throws PDOException If database query fails
     */
    public function updateFirstName(int $user_id, string $newFirstName): void
    {
        try {
            $query = 'UPDATE USERS
                SET first_name = :newFirstName 
                WHERE user_id = :user_id';

            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['newFirstName' => $newFirstName, 'user_id' => $user_id]);
        } catch (PDOException $e) {
            error_log('UserManager::updateFirstName - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update a user's last name
     *
     * Changes a user's last name in the database.
     *
     * @param int $user_id The ID of the user
     * @param string $newLastName The new last name
     * @return void
     * @throws PDOException If database query fails
     */
    public function updateLastName(int $user_id, string $newLastName): void
    {
        try {
            $query = 'UPDATE USERS
                SET last_name = :newLastName 
                WHERE user_id = :user_id';
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['newLastName' => $newLastName, 'user_id' => $user_id]);
        } catch (PDOException $e) {
            error_log('UserManager::updateLastName - ' . $e->getMessage());
            throw $e;
        }
    }


    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAllUsersPaginated(int $limit, int $offset, bool $isBlocked = false): array
    {
        try {
            $blockedValue = $isBlocked ? 1 : 0;

            $query = 'SELECT user_id, last_name, first_name, user_status, email, role, is_blocked
                      FROM USERS
                      WHERE is_blocked = :is_blocked
                      ORDER BY last_name ASC
                      LIMIT :limit OFFSET :offset';

            $stmt = $this->pdo->prepare($query);
            $stmt->execute([
                'is_blocked' => $blockedValue,
                'limit' => $limit,
                'offset' => $offset
            ]);

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('UserManager::getAllUsersPaginated - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Compte les utilisateurs pour la pagination selon is_blocked
     */
    public function countUsersByBlockStatus(bool $isBlocked = false): int
    {
        $blockedValue = $isBlocked ? 1 : 0;
        $query = 'SELECT COUNT(*) FROM USERS WHERE is_blocked = :is_blocked';
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['is_blocked' => $blockedValue]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Change le rôle d'un utilisateur (ex: 'admin' ou 'user')
     */
    public function updateUserRole(int $userId, string $role): bool
    {
        $query = 'UPDATE USERS SET role = :role WHERE user_id = :id';
        return $this->pdo->prepare($query)->execute(['role' => $role, 'id' => $userId]);
    }

    public function setBlockStatus(int $userId, int $status): bool
    {
        $query = 'UPDATE USERS SET is_blocked = :status WHERE user_id = :id';
        return $this->pdo->prepare($query)->execute(['status' => $status, 'id' => $userId]);
    }

    /**
     * Update a user's status
     *
     * Changes a user's status in the database.
     * Valid statuses: BUT 1, BUT 2, BUT 3, Personnel Enseignant
     * Note: BDE status cannot be set through this method for security.
     *
     * @param int $user_id The ID of the user
     * @param string $newUserStatus The new user status
     * @return void
     * @throws PDOException If database query fails
     */
    public function updateUserStatus(int $user_id, string $newUserStatus): void
    {
        try {
            $query = 'UPDATE USERS
                SET user_status = :newUserStatus 
                WHERE user_id = :user_id';
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['newUserStatus' => $newUserStatus, 'user_id' => $user_id]);
        } catch (PDOException $e) {
            error_log('UserManager::updateUserStatus - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate a unique verification token
     *
     * Generates a cryptographically secure random token for email verification.
     * The token is a 64-character hexadecimal string (32 bytes).
     *
     * @return string The generated verification token
     */
    public function generateVerificationToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Create a new user with verification token
     *
     * Inserts a new user record into the database with hashed password and verification token.
     * The user is created with is_verified = 0 (not verified).
     * The verification token expires 24 hours after creation and the expiration
     * date is stored in the token_expires_at column.
     *
     * @param string $last_name User's last name
     * @param string $first_name User's first name
     * @param string $user_status User's status (BUT 1, BUT 2, BUT 3, Personnel Enseignant)
     * @param string $email User's email address
     * @param string $password User's password (plain text, will be hashed)
     * @return array{user_id: int, token: string}|false Array with user_id and token if successful, false otherwise
     * @throws PDOException If database query fails
     */
    public function createUserWithVerification(
        string $last_name,
        string $first_name,
        string $user_status,
        string $email,
        string $password
    ): array|false {
        try {
            $hashedPassword = $this->hashPassword($password);
            $verificationToken = $this->generateVerificationToken();
            // Token expires 24 hours after creation
            $now = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
            $now->modify('+24 hours');
            $tokenExpiresAt = $now->format('Y-m-d H:i:s');

            $query = "INSERT INTO USERS (last_name, first_name, user_status, email, password, verification_token, is_verified, token_expires_at) 
                      VALUES (:last_name, :first_name, :user_status, :email, :password, :verification_token, 0, :token_expires_at)";

            $stmt = $this->pdo->prepare($query);
            $success = $stmt->execute([
                'last_name' => $last_name,
                'first_name' => $first_name,
                'user_status' => $user_status,
                'email' => $email,
                'password' => $hashedPassword,
                'verification_token' => $verificationToken,
                'token_expires_at' => $tokenExpiresAt,
            ]);

            if ($success) {
                return [
                    'user_id' => (int)$this->pdo->lastInsertId(),
                    'token' => $verificationToken,
                ];
            }

            return false;
        } catch (PDOException $e) {
            error_log('UserManager::createUserWithVerification - ' . $e->getMessage());

            throw $e;
        }
    }

    /**
     * Verify email token and activate account
     *
     * Verifies the token and sets is_verified to 1 for the user.
     * Checks if the token has expired (24 hours). If expired, deletes the user
     * and returns an "expired" message. The token is cleared after successful
     * verification and the expiration date is reset to NULL.
     *
     * @param string $token The verification token
     * @return array{success: bool, user_id: int}|array{success: bool, message: string}
     *         Array with success status and user_id or error message
     * @throws PDOException If database query fails
     */
    public function verifyEmailToken(string $token): array
    {
        try {
            // Find user by verification token
            $query = "SELECT user_id, is_verified, token_expires_at FROM USERS 
                      WHERE verification_token = :token 
                      LIMIT 1";

            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['token' => $token]);
            $user = $stmt->fetch();

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Token de vérification invalide',
                ];
            }

            // Check if email is already verified
            if ((int) $user['is_verified'] === 1) {
                return [
                    'success' => false,
                    'message' => 'Cet email a déjà été vérifié',
                ];
            }

            // Validate token expiration if present
            if ($user['token_expires_at'] !== null) {
                $expirationDate = new \DateTime($user['token_expires_at'], new \DateTimeZone('Europe/Paris'));
                $now = new \DateTime('now', new \DateTimeZone('Europe/Paris'));

                if ($now > $expirationDate) {
                    // Token expired: delete the user so they can register again
                    $this->deleteUser((int) $user['user_id']);

                    return [
                        'success' => false,
                        'message' => 'expired',
                    ];
                }
            }

            // Activate account and clear token and expiration date
            $updateQuery = "UPDATE USERS 
                           SET is_verified = 1, verification_token = NULL, token_expires_at = NULL 
                           WHERE user_id = :user_id";

            $updateStmt = $this->pdo->prepare($updateQuery);
            $updateSuccess = $updateStmt->execute(['user_id' => $user['user_id']]);

            if ($updateSuccess) {
                return [
                    'success' => true,
                    'user_id' => (int) $user['user_id'],
                ];
            }

            return [
                'success' => false,
                'message' => 'Erreur lors de la vérification',
            ];
        } catch (PDOException $e) {
            error_log('UserManager::verifyEmailToken - ' . $e->getMessage());

            throw $e;
        }
    }

    /**
     * Check if user's email is verified
     *
     * @param int $user_id The ID of the user
     * @return bool True if email is verified, false otherwise
     * @throws PDOException If database query fails
     */
    public function isEmailVerified(int $user_id): bool
    {
        try {
            $query = "SELECT is_verified FROM USERS WHERE user_id = :user_id LIMIT 1";

            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['user_id' => $user_id]);
            $result = $stmt->fetch();

            return $result && (int)$result['is_verified'] === 1;
        } catch (PDOException $e) {
            error_log('UserManager::isEmailVerified - ' . $e->getMessage());

            throw $e;
        }
    }

    /**
     * Resend verification token
     *
     * Generates a new verification token for the user and returns it.
     * Updates the token expiration date to 24 hours from now.
     * Does not send the email (that's handled by the controller).
     *
     * @param int $user_id The ID of the user
     * @return string|false The new verification token if successful, false otherwise
     * @throws PDOException If database query fails
     */
    public function resendVerificationToken(int $user_id): string|false
    {
        try {
            $newToken = $this->generateVerificationToken();
            // Token expires 24 hours after resend
            $now = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
            $now->modify('+24 hours');
            $tokenExpiresAt = $now->format('Y-m-d H:i:s');

            $query = "UPDATE USERS 
                     SET verification_token = :token, is_verified = 0, token_expires_at = :token_expires_at 
                     WHERE user_id = :user_id";

            $stmt = $this->pdo->prepare($query);
            $success = $stmt->execute([
                'token' => $newToken,
                'user_id' => $user_id,
                'token_expires_at' => $tokenExpiresAt,
            ]);

            return $success ? $newToken : false;
        } catch (PDOException $e) {
            error_log('UserManager::resendVerificationToken - ' . $e->getMessage());

            throw $e;
        }
    }

    /**
     * Update a user's email address
     *
     * Changes a user's email address in the database.
     *
     * @param int $user_id The ID of the user
     * @param string $newEmail The new email address
     * @return bool True if update successful, false otherwise
     * @throws PDOException If database query fails
     */
    public function updateEmail(int $user_id, string $newEmail): bool
    {
        try {
            $query = "UPDATE USERS SET email = :email WHERE user_id = :user_id";
            $stmt = $this->pdo->prepare($query);

            return $stmt->execute([
                'email' => $newEmail,
                'user_id' => $user_id
            ]);
        } catch (PDOException $e) {
            error_log('UserManager::updateEmail - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get user by ID
     *
     * Retrieves user information by user ID.
     *
     * @param int $user_id The user ID
     * @return array<string, mixed>|false User data if found, false otherwise
     * @throws PDOException If database query fails
     */
    public function getUserById(int $user_id): array|false
    {
        try {
            $query = "SELECT user_id, last_name, first_name, user_status, email, password, is_verified 
                      FROM USERS 
                      WHERE user_id = :user_id 
                      LIMIT 1";

            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['user_id' => $user_id]);

            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log('UserManager::getUserById - ' . $e->getMessage());
            throw $e;
        }
    }
}
