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
 * password hashing and verification, email verification, role management,
 * soft deletion, and user search/pagination functionality.
 * This class provides a unified data access layer for the USERS table.
 *
 * @package BdeLive\Models
 * @author Mohamed-Amine Boudhib, Thomas Palot, Amin Helali, Willem Chetioui, Nasser Ahamed, Romain Cantor
 * @version 2.0.0
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
     * Hash a password using the default PHP hashing algorithm (bcrypt)
     *
     * @param string $password The plain text password to hash
     * @return string The hashed password
     */
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verify a password against its hash
     *
     * Compares a plain text password with its hashed version.
     *
     * @param string $password       The plain text password to verify
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
     * Returns all fields required for authentication and session setup.
     *
     * @param string $email The email address to search for
     * @return array<string, mixed>|false Array containing user data if found, false otherwise
     * @throws PDOException If the database query fails
     */
    public function findUserByEmail(string $email): array|false
    {
        try {
            $query = "SELECT user_id, last_name, first_name, user_status, email, password,
                             is_verified, is_blocked, role, deleted_at
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
     * Retrieve a single user by their primary key
     *
     * @param int $user_id The user's primary key
     * @return array<string, mixed>|false User row if found, false otherwise
     * @throws PDOException If the database query fails
     */
    public function getUserById(int $user_id): array|false
    {
        try {
            $query = "SELECT * FROM USERS WHERE user_id = :user_id LIMIT 1";
            $stmt  = $this->pdo->prepare($query);
            $stmt->execute(['user_id' => $user_id]);

            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log('UserManager::getUserById - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a new user without email verification
     *
     * Inserts a new user record with a hashed password. The password is
     * automatically hashed before storage.
     *
     * @param string $last_name   User's last name
     * @param string $first_name  User's first name
     * @param string $user_status User's academic status (BUT 1, BUT 2, BUT 3, Personnel Enseignant)
     * @param string $email       User's email address
     * @param string $password    User's password (plain text, will be hashed)
     * @return int|false The new user ID on success, false otherwise
     * @throws PDOException If the database query fails
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

            $stmt    = $this->pdo->prepare($query);
            $success = $stmt->execute([
                'last_name'   => $last_name,
                'first_name'  => $first_name,
                'user_status' => $user_status,
                'email'       => $email,
                'password'    => $hashedPassword,
            ]);

            return $success ? (int) $this->pdo->lastInsertId() : false;
        } catch (PDOException $e) {
            error_log('UserManager::createUser - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a new user with an email verification token
     *
     * Inserts a user row that includes a 64-character hex verification token
     * and an expiry timestamp set 24 hours in the future.
     *
     * @param string $last_name   User's last name
     * @param string $first_name  User's first name
     * @param string $user_status User's academic status
     * @param string $email       User's email address
     * @param string $password    User's password (plain text, will be hashed)
     * @return array{user_id: int, token: string}|false Associative array with the new user_id
     *         and the raw verification token, or false on failure
     * @throws PDOException If the database query fails
     */
    public function createUserWithVerification(
        string $last_name,
        string $first_name,
        string $user_status,
        string $email,
        string $password
    ): array|false {
        try {
            $token          = $this->generateVerificationToken();
            $hashedPassword = $this->hashPassword($password);
            $expiresAt      = date('Y-m-d H:i:s', strtotime('+24 hours'));

            $query = "INSERT INTO USERS
                        (last_name, first_name, user_status, email, password,
                         verification_token, token_expires_at)
                      VALUES
                        (:last_name, :first_name, :user_status, :email, :password,
                         :verification_token, :token_expires_at)";

            $stmt    = $this->pdo->prepare($query);
            $success = $stmt->execute([
                'last_name'          => $last_name,
                'first_name'         => $first_name,
                'user_status'        => $user_status,
                'email'              => $email,
                'password'           => $hashedPassword,
                'verification_token' => $token,
                'token_expires_at'   => $expiresAt,
            ]);

            if (!$success) {
                return false;
            }

            return [
                'user_id' => (int) $this->pdo->lastInsertId(),
                'token'   => $token,
            ];
        } catch (PDOException $e) {
            error_log('UserManager::createUserWithVerification - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update user profile information (excluding password)
     *
     * @param int    $user_id     The ID of the user to update
     * @param string $last_name   New last name
     * @param string $first_name  New first name
     * @param string $user_status New academic status
     * @param string $email       New email address
     * @return bool True if the update was successful, false otherwise
     * @throws PDOException If the database query fails
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
                'user_id'     => $user_id,
                'last_name'   => $last_name,
                'first_name'  => $first_name,
                'user_status' => $user_status,
                'email'       => $email,
            ]);
        } catch (PDOException $e) {
            error_log('UserManager::updateUser - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update a user's password
     *
     * The new password is automatically hashed before storage.
     *
     * @param int    $user_id      The ID of the user
     * @param string $new_password The new password in plain text
     * @return bool True if the update was successful, false otherwise
     * @throws PDOException If the database query fails
     */
    public function updatePassword(int $user_id, string $new_password): bool
    {
        try {
            $hashedPassword = $this->hashPassword($new_password);

            $query = "UPDATE USERS SET password = :password WHERE user_id = :user_id";
            $stmt  = $this->pdo->prepare($query);

            return $stmt->execute([
                'user_id'  => $user_id,
                'password' => $hashedPassword,
            ]);
        } catch (PDOException $e) {
            error_log('UserManager::updatePassword - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update a user's first name
     *
     * @param int    $user_id      The ID of the user
     * @param string $newFirstName The new first name
     * @return void
     * @throws PDOException If the database query fails
     */
    public function updateFirstName(int $user_id, string $newFirstName): void
    {
        try {
            $query = 'UPDATE USERS SET first_name = :newFirstName WHERE user_id = :user_id';
            $stmt  = $this->pdo->prepare($query);
            $result = $stmt->execute(['newFirstName' => $newFirstName, 'user_id' => $user_id]);
            error_log('UserManager::updateFirstName - ' . $result);
        } catch (PDOException $e) {
            error_log('UserManager::updateFirstName - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update a user's last name
     *
     * @param int    $user_id     The ID of the user
     * @param string $newLastName The new last name
     * @return void
     * @throws PDOException If the database query fails
     */
    public function updateLastName(int $user_id, string $newLastName): void
    {
        try {
            $query = 'UPDATE USERS SET last_name = :newLastName WHERE user_id = :user_id';
            $stmt  = $this->pdo->prepare($query);
            $stmt->execute(['newLastName' => $newLastName, 'user_id' => $user_id]);
        } catch (PDOException $e) {
            error_log('UserManager::updateLastName - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update a user's email address
     *
     * Checks that the new email is not already in use before updating.
     * Returns true when the email was changed, false when it is already taken.
     *
     * @param int    $user_id  The ID of the user
     * @param string $newEmail The new email address
     * @return bool True if the email was updated, false if already in use
     */
    public function updateEmail(int $user_id, string $newEmail): bool
    {
        $query = 'SELECT email FROM USERS WHERE email = :newEmail';
        $stmt  = $this->pdo->prepare($query);
        $stmt->execute(['newEmail' => $newEmail]);
        $result = $stmt->fetch();

        if ($result === false) {
            $query2 = "UPDATE USERS SET email = :newEmail WHERE user_id = :user_id";
            $stmt2  = $this->pdo->prepare($query2);
            $stmt2->execute(['user_id' => $user_id, 'newEmail' => $newEmail]);
            $_SESSION['email'] = $_POST['newEmail'] ?? '';

            return true;
        }

        error_log('UserManager::updateEmail - email already exists: ' . $newEmail);
        $_SESSION['error_email'] = 'Email déjà existante';

        return false;
    }

    /**
     * Update a user's academic status
     *
     * @param int    $user_id    The ID of the user
     * @param string $userStatus The new academic status
     * @return void
     * @throws PDOException If the database query fails
     */
    public function updateUserStatus(int $user_id, string $userStatus): void
    {
        try {
            $query = 'UPDATE USERS SET user_status = :user_status WHERE user_id = :user_id';
            $stmt  = $this->pdo->prepare($query);
            $stmt->execute(['user_status' => $userStatus, 'user_id' => $user_id]);
        } catch (PDOException $e) {
            error_log('UserManager::updateUserStatus - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Permanently delete a user record
     *
     * This operation cannot be undone. Use softDeleteUser() for a reversible
     * approach that respects the 30-day grace period.
     *
     * @param int $user_id The ID of the user to delete
     * @return bool True if deletion was successful, false otherwise
     * @throws PDOException If the database query fails
     */
    public function deleteUser(int $user_id): bool
    {
        try {
            $query = "DELETE FROM USERS WHERE user_id = :user_id";
            $stmt  = $this->pdo->prepare($query);

            return $stmt->execute(['user_id' => $user_id]);
        } catch (PDOException $e) {
            error_log('UserManager::deleteUser - ' . $e->getMessage());
            throw $e;
        }
    }



    /**
     * Check if an email address already exists in the database
     *
     * Useful for registration validation to prevent duplicate accounts.
     *
     * @param string $email The email address to check
     * @return bool True if the email exists, false otherwise
     * @throws PDOException If the database query fails
     */
    public function emailExists(string $email): bool
    {
        try {
            $query = "SELECT COUNT(*) as count FROM USERS WHERE email = :email";
            $stmt  = $this->pdo->prepare($query);
            $stmt->execute(['email' => $email]);
            $result = $stmt->fetch();

            return $result['count'] > 0;
        } catch (PDOException $e) {
            error_log('UserManager::emailExists - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate a cryptographically secure 64-character hexadecimal token
     *
     * Used for email verification and password reset flows.
     *
     * @return string A 64-character lowercase hexadecimal string
     */
    public function generateVerificationToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Verify an email verification token and activate the user account
     *
     * Looks up the user by token, then:
     *  - Returns a failure result if the token is invalid or already verified.
     *  - Deletes the user row and returns an 'expired' result if the token has
     *    passed its expiry date.
     *  - Marks the account as verified and clears the token on success.
     *
     * @param string $token The raw 64-character verification token
     * @return array{success: bool, message?: string, user_id?: int} Result map
     * @throws PDOException If a database query fails
     */
    public function verifyEmailToken(string $token): array
    {
        try {
            $query = "SELECT user_id, is_verified, token_expires_at
                      FROM USERS
                      WHERE verification_token = :token
                      LIMIT 1";
            $stmt  = $this->pdo->prepare($query);
            $stmt->execute(['token' => $token]);
            $user = $stmt->fetch();

            if ($user === false) {
                return ['success' => false, 'message' => 'Token de vérification invalide'];
            }

            if ((int) $user['is_verified'] === 1) {
                return ['success' => false, 'message' => 'Cet email a déjà été vérifié'];
            }

            if (new \DateTime() > new \DateTime($user['token_expires_at'])) {
                $deleteStmt = $this->pdo->prepare("DELETE FROM USERS WHERE user_id = :user_id");
                $deleteStmt->execute(['user_id' => $user['user_id']]);

                return ['success' => false, 'message' => 'expired'];
            }

            $updateStmt = $this->pdo->prepare(
                "UPDATE USERS
                 SET is_verified = 1, verification_token = NULL, token_expires_at = NULL
                 WHERE user_id = :user_id"
            );
            $updateStmt->execute(['user_id' => $user['user_id']]);

            return ['success' => true, 'user_id' => (int) $user['user_id']];
        } catch (PDOException $e) {
            error_log('UserManager::verifyEmailToken - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check whether a user's email address has been verified
     *
     * @param int $user_id The user's primary key
     * @return bool True if the email is verified, false if not or user not found
     * @throws PDOException If the database query fails
     */
    public function isEmailVerified(int $user_id): bool
    {
        try {
            $query = "SELECT is_verified FROM USERS WHERE user_id = :user_id LIMIT 1";
            $stmt  = $this->pdo->prepare($query);
            $stmt->execute(['user_id' => $user_id]);
            $row = $stmt->fetch();

            if ($row === false) {
                return false;
            }

            return (int) $row['is_verified'] === 1;
        } catch (PDOException $e) {
            error_log('UserManager::isEmailVerified - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate and persist a new email verification token for a user
     *
     * Replaces any previously stored token and extends the expiry by 24 hours.
     *
     * @param int $user_id The user's primary key
     * @return string|false The new 64-character token on success, false on failure
     * @throws PDOException If the database query fails
     */
    public function resendVerificationToken(int $user_id): string|false
    {
        try {
            $token     = $this->generateVerificationToken();
            $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

            $query = "UPDATE USERS
                      SET verification_token = :token,
                          token_expires_at   = :token_expires_at
                      WHERE user_id = :user_id";
            $stmt  = $this->pdo->prepare($query);
            $success = $stmt->execute([
                'token'            => $token,
                'token_expires_at' => $expiresAt,
                'user_id'          => $user_id,
            ]);

            return $success ? $token : false;
        } catch (PDOException $e) {
            error_log('UserManager::resendVerificationToken - ' . $e->getMessage());
            throw $e;
        }
    }


    /**
     * Retrieve a paginated list of active (non-deleted) users
     *
     * Supports optional filtering by blocked status, role, and a full-text
     * search across last_name, first_name and email.
     *
     * @param int    $limit       Maximum number of rows to return
     * @param int    $offset      Number of rows to skip (pagination)
     * @param bool   $showBlocked When true, return only blocked users; otherwise only non-blocked users
     * @param string $role        Role filter: 'admin', 'user', 'super_admin', or 'all'
     * @param string $search      Partial string to search across name and email (empty = no filter)
     * @return array<int, array<string, mixed>> List of matching user rows
     * @throws PDOException If the database query fails
     */
    public function getUsers(int $limit, int $offset, bool $showBlocked, string $role, string $search): array
    {
        try {
            $where  = 'WHERE deleted_at IS NULL AND is_blocked = :is_blocked';
            $params = [];

            if ($role !== 'all') {
                $where   .= ' AND role = :role';
                $params[] = ['name' => ':role', 'value' => $role, 'type' => PDO::PARAM_STR];
            }

            if ($search !== '') {
                $where   .= ' AND (last_name LIKE :search1 OR first_name LIKE :search2 OR email LIKE :search3)';
                $searchVal = '%' . $search . '%';
                $params[]  = ['name' => ':search1', 'value' => $searchVal, 'type' => PDO::PARAM_STR];
                $params[]  = ['name' => ':search2', 'value' => $searchVal, 'type' => PDO::PARAM_STR];
                $params[]  = ['name' => ':search3', 'value' => $searchVal, 'type' => PDO::PARAM_STR];
            }

            $query = "SELECT user_id, last_name, first_name, email, role, is_blocked, user_status
                      FROM USERS
                      {$where}
                      ORDER BY last_name ASC
                      LIMIT :limit OFFSET :offset";

            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':is_blocked', $showBlocked ? 1 : 0, PDO::PARAM_INT);

            foreach ($params as $param) {
                $stmt->bindValue($param['name'], $param['value'], $param['type']);
            }

            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('UserManager::getUsers - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Count active (non-deleted) users matching the given filters
     *
     * Mirrors the filtering logic of getUsers() without pagination.
     *
     * @param bool   $showBlocked When true, count only blocked users; otherwise only non-blocked users
     * @param string $role        Role filter: 'admin', 'user', 'super_admin', or 'all'
     * @param string $search      Partial string to search across name and email (empty = no filter)
     * @return int Total number of matching users
     * @throws PDOException If the database query fails
     */
    public function countUsers(bool $showBlocked, string $role, string $search): int
    {
        try {
            $where  = 'WHERE deleted_at IS NULL AND is_blocked = :is_blocked';
            $params = [':is_blocked' => $showBlocked ? 1 : 0];

            if ($role !== 'all') {
                $where            .= ' AND role = :role';
                $params[':role']   = $role;
            }

            if ($search !== '') {
                $where              .= ' AND (last_name LIKE :search1'
                    . ' OR first_name LIKE :search2 OR email LIKE :search3)';
                $searchVal            = '%' . $search . '%';
                $params[':search1']   = $searchVal;
                $params[':search2']   = $searchVal;
                $params[':search3']   = $searchVal;
            }

            $query = "SELECT COUNT(*) FROM USERS {$where}";
            $stmt  = $this->pdo->prepare($query);
            $stmt->execute($params);

            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('UserManager::countUsers - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Retrieve a paginated list of soft-deleted users
     *
     * Supports optional filtering by role and full-text search.
     *
     * @param int    $limit  Maximum number of rows to return
     * @param int    $offset Number of rows to skip
     * @param string $role   Role filter: 'admin', 'user', 'super_admin', or 'all'
     * @param string $search Partial string to search across name and email
     * @return array<int, array<string, mixed>> List of matching soft-deleted user rows
     * @throws PDOException If the database query fails
     */
    public function getDeletedUsers(int $limit, int $offset, string $role, string $search): array
    {
        try {
            $where  = 'WHERE deleted_at IS NOT NULL';
            $params = [];

            if ($role !== 'all') {
                $where   .= ' AND role = :role';
                $params[] = ['name' => ':role', 'value' => $role, 'type' => PDO::PARAM_STR];
            }

            if ($search !== '') {
                $where    .= ' AND (last_name LIKE :search1 OR first_name LIKE :search2 OR email LIKE :search3)';
                $searchVal = '%' . $search . '%';
                $params[]  = ['name' => ':search1', 'value' => $searchVal, 'type' => PDO::PARAM_STR];
                $params[]  = ['name' => ':search2', 'value' => $searchVal, 'type' => PDO::PARAM_STR];
                $params[]  = ['name' => ':search3', 'value' => $searchVal, 'type' => PDO::PARAM_STR];
            }

            $query = "SELECT user_id, last_name, first_name, email, role, is_blocked, user_status, deleted_at
                      FROM USERS
                      {$where}
                      ORDER BY deleted_at DESC
                      LIMIT :limit OFFSET :offset";

            $stmt = $this->pdo->prepare($query);

            foreach ($params as $param) {
                $stmt->bindValue($param['name'], $param['value'], $param['type']);
            }

            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('UserManager::getDeletedUsers - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Count soft-deleted users matching the given filters
     *
     * @param string $role   Role filter: 'admin', 'user', 'super_admin', or 'all'
     * @param string $search Partial string to search across name and email
     * @return int Total number of matching soft-deleted users
     * @throws PDOException If the database query fails
     */
    public function countDeletedUsers(string $role, string $search): int
    {
        try {
            $where  = 'WHERE deleted_at IS NOT NULL';
            $params = [];

            if ($role !== 'all') {
                $where           .= ' AND role = :role';
                $params[':role']  = $role;
            }

            if ($search !== '') {
                $where              .= ' AND (last_name LIKE :search1'
                    . ' OR first_name LIKE :search2 OR email LIKE :search3)';
                $searchVal            = '%' . $search . '%';
                $params[':search1']   = $searchVal;
                $params[':search2']   = $searchVal;
                $params[':search3']   = $searchVal;
            }

            $query = "SELECT COUNT(*) FROM USERS {$where}";
            $stmt  = $this->pdo->prepare($query);
            $stmt->execute($params);

            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('UserManager::countDeletedUsers - ' . $e->getMessage());
            throw $e;
        }
    }


    /**
     * Retrieve the role of a user by their primary key
     *
     * @param int $user_id The user's primary key
     * @return string The user's role (e.g. 'user', 'admin', 'super_admin'), or empty string if not found
     * @throws PDOException If the database query fails
     */
    public function getUserRoleById(int $user_id): string
    {
        try {
            $query = "SELECT role FROM USERS WHERE user_id = :user_id LIMIT 1";
            $stmt  = $this->pdo->prepare($query);
            $stmt->execute(['user_id' => $user_id]);
            $row = $stmt->fetch();

            return $row !== false ? (string) $row['role'] : '';
        } catch (PDOException $e) {
            error_log('UserManager::getUserRoleById - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update the role of a user
     *
     * @param int    $user_id The user's primary key
     * @param string $role    The new role ('user', 'admin', 'super_admin')
     * @return bool True on success, false otherwise
     * @throws PDOException If the database query fails
     */
    public function updateUserRole(int $user_id, string $role): bool
    {
        try {
            $query = "UPDATE USERS SET role = :role WHERE user_id = :user_id";
            $stmt  = $this->pdo->prepare($query);

            return $stmt->execute(['role' => $role, 'user_id' => $user_id]);
        } catch (PDOException $e) {
            error_log('UserManager::updateUserRole - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Block or unblock a user account
     *
     * @param int $user_id The user's primary key
     * @param int $status  1 to block, 0 to unblock
     * @return bool True on success, false otherwise
     * @throws PDOException If the database query fails
     */
    public function setBlockStatus(int $user_id, int $status): bool
    {
        try {
            $query = "UPDATE USERS SET is_blocked = :status WHERE user_id = :user_id";
            $stmt  = $this->pdo->prepare($query);

            return $stmt->execute(['status' => $status, 'user_id' => $user_id]);
        } catch (PDOException $e) {
            error_log('UserManager::setBlockStatus - ' . $e->getMessage());
            throw $e;
        }
    }



    /**
     * Count active (non-deleted, non-blocked) users for a given role
     *
     * Used to enforce the minimum-one-admin / minimum-one-super_admin rule
     * before performing a destructive action (soft_delete, demote, block).
     *
     * @param string $role The role to count ('admin' or 'super_admin')
     * @return int Number of active users with that role
     * @throws PDOException If the database query fails
     */
    public function countActiveUsersByRole(string $role): int
    {
        try {
            $query = "SELECT COUNT(*)
                      FROM USERS
                      WHERE role       = :role
                        AND deleted_at IS NULL
                        AND is_blocked = 0";

            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':role', $role, PDO::PARAM_STR);
            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('UserManager::countActiveUsersByRole - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Soft-delete a user account (30-day grace period)
     *
     * Sets the deleted_at timestamp to the current time. The account can be
     * restored within 30 days before the cron job anonymizes it permanently.
     *
     * @param int $user_id The user's primary key
     * @return bool True on success, false otherwise
     * @throws PDOException If the database query fails
     */
    public function softDeleteUser(int $user_id): bool
    {
        try {
            $query = "UPDATE USERS SET deleted_at = NOW() WHERE user_id = :user_id";
            $stmt  = $this->pdo->prepare($query);

            return $stmt->execute(['user_id' => $user_id]);
        } catch (PDOException $e) {
            error_log('UserManager::softDeleteUser - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Restore a soft-deleted user account
     *
     * Clears the deleted_at timestamp, making the account active again.
     *
     * @param int $user_id The user's primary key
     * @return bool True on success, false otherwise
     * @throws PDOException If the database query fails
     */
    public function restoreUser(int $user_id): bool
    {
        try {
            $query = "UPDATE USERS SET deleted_at = NULL WHERE user_id = :user_id";
            $stmt  = $this->pdo->prepare($query);

            return $stmt->execute(['user_id' => $user_id]);
        } catch (PDOException $e) {
            error_log('UserManager::restoreUser - ' . $e->getMessage());
            throw $e;
        }
    }



    /**
     * Retrieve soft-deleted users whose grace period has expired
     *
     * Returns all user rows where deleted_at is older than the given number
     * of days. Used by the nightly cron job to identify accounts to anonymize.
     *
     * @param int $days Number of days after which a soft-deleted account is considered expired
     * @return array<int, array<string, mixed>> List of expired user rows
     * @throws PDOException If the database query fails
     */
    public function getExpiredDeletedUsers(int $days): array
    {
        try {
            $query = "SELECT user_id, email, first_name, last_name, deleted_at
                      FROM USERS
                      WHERE deleted_at IS NOT NULL
                        AND deleted_at <= DATE_SUB(NOW(), INTERVAL :days DAY)";

            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':days', $days, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('UserManager::getExpiredDeletedUsers - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Anonymize a user record by replacing all personal data with placeholders
     *
     * Replaces email, first_name, last_name, password and verification token
     * with non-identifying values. The row itself is preserved for referential
     * and statistical integrity.
     *
     * @param int $user_id The user's primary key
     * @return bool True on success, false otherwise
     * @throws PDOException If the database query fails
     */
    public function anonymizeUser(int $user_id): bool
    {
        try {
            $placeholder = bin2hex(random_bytes(8));

            $query = "UPDATE USERS
                      SET email              = CONCAT('deleted_', :placeholder, '@anonymous.local'),
                          first_name         = 'deleted_user',
                          last_name          = 'anonymous',
                          password           = '',
                          verification_token = NULL,
                          token_expires_at   = NULL
                      WHERE user_id = :user_id";

            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':placeholder', $placeholder, PDO::PARAM_STR);
            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('UserManager::anonymizeUser - ' . $e->getMessage());
            throw $e;
        }
    }
}

\class_alias(__NAMESPACE__ . '\\UserManager', 'UserManager');
