<?php

/**
 * Script to clean expired unverified users based on email verification tokens.
 *
 * This script removes users whose email verification token has expired
 * (token_expires_at < NOW()) and who are still marked as not verified.
 * It can be executed manually or scheduled via a cron job.
 */

declare(strict_types=1);

// Set timezone to France (Europe/Paris)
date_default_timezone_set('Europe/Paris');

// Load configuration
require_once __DIR__ . '/../Config/config.php';

// Composer autoload (PSR-4)
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();

    // Set MySQL session timezone to Europe/Paris to match PHP timezone
    $db->exec("SET time_zone = '+01:00'");

    // Delete expired unverified users
    // token_expires_at is stored in DATETIME format, compared with NOW() in Europe/Paris timezone
    $query = "DELETE FROM USERS 
              WHERE is_verified = 0 
                AND verification_token IS NOT NULL 
                AND token_expires_at IS NOT NULL 
                AND token_expires_at < NOW()";

    $stmt = $db->prepare($query);
    $stmt->execute();

    $deletedCount = $stmt->rowCount();

    echo date('Y-m-d H:i:s') . ' - Deleted ' . $deletedCount . " expired unverified user(s)\n";
} catch (Throwable $e) {
    echo date('Y-m-d H:i:s') . ' - Error while cleaning expired unverified users: ' . $e->getMessage() . "\n";
}
