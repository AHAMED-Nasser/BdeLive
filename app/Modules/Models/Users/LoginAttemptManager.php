<?php

declare(strict_types=1);

namespace App\Modules\Models\Users;

use PDO;
use PDOException;
use App\Core\Database;

/**
 * LoginAttemptManager
 *
 * Tracks and manages failed login attempts per IP address and email.
 * Used by LoginController to apply conditional anti-brute-force logic
 * with Google reCAPTCHA v2 when a threshold of failed attempts is reached.
 *
 * @package App\Modules\Models\Users
 * @version 1.0.0
 */
class LoginAttemptManager
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Count failed login attempts for a given IP and email within a time window
     *
     * @param string $ip        The client IP address
     * @param string $email     The submitted email address
     * @param int    $minutes   Size of the sliding window in minutes (default: 15)
     * @return int Number of failed attempts in the window
     * @throws PDOException If the database query fails
     */
    public function countRecentAttempts(string $ip, string $email, int $minutes = 15): int
    {
        try {
            $query = "SELECT COUNT(*) FROM LOGIN_ATTEMPTS
                      WHERE ip_address = :ip
                        AND email      = :email
                        AND attempted_at >= DATE_SUB(NOW(), INTERVAL :minutes MINUTE)";

            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->bindValue(':minutes', $minutes, PDO::PARAM_INT);
            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('LoginAttemptManager::countRecentAttempts - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Record a new failed login attempt
     *
     * @param string $ip    The client IP address
     * @param string $email The submitted email address
     * @return void
     * @throws PDOException If the database query fails
     */
    public function recordFailedAttempt(string $ip, string $email): void
    {
        try {
            $query = "INSERT INTO LOGIN_ATTEMPTS (ip_address, email, attempted_at)
                      VALUES (:ip, :email, NOW())";

            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log('LoginAttemptManager::recordFailedAttempt - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Remove all failed login attempt records for a given IP and email
     *
     * Called after a successful login to reset the brute-force counter.
     *
     * @param string $ip    The client IP address
     * @param string $email The authenticated user's email address
     * @return void
     * @throws PDOException If the database query fails
     */
    public function clearAttempts(string $ip, string $email): void
    {
        try {
            $query = "DELETE FROM LOGIN_ATTEMPTS
                      WHERE ip_address = :ip
                        AND email      = :email";

            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log('LoginAttemptManager::clearAttempts - ' . $e->getMessage());
            throw $e;
        }
    }
}
