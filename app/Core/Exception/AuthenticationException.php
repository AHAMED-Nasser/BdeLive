<?php

declare(strict_types=1);

namespace App\Core\Exception;

use Exception;

/**
 * Exception thrown when an unauthenticated user attempts to access a protected resource
 *
 * Used by AuthManager::requireAuthentication() when a user is not logged in.
 * HTTP status code: 401 Unauthorized
 *
 * @author BDELIVE - Groupe 8
 * @package App\Core\Exception
 * @version 1.0.0
 */
class AuthenticationException extends Exception
{
    public function __construct(
        string $message = 'Authentication required',
        int $code = 401,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
