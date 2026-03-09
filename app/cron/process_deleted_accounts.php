<?php

/**
 * Cron script to process accounts whose 30-day grace period has expired.
 *
 * All expired accounts are anonymized: personally identifiable information
 * (email, first name, last name, password, verification token) is replaced
 * with non-identifying placeholder values. The record itself is kept to
 * preserve statistical integrity and referential consistency.
 *
 * Suggested crontab entry (runs every night at midnight):
 *   0 0 * * * php /path/to/app/cron/process_deleted_accounts.php >> /var/log/bdelive_cleanup.log 2>&1
 */

declare(strict_types=1);

use App\Modules\Models\Users\UserManager;
use Throwable;

date_default_timezone_set('Europe/Paris');

require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

try {
    $userManager = new UserManager();

    $expiredUsers = $userManager->getExpiredDeletedUsers(30);
    $processedCount = 0;
    $errorCount = 0;

    foreach ($expiredUsers as $user) {
        $userId = (int) $user['user_id'];

        try {
            $userManager->anonymizeUser($userId);
            $processedCount++;
        } catch (Throwable $e) {
            $errorCount++;
            error_log(
                'process_deleted_accounts: failed to process user_id=' . $userId
                . ' - ' . $e->getMessage()
            );
        }
    }

    echo date('Y-m-d H:i:s')
        . ' - Processed ' . $processedCount . ' expired account(s)'
        . ($errorCount > 0 ? ' (' . $errorCount . ' error(s))' : '')
        . "\n";
} catch (Throwable $e) {
    error_log('process_deleted_accounts: ' . $e->getMessage());
    echo date('Y-m-d H:i:s') . ' - Fatal error: ' . $e->getMessage() . "\n";
    exit(1);
}
