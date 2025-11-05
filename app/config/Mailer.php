<?php

declare(strict_types=1);

namespace App\Config;

use Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/**
 * Email Service Provider
 *
 * Handles email sending functionality using PHPMailer library.
 * Configured to work with AlwaysData SMTP server for sending
 * password reset emails and other application notifications.
 *
 * @package BdeLive\Services
 */
class Mailer
{
    /**
     * Sender email address
     */
    private string $from_email = 'noreply@bdelivesae.alwaysdata.net';

    /**
     * Sender display name
     */
    private string $from_name = 'BDE Inform\'Aix';

    /**
     * Send a password reset email
     */
    public function sendPasswordResetEmail(string $to_email, string $to_name, string $token): bool
    {
        try {
            // ✅ plus besoin de require_once, Composer autoload s'en charge
            $mail = new PHPMailer(true);

            // SMTP Configuration for AlwaysData
            $mail->isSMTP();
            $mail->Host = 'smtp-bdelivesae.alwaysdata.net';
            $mail->SMTPAuth = true;
            $mail->Username = 'bdelivesae@alwaysdata.net';
            $mail->Password = 'bdelive+6';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Sender configuration
            $mail->setFrom($this->from_email, $this->from_name);
            $mail->addAddress($to_email, $to_name);

            // Plain text email
            $mail->isHTML(false);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'Réinitialisation de votre mot de passe - BDE Inform\'Aix';
            $mail->Body = $this->getEmailTextVersion($to_name, $token);

            $mail->send();
            error_log("Email envoyé avec succès à : " . $to_email);
            return true;

        } catch (PHPMailerException $e) {
            error_log("Erreur PHPMailer : " . $e->getMessage());
            return false;

        } catch (Exception $e) {
            error_log("Erreur envoi email : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate plain text email content for password reset
     */
    private function getEmailTextVersion(string $name, string $token): string
    {
        return <<<TEXT
BDE INFORM'AIX
Réinitialisation de mot de passe

Bonjour {$name},

Vous avez demandé la réinitialisation de votre mot de passe.

VOTRE CODE DE VÉRIFICATION :
{$token}

Ce code est valable pendant 3 heures.

COMMENT L'UTILISER ?
1. Retournez sur la page de vérification
2. Saisissez ce code
3. Définissez votre nouveau mot de passe

IMPORTANT : Si vous n'avez pas demandé cette réinitialisation,
ignorez cet email. Votre mot de passe actuel reste inchangé.

Cordialement,
L'équipe du BDE Inform'Aix

(c) 2025 BDE Inform'Aix - Tous droits réservés
Cet email a été envoyé automatiquement
TEXT;
    }
}
