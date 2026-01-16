<?php

namespace App\Config;

/**
 * Mailer stub for PHPStan analysis
 *
 * This stub file allows PHPStan to understand the Mailer class
 * even though the actual implementation is in app/Config/ which is gitignored.
 */
class Mailer
{
    /**
     * Send a password reset email
     *
     * @param string $to_email Recipient email address
     * @param string $to_name Recipient name
     * @param string $token Reset token
     * @return bool True if email was sent successfully, false otherwise
     */
    public function sendPasswordResetEmail(string $to_email, string $to_name, string $token): bool
    {
        return false;
    }
}
