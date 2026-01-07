<?php

declare(strict_types=1);

namespace App\Modules\Models\Users;

use PDO;
use PDOException;
use App\Core\Database;

/**
 * Privacy Manager Model
 *
 * Handles all privacy-related database operations including account blocking,
 * password change tokens, email change verification, and security tracking.
 * Manages the security aspects of user account modifications.
 *
 * @author BdeLive Team
 * @version 1.0.0
 * @package BdeLive\Models\Users
 */
class PrivacyManager
{
    /**
     * PDO database connection instance
     *
     * @var PDO
     */
    private PDO $pdo;

    /**
     * Block duration in minutes
     *
     * @var int
     */
    private const BLOCK_DURATION_MINUTES = 30;

    /**
     * Maximum failed attempts before blocking
     *
     * @var int
     */
    private const MAX_FAILED_ATTEMPTS = 10;

    /**
     * Resend code cooldown in seconds
     *
     * @var int
     */
    private const RESEND_COOLDOWN_SECONDS = 60;

    /**
     * Maximum code resend attempts
     *
     * @var int
     */
    private const MAX_RESEND_ATTEMPTS = 5;

    /**
     * Constructor - Initialize the PrivacyManager
     *
     * Retrieves the database connection from the Database singleton.
     *
     * @return void
     */
    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
        $this->pdo->exec('SET CHARACTER SET utf8');
    }

    /**
     * Check if user account is blocked for privacy modifications
     *
     * Verifies if the user's account is currently blocked and if the
     * block period has expired. Automatically unblocks if time has passed.
     *
     * @param int $userId The user ID to check
     * @return bool True if blocked, false otherwise
     */
    public function isUserBlocked(int $userId): bool
    {
        try {
            $query = "SELECT is_blocked, blocked_until FROM USERS WHERE user_id = :user_id LIMIT 1";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['user_id' => $userId]);
            $result = $stmt->fetch();

            if (!$result || (int)$result['is_blocked'] !== 1) {
                return false;
            }

            // Check if block period has expired
            if ($result['blocked_until'] !== null) {
                $blockedUntil = strtotime($result['blocked_until']);
                if (time() > $blockedUntil) {
                    // Automatically unblock
                    $this->unblockUser($userId);
                    return false;
                }
            }

            return true;
        } catch (PDOException $e) {
            error_log('PrivacyManager::isUserBlocked - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get remaining block time in minutes
     *
     * Returns the number of minutes remaining until the account is unblocked.
     *
     * @param int $userId The user ID to check
     * @return int Minutes remaining, 0 if not blocked
     */
    public function getRemainingBlockTime(int $userId): int
    {
        try {
            $query = "SELECT blocked_until FROM USERS WHERE user_id = :user_id AND is_blocked = 1 LIMIT 1";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['user_id' => $userId]);
            $result = $stmt->fetch();

            if (!$result || $result['blocked_until'] === null) {
                return 0;
            }

            $blockedUntil = strtotime($result['blocked_until']);
            $remaining = $blockedUntil - time();

            return $remaining > 0 ? (int)ceil($remaining / 60) : 0;
        } catch (PDOException $e) {
            error_log('PrivacyManager::getRemainingBlockTime - ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Block user account for privacy modifications
     *
     * Sets the is_blocked flag and blocked_until timestamp for the user.
     *
     * @param int $userId The user ID to block
     * @return bool True if successful, false otherwise
     */
    public function blockUser(int $userId): bool
    {
        try {
            date_default_timezone_set('Europe/Paris');
            $blockedUntil = date('Y-m-d H:i:s', strtotime('+' . self::BLOCK_DURATION_MINUTES . ' minutes'));

            $query = "UPDATE USERS SET is_blocked = 1, blocked_until = :blocked_until WHERE user_id = :user_id";
            $stmt = $this->pdo->prepare($query);

            return $stmt->execute([
                'blocked_until' => $blockedUntil,
                'user_id' => $userId
            ]);
        } catch (PDOException $e) {
            error_log('PrivacyManager::blockUser - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Unblock user account
     *
     * Removes the block from the user's account.
     *
     * @param int $userId The user ID to unblock
     * @return bool True if successful, false otherwise
     */
    public function unblockUser(int $userId): bool
    {
        try {
            $query = "UPDATE USERS SET is_blocked = 0, blocked_until = NULL WHERE user_id = :user_id";
            $stmt = $this->pdo->prepare($query);

            return $stmt->execute(['user_id' => $userId]);
        } catch (PDOException $e) {
            error_log('PrivacyManager::unblockUser - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate a secure 6-digit verification code
     *
     * Creates a cryptographically secure random 6-digit code.
     *
     * @return string The 6-digit verification code
     */
    public function generateVerificationCode(): string
    {
        return str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Create a password change verification token
     *
     * Stores a 6-digit verification code for password change confirmation.
     * Uses the existing PASSWORD_RESET_TOKEN table.
     * The code expires after 10 minutes.
     *
     * @param int $userId The user ID
     * @param string $code The 6-digit verification code
     * @return bool True if successful, false otherwise
     */
    public function createPasswordChangeToken(int $userId, string $code): bool
    {
        try {
            // Delete any existing unused password tokens for this user (not EMAIL tokens)
            $deleteQuery = "DELETE FROM PASSWORD_RESET_TOKEN WHERE user_id = :user_id AND is_used = 0 AND token NOT LIKE 'EMAIL:%'";
            $deleteStmt = $this->pdo->prepare($deleteQuery);
            $deleteStmt->execute(['user_id' => $userId]);

            // Use MySQL NOW() and DATE_ADD for consistent timezone handling
            $query = "INSERT INTO PASSWORD_RESET_TOKEN (user_id, token, expires_at, is_used, attempts, resend_count, last_resend_at) 
                      VALUES (:user_id, :token, DATE_ADD(NOW(), INTERVAL 10 MINUTE), 0, 0, 1, NOW())";
            $stmt = $this->pdo->prepare($query);

            return $stmt->execute([
                'user_id' => $userId,
                'token' => $code
            ]);
        } catch (PDOException $e) {
            error_log('PrivacyManager::createPasswordChangeToken - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify password change token
     *
     * Validates the 6-digit code and increments attempt counter.
     * Returns status indicating if code is valid, expired, or max attempts reached.
     * Uses MySQL for expiration check for consistent timezone handling.
     *
     * @param int $userId The user ID
     * @param string $code The verification code to check
     * @return array{valid: bool, message: string, attempts: int} Verification result
     */
    public function verifyPasswordChangeToken(int $userId, string $code): array
    {
        try {
            // Use MySQL NOW() for consistent timezone comparison
            $query = "SELECT id, token, attempts, (expires_at < NOW()) as is_expired 
                      FROM PASSWORD_RESET_TOKEN 
                      WHERE user_id = :user_id AND is_used = 0 AND token NOT LIKE 'EMAIL:%'
                      ORDER BY id DESC LIMIT 1";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['user_id' => $userId]);
            $result = $stmt->fetch();

            if (!$result) {
                return ['valid' => false, 'message' => 'Aucun code de vérification trouvé', 'attempts' => 0];
            }

            $currentAttempts = (int)($result['attempts'] ?? 0) + 1;

            // Update attempts count
            $updateQuery = "UPDATE PASSWORD_RESET_TOKEN SET attempts = :attempts WHERE id = :id";
            $updateStmt = $this->pdo->prepare($updateQuery);
            $updateStmt->execute(['attempts' => $currentAttempts, 'id' => $result['id']]);

            // Check if max attempts reached
            if ($currentAttempts >= self::MAX_FAILED_ATTEMPTS) {
                $this->deletePasswordChangeToken($userId);
                return [
                    'valid' => false,
                    'message' => 'Nombre maximum de tentatives atteint',
                    'attempts' => $currentAttempts
                ];
            }

            // Check if token expired (using MySQL's comparison result)
            if ((int)$result['is_expired'] === 1) {
                return ['valid' => false, 'message' => 'Le code a expiré', 'attempts' => $currentAttempts];
            }

            // Check if code matches
            if ($result['token'] !== $code) {
                return [
                    'valid' => false,
                    'message' => 'Code incorrect. Tentative ' . $currentAttempts . '/' . self::MAX_FAILED_ATTEMPTS,
                    'attempts' => $currentAttempts
                ];
            }

            return ['valid' => true, 'message' => 'Code vérifié avec succès', 'attempts' => $currentAttempts];
        } catch (PDOException $e) {
            error_log('PrivacyManager::verifyPasswordChangeToken - ' . $e->getMessage());
            return ['valid' => false, 'message' => 'Erreur de vérification', 'attempts' => 0];
        }
    }

    /**
     * Check if user can resend verification code
     *
     * Verifies if the cooldown period has passed and max resends not reached.
     * Uses SQL TIMESTAMPDIFF for consistent timezone handling.
     *
     * @param int $userId The user ID
     * @return array{can_resend: bool, wait_seconds: int, resend_count: int} Resend status
     */
    public function canResendCode(int $userId): array
    {
        try {
            // Use SQL to calculate seconds difference for consistent timezone handling
            $query = "SELECT resend_count, last_resend_at, 
                      TIMESTAMPDIFF(SECOND, last_resend_at, NOW()) as seconds_since_resend
                      FROM PASSWORD_RESET_TOKEN 
                      WHERE user_id = :user_id AND is_used = 0 AND token NOT LIKE 'EMAIL:%'
                      ORDER BY id DESC LIMIT 1";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['user_id' => $userId]);
            $result = $stmt->fetch();

            if (!$result) {
                return ['can_resend' => true, 'wait_seconds' => 0, 'resend_count' => 0];
            }

            $resendCount = (int)($result['resend_count'] ?? 0);
            $secondsSinceLastResend = (int)($result['seconds_since_resend'] ?? self::RESEND_COOLDOWN_SECONDS);
            $waitSeconds = max(0, self::RESEND_COOLDOWN_SECONDS - $secondsSinceLastResend);

            // Check max resends
            if ($resendCount >= self::MAX_RESEND_ATTEMPTS) {
                return [
                    'can_resend' => false,
                    'wait_seconds' => self::BLOCK_DURATION_MINUTES * 60,
                    'resend_count' => $resendCount
                ];
            }

            return [
                'can_resend' => $waitSeconds === 0,
                'wait_seconds' => $waitSeconds,
                'resend_count' => $resendCount
            ];
        } catch (PDOException $e) {
            error_log('PrivacyManager::canResendCode - ' . $e->getMessage());
            return ['can_resend' => false, 'wait_seconds' => 60, 'resend_count' => 0];
        }
    }

    /**
     * Update token for resend
     *
     * Updates the verification code and increments resend counter.
     * Uses MySQL functions for consistent timezone handling.
     *
     * @param int $userId The user ID
     * @param string $newCode The new verification code
     * @return bool True if successful, false otherwise
     */
    public function resendCode(int $userId, string $newCode): bool
    {
        try {
            // Use MySQL DATE_ADD for consistent timezone handling
            $query = "UPDATE PASSWORD_RESET_TOKEN 
                      SET token = :token, expires_at = DATE_ADD(NOW(), INTERVAL 10 MINUTE), attempts = 0, 
                          resend_count = resend_count + 1, last_resend_at = NOW() 
                      WHERE user_id = :user_id AND is_used = 0 AND token NOT LIKE 'EMAIL:%'";
            $stmt = $this->pdo->prepare($query);

            return $stmt->execute([
                'token' => $newCode,
                'user_id' => $userId
            ]);
        } catch (PDOException $e) {
            error_log('PrivacyManager::resendCode - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete password change token
     *
     * Removes the verification token after successful verification or max attempts.
     *
     * @param int $userId The user ID
     * @return bool True if successful, false otherwise
     */
    public function deletePasswordChangeToken(int $userId): bool
    {
        try {
            $query = "DELETE FROM PASSWORD_RESET_TOKEN WHERE user_id = :user_id AND is_used = 0";
            $stmt = $this->pdo->prepare($query);

            return $stmt->execute(['user_id' => $userId]);
        } catch (PDOException $e) {
            error_log('PrivacyManager::deletePasswordChangeToken - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Track email change attempt
     *
     * Increments the failed attempt counter for email changes.
     * Returns the current attempt count.
     *
     * @param int $userId The user ID
     * @return int Current attempt count
     */
    public function trackEmailChangeAttempt(int $userId): int
    {
        try {
            // Get current attempts from session or create tracking
            $query = "SELECT email_change_attempts FROM USERS WHERE user_id = :user_id LIMIT 1";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['user_id' => $userId]);
            $result = $stmt->fetch();

            $attempts = ($result['email_change_attempts'] ?? 0) + 1;

            $updateQuery = "UPDATE USERS SET email_change_attempts = :attempts WHERE user_id = :user_id";
            $updateStmt = $this->pdo->prepare($updateQuery);
            $updateStmt->execute(['attempts' => $attempts, 'user_id' => $userId]);

            return $attempts;
        } catch (PDOException $e) {
            error_log('PrivacyManager::trackEmailChangeAttempt - ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Reset email change attempts counter
     *
     * Resets the failed attempt counter after successful email change.
     *
     * @param int $userId The user ID
     * @return bool True if successful, false otherwise
     */
    public function resetEmailChangeAttempts(int $userId): bool
    {
        try {
            $query = "UPDATE USERS SET email_change_attempts = 0 WHERE user_id = :user_id";
            $stmt = $this->pdo->prepare($query);

            return $stmt->execute(['user_id' => $userId]);
        } catch (PDOException $e) {
            error_log('PrivacyManager::resetEmailChangeAttempts - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get email change attempts count
     *
     * Returns the current number of failed email change attempts.
     *
     * @param int $userId The user ID
     * @return int Current attempt count
     */
    public function getEmailChangeAttempts(int $userId): int
    {
        try {
            $query = "SELECT email_change_attempts FROM USERS WHERE user_id = :user_id LIMIT 1";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['user_id' => $userId]);
            $result = $stmt->fetch();

            return (int)($result['email_change_attempts'] ?? 0);
        } catch (PDOException $e) {
            error_log('PrivacyManager::getEmailChangeAttempts - ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Check if max email change attempts reached
     *
     * @param int $userId The user ID
     * @return bool True if max attempts reached, false otherwise
     */
    public function isMaxEmailChangeAttemptsReached(int $userId): bool
    {
        return $this->getEmailChangeAttempts($userId) >= self::MAX_FAILED_ATTEMPTS;
    }

    /**
     * Validate email format
     *
     * Checks if the provided email has a valid format.
     *
     * @param string $email The email to validate
     * @return bool True if valid format, false otherwise
     */
    public function isValidEmailFormat(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate password format
     *
     * Checks if the password meets requirements:
     * - Minimum 10 characters
     * - At least 1 uppercase letter
     * - At least 1 digit
     * - At least 1 special character
     *
     * @param string $password The password to validate
     * @return array{valid: bool, errors: array<string>} Validation result with errors
     */
    public function validatePasswordFormat(string $password): array
    {
        $errors = [];

        if (strlen($password) < 10) {
            $errors[] = 'Le mot de passe doit contenir au moins 10 caractères';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins une lettre majuscule';
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins un chiffre';
        }

        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins un caractère spécial';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Get max failed attempts constant
     *
     * @return int Maximum failed attempts allowed
     */
    public function getMaxFailedAttempts(): int
    {
        return self::MAX_FAILED_ATTEMPTS;
    }

    /**
     * Get resend cooldown constant
     *
     * @return int Cooldown seconds between resends
     */
    public function getResendCooldown(): int
    {
        return self::RESEND_COOLDOWN_SECONDS;
    }

    /**
     * Get max resend attempts constant
     *
     * @return int Maximum resend attempts allowed
     */
    public function getMaxResendAttempts(): int
    {
        return self::MAX_RESEND_ATTEMPTS;
    }

    // ========== EMAIL CHANGE TOKEN METHODS ==========

    /**
     * Create an email change verification token
     *
     * Stores a 6-digit verification code for email change confirmation.
     * Uses the PASSWORD_RESET_TOKEN table with a special marker.
     * The code expires after 10 minutes.
     *
     * @param int $userId The user ID
     * @param string $code The 6-digit verification code
     * @param string $newEmail The new email address to store
     * @return bool True if successful, false otherwise
     */
    public function createEmailChangeToken(int $userId, string $code, string $newEmail): bool
    {
        try {
            // Delete any existing email change tokens for this user (token starting with EMAIL:)
            $deleteQuery = "DELETE FROM PASSWORD_RESET_TOKEN WHERE user_id = :user_id AND token LIKE 'EMAIL:%'";
            $deleteStmt = $this->pdo->prepare($deleteQuery);
            $deleteStmt->execute(['user_id' => $userId]);

            // Store code with EMAIL: prefix and new email
            $tokenData = 'EMAIL:' . $code . ':' . $newEmail;

            // Use MySQL DATE_ADD for consistent timezone handling
            $query = "INSERT INTO PASSWORD_RESET_TOKEN (user_id, token, expires_at, is_used, attempts, resend_count, last_resend_at) 
                      VALUES (:user_id, :token, DATE_ADD(NOW(), INTERVAL 10 MINUTE), 0, 0, 1, NOW())";
            $stmt = $this->pdo->prepare($query);

            return $stmt->execute([
                'user_id' => $userId,
                'token' => $tokenData
            ]);
        } catch (PDOException $e) {
            error_log('PrivacyManager::createEmailChangeToken - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify email change token
     *
     * Validates the 6-digit code and increments attempt counter.
     * Returns status indicating if code is valid, expired, or max attempts reached.
     * Uses MySQL for expiration check for consistent timezone handling.
     *
     * @param int $userId The user ID
     * @param string $code The verification code to check
     * @return array{valid: bool, message: string, attempts: int} Verification result
     */
    public function verifyEmailChangeToken(int $userId, string $code): array
    {
        try {
            // Use MySQL NOW() for consistent timezone comparison
            $query = "SELECT id, token, attempts, (expires_at < NOW()) as is_expired 
                      FROM PASSWORD_RESET_TOKEN 
                      WHERE user_id = :user_id AND token LIKE 'EMAIL:%' AND is_used = 0 
                      ORDER BY id DESC LIMIT 1";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['user_id' => $userId]);
            $result = $stmt->fetch();

            if (!$result) {
                return ['valid' => false, 'message' => 'Aucun code de vérification trouvé', 'attempts' => 0];
            }

            $currentAttempts = (int)($result['attempts'] ?? 0) + 1;

            // Update attempts count
            $updateQuery = "UPDATE PASSWORD_RESET_TOKEN SET attempts = :attempts WHERE id = :id";
            $updateStmt = $this->pdo->prepare($updateQuery);
            $updateStmt->execute(['attempts' => $currentAttempts, 'id' => $result['id']]);

            // Check if max attempts reached
            if ($currentAttempts >= self::MAX_FAILED_ATTEMPTS) {
                $this->deleteEmailChangeToken($userId);
                return [
                    'valid' => false,
                    'message' => 'Nombre maximum de tentatives atteint',
                    'attempts' => $currentAttempts
                ];
            }

            // Check if token expired (using MySQL's comparison result)
            if ((int)$result['is_expired'] === 1) {
                return ['valid' => false, 'message' => 'Le code a expiré', 'attempts' => $currentAttempts];
            }

            // Extract stored code from token (format: EMAIL:CODE:NEWEMAIL)
            $tokenParts = explode(':', $result['token']);
            $storedCode = $tokenParts[1] ?? '';

            // Check if code matches
            if ($storedCode !== $code) {
                return [
                    'valid' => false,
                    'message' => 'Code incorrect. Tentative ' . $currentAttempts . '/' . self::MAX_FAILED_ATTEMPTS,
                    'attempts' => $currentAttempts
                ];
            }

            return ['valid' => true, 'message' => 'Code vérifié avec succès', 'attempts' => $currentAttempts];
        } catch (PDOException $e) {
            error_log('PrivacyManager::verifyEmailChangeToken - ' . $e->getMessage());
            return ['valid' => false, 'message' => 'Erreur de vérification', 'attempts' => 0];
        }
    }

    /**
     * Check if user can resend email verification code
     *
     * Verifies if the cooldown period has passed and max resends not reached.
     * Uses SQL TIMESTAMPDIFF for consistent timezone handling.
     *
     * @param int $userId The user ID
     * @return array{can_resend: bool, wait_seconds: int, resend_count: int} Resend status
     */
    public function canResendEmailCode(int $userId): array
    {
        try {
            // Use SQL to calculate seconds difference for consistent timezone handling
            $query = "SELECT resend_count, last_resend_at,
                      TIMESTAMPDIFF(SECOND, last_resend_at, NOW()) as seconds_since_resend
                      FROM PASSWORD_RESET_TOKEN 
                      WHERE user_id = :user_id AND token LIKE 'EMAIL:%' AND is_used = 0 
                      ORDER BY id DESC LIMIT 1";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['user_id' => $userId]);
            $result = $stmt->fetch();

            if (!$result) {
                return ['can_resend' => true, 'wait_seconds' => 0, 'resend_count' => 0];
            }

            $resendCount = (int)($result['resend_count'] ?? 0);
            $secondsSinceLastResend = (int)($result['seconds_since_resend'] ?? self::RESEND_COOLDOWN_SECONDS);
            $waitSeconds = max(0, self::RESEND_COOLDOWN_SECONDS - $secondsSinceLastResend);

            // Check max resends
            if ($resendCount >= self::MAX_RESEND_ATTEMPTS) {
                return [
                    'can_resend' => false,
                    'wait_seconds' => self::BLOCK_DURATION_MINUTES * 60,
                    'resend_count' => $resendCount
                ];
            }

            return [
                'can_resend' => $waitSeconds === 0,
                'wait_seconds' => $waitSeconds,
                'resend_count' => $resendCount
            ];
        } catch (PDOException $e) {
            error_log('PrivacyManager::canResendEmailCode - ' . $e->getMessage());
            return ['can_resend' => false, 'wait_seconds' => 60, 'resend_count' => 0];
        }
    }

    /**
     * Resend email verification code
     *
     * Updates the verification code and increments resend counter.
     * Preserves the pending email address.
     * Uses MySQL functions for consistent timezone handling.
     *
     * @param int $userId The user ID
     * @param string $newCode The new verification code
     * @return bool True if successful, false otherwise
     */
    public function resendEmailCode(int $userId, string $newCode): bool
    {
        try {
            // First get the current pending email
            $selectQuery = "SELECT token FROM PASSWORD_RESET_TOKEN 
                           WHERE user_id = :user_id AND token LIKE 'EMAIL:%' AND is_used = 0 
                           ORDER BY id DESC LIMIT 1";
            $selectStmt = $this->pdo->prepare($selectQuery);
            $selectStmt->execute(['user_id' => $userId]);
            $result = $selectStmt->fetch();

            if (!$result) {
                return false;
            }

            // Extract email from token (format: EMAIL:CODE:NEWEMAIL)
            $tokenParts = explode(':', $result['token']);
            $pendingEmail = $tokenParts[2] ?? '';

            if (empty($pendingEmail)) {
                return false;
            }

            $newTokenData = 'EMAIL:' . $newCode . ':' . $pendingEmail;

            // Use MySQL DATE_ADD for consistent timezone handling
            $query = "UPDATE PASSWORD_RESET_TOKEN 
                      SET token = :token, expires_at = DATE_ADD(NOW(), INTERVAL 10 MINUTE), attempts = 0, 
                          resend_count = resend_count + 1, last_resend_at = NOW() 
                      WHERE user_id = :user_id AND token LIKE 'EMAIL:%' AND is_used = 0";
            $stmt = $this->pdo->prepare($query);

            return $stmt->execute([
                'token' => $newTokenData,
                'user_id' => $userId
            ]);
        } catch (PDOException $e) {
            error_log('PrivacyManager::resendEmailCode - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete email change token
     *
     * Removes the verification token after successful verification or max attempts.
     *
     * @param int $userId The user ID
     * @return bool True if successful, false otherwise
     */
    public function deleteEmailChangeToken(int $userId): bool
    {
        try {
            $query = "DELETE FROM PASSWORD_RESET_TOKEN WHERE user_id = :user_id AND token LIKE 'EMAIL:%'";
            $stmt = $this->pdo->prepare($query);

            return $stmt->execute(['user_id' => $userId]);
        } catch (PDOException $e) {
            error_log('PrivacyManager::deleteEmailChangeToken - ' . $e->getMessage());
            return false;
        }
    }

    // ========== RENAMED PASSWORD CODE METHODS FOR CLARITY ==========

    /**
     * Check if user can resend password verification code
     *
     * Alias for canResendCode, specifically for password changes.
     *
     * @param int $userId The user ID
     * @return array{can_resend: bool, wait_seconds: int, resend_count: int} Resend status
     */
    public function canResendPasswordCode(int $userId): array
    {
        return $this->canResendCode($userId);
    }

    /**
     * Resend password verification code
     *
     * Alias for resendCode, specifically for password changes.
     *
     * @param int $userId The user ID
     * @param string $newCode The new verification code
     * @return bool True if successful, false otherwise
     */
    public function resendPasswordCode(int $userId, string $newCode): bool
    {
        return $this->resendCode($userId, $newCode);
    }
}
