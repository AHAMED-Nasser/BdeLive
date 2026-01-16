<?php

declare(strict_types=1);

namespace App\Core\Exception;

use Exception;

/**
 * Exception thrown when CSRF token validation fails
 *
 * Used by CsrfProtection::requireValidToken() for invalid or expired tokens.
 * HTTP status code: 403 Forbidden
 *
 * @author BDELIVE - Groupe 8
 * @package App\Core\Exception
 * @version 1.0.0
 */
class CsrfException extends Exception
{
    public function __construct(
        string $message = 'CSRF token validation failed',
        int $code = 403,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
