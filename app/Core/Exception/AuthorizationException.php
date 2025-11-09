<?php

declare(strict_types=1);

namespace App\Core\Exception;

use Exception;

/**
 * Exception thrown when an authenticated user lacks the required permissions
 *
 * Used by AuthManager::requireAdmin() when a user is not an administrator.
 * HTTP status code: 403 Forbidden
 *
 * @package App\Core\Exception
 * @version 1.0.0
 */
class AuthorizationException extends Exception
{
    public function __construct(string $message = 'Access denied', int $code = 403, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
