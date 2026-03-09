<?php

declare(strict_types=1);

namespace App\Core\Security;

/**
 * Validates Google reCAPTCHA v2 responses via the siteverify API.
 *
 * @package App\Core\Security
 * @version 1.0.0
 */
class RecaptchaValidator
{
    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';
    private const TIMEOUT_SECONDS = 3;

    /**
     * Validate a reCAPTCHA response token
     *
     * @param string $token   The g-recaptcha-response value from the form
     * @param string $clientIp The client IP address (optional, sent to Google)
     * @return bool True if Google confirms the token is valid
     */
    public function validate(string $token, string $clientIp = ''): bool
    {
        $secretKey = (string) ($_ENV['RECAPTCHA_SECRET_KEY'] ?? '');

        if (empty($secretKey)) {
            error_log('RecaptchaValidator::validate - RECAPTCHA_SECRET_KEY is not set in the environment.');
            return false;
        }

        if (empty($token)) {
            return false;
        }

        $context = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query([
                    'secret'   => $secretKey,
                    'response' => $token,
                    'remoteip' => $clientIp,
                ]),
                'timeout' => self::TIMEOUT_SECONDS,
            ],
        ]);

        $result = @file_get_contents(self::VERIFY_URL, false, $context);

        if ($result === false) {
            error_log('RecaptchaValidator::validate - Could not reach Google siteverify endpoint.');
            return false;
        }

        $data = json_decode($result, true);

        return isset($data['success']) && $data['success'] === true;
    }
}
