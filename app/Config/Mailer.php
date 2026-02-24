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
 * All emails are sent in plain text format only.
 *
 * @author BDELIVE - Group 8
 * @package BdeLive\Services
 * @version 2.3.1
 */
class Mailer
{
    /**
     * Sender email address
     *
     * @var string
     */
    private string $from_email;

    /**
     * Sender display name
     *
     * @var string
     */
    private string $from_name = 'BDELive';

    public function __construct()
    {
        define('FROM_EMAIL', $_ENV['FROM_EMAIL']);
        $this->from_email = $_ENV['FROM_EMAIL'];
    }

    /**
     * Send a password reset email
     *
     * Sends an email with a password reset token.
     *
     * @param string $to_email Recipient email address
     * @param string $to_name Recipient name
     * @param string $token Password reset token
     * @return bool True if email sent successfully, false otherwise
     */
    public function sendPasswordResetEmail(string $to_email, string $to_name, string $token): bool
    {
        try {
            $mail = new PHPMailer(true);

            $this->smtpConfiguration($mail);

            $mail->setFrom($this->from_email, $this->from_name);
            $mail->addAddress($to_email, $to_name);

            $mail->isHTML(false);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'Réinitialisation de votre mot de passe - BDE Inform\'Aix';
            $mail->Body = $this->getPasswordResetEmailText($to_name, $token);

            $mail->send();
            return true;
        } catch (PHPMailerException $e) {
            error_log("Mailer::sendPasswordResetEmail (PHPMailer) - " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("Mailer::sendPasswordResetEmail - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a verification email
     *
     * Sends an email with a verification link to activate the user's account.
     *
     *
     * @param string $to_email Recipient email address
     * @param string $to_name Recipient name
     * @param string $token Verification token
     * @return bool True if email sent successfully, false otherwise
     */
    public function sendVerificationEmail(string $to_email, string $to_name, string $token): bool
    {
        try {
            $mail = new PHPMailer(true);

            $this->smtpConfiguration($mail);

            $mail->setFrom($this->from_email, $this->from_name);
            $mail->addAddress($to_email, $to_name);

            $mail->isHTML(false);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'Vérification de votre adresse email - BDE Inform\'Aix';

            $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') .
                '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
            $verifyUrl = rtrim($baseUrl, '/') . '/index.php?page=verify_email&token=' . urlencode($token);

            $mail->Body = $this->getVerificationEmailText($to_name, $verifyUrl);

            $mail->send();
            return true;
        } catch (PHPMailerException $e) {
            error_log("Mailer::sendVerificationEmail (PHPMailer) - " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("Mailer::sendVerificationEmail - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a security alert email for suspicious activity
     *
     * Notifies the user that a suspicious modification attempt was detected
     * and their account has been temporarily blocked.
     *
     * @param string $to_email Recipient email address
     * @param string $to_name Recipient name
     * @param string $alertType Type of alert (email_change, password_change)
     * @return bool True if email sent successfully, false otherwise
     */
    public function sendSecurityAlertEmail(string $to_email, string $to_name, string $alertType): bool
    {
        try {
            $mail = new PHPMailer(true);

            $this->smtpConfiguration($mail);

            $mail->setFrom($this->from_email, $this->from_name);
            $mail->addAddress($to_email, $to_name);

            $mail->isHTML(false);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'Alerte de sécurité - BDE Inform\'Aix';
            $mail->Body = $this->getSecurityAlertEmailText($to_name, $alertType);

            $mail->send();
            return true;
        } catch (PHPMailerException $e) {
            error_log("Mailer::sendSecurityAlertEmail (PHPMailer) - " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("Mailer::sendSecurityAlertEmail - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send an email verification code for email change
     *
     * Sends a 6-digit verification code to the NEW email address
     * to verify that the email exists and is accessible.
     *
     * @param string $to_email New email address to verify
     * @param string $to_name Recipient name
     * @param string $code The 6-digit verification code
     * @return bool True if email sent successfully, false otherwise
     */
    public function sendEmailVerificationCodeEmail(string $to_email, string $to_name, string $code): bool
    {
        try {
            $mail = new PHPMailer(true);

            $this->smtpConfiguration($mail);

            $mail->setFrom($this->from_email, $this->from_name);
            $mail->addAddress($to_email, $to_name);

            $mail->isHTML(false);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'Code de vérification - Modification d\'email - BDE Inform\'Aix';
            $mail->Body = $this->getEmailVerificationCodeText($to_name, $code);

            $mail->send();
            return true;
        } catch (PHPMailerException $e) {
            error_log("Mailer::sendEmailVerificationCodeEmail (PHPMailer) - " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("Mailer::sendEmailVerificationCodeEmail - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a password change verification code email
     *
     * Sends a 6-digit verification code for password change confirmation.
     *
     * @param string $to_email Recipient email address
     * @param string $to_name Recipient name
     * @param string $code The 6-digit verification code
     * @return bool True if email sent successfully, false otherwise
     */
    public function sendPasswordChangeCodeEmail(string $to_email, string $to_name, string $code): bool
    {
        try {
            $mail = new PHPMailer(true);

            $this->smtpConfiguration($mail);

            $mail->setFrom($this->from_email, $this->from_name);
            $mail->addAddress($to_email, $to_name);

            $mail->isHTML(false);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'Code de vérification - Modification du mot de passe - BDE Inform\'Aix';
            $mail->Body = $this->getPasswordChangeCodeEmailText($to_name, $code);

            $mail->send();
            return true;
        } catch (PHPMailerException $e) {
            error_log("Mailer::sendPasswordChangeCodeEmail (PHPMailer) - " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("Mailer::sendPasswordChangeCodeEmail - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a team invitation email for group registration
     *
     * Sends an email inviting someone to join a team for a group event, with a link to join the team.
     *
     * @param string $to_email Recipient email address
     * @param string $to_name Recipient name
     * @param string $token Validation token
     * @param string $eventName Name of the event
     * @param string $creatorName Name of the team creator
     * @param int $teamNumber Team number
     * @param int $teamSize Total team size
     * @return bool True if email sent successfully, false otherwise
     */
    public function sendTeamInvitationEmail(
        string $to_email,
        string $to_name,
        string $token,
        string $eventName,
        string $creatorName,
        int $teamNumber,
        int $teamSize
    ): bool {
        try {
            $mail = new PHPMailer(true);

            $this->smtpConfiguration($mail);

            $mail->setFrom($this->from_email, $this->from_name);
            $mail->addAddress($to_email, $to_name);

            $mail->isHTML(false);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = "Invitation à rejoindre le Groupe {$teamNumber} - {$eventName} - BDE Inform'Aix";

            $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') .
                '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
            $validationUrl = rtrim($baseUrl, '/') . '/index.php?page=validateTeamInvitation&token=' . urlencode($token);

            $mail->Body = $this->getTeamInvitationEmailText(
                $to_name,
                $eventName,
                $creatorName,
                $teamNumber,
                $teamSize,
                $validationUrl
            );

            $mail->send();
            error_log("Mailer::sendTeamInvitationEmail - Email sent to: " . $to_email);
            return true;
        } catch (PHPMailerException $e) {
            error_log("Mailer::sendTeamInvitationEmail (PHPMailer) - " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("Mailer::sendTeamInvitationEmail - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a notification email to the group creator when all members confirmed
     *
     * Notifies the creator that the team is now fully confirmed and registered
     * for the event.
     *
     * @param string $to_email Creator's email address
     * @param string $to_name Creator's name
     * @param string $eventName Name of the event
     * @param int $teamNumber Team/group number
     * @return bool True if email sent successfully, false otherwise
     */
    public function sendTeamConfirmedEmail(
        string $to_email,
        string $to_name,
        string $eventName,
        int $teamNumber
    ): bool {
        try {
            $mail = new PHPMailer(true);

            $this->smtpConfiguration($mail);

            $mail->setFrom($this->from_email, $this->from_name);
            $mail->addAddress($to_email, $to_name);

            $mail->isHTML(false);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'Groupe confirmé - ' . $eventName . ' - BDE Inform\'Aix';
            $mail->Body = $this->getTeamConfirmedEmailText($to_name, $eventName, $teamNumber);

            $mail->send();
            error_log("Mailer::sendTeamConfirmedEmail - Sent to: " . $to_email);
            return true;
        } catch (PHPMailerException $e) {
            error_log("Mailer::sendTeamConfirmedEmail (PHPMailer) - " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("Mailer::sendTeamConfirmedEmail - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate email content for password reset operations
     *
     * @param string $name Recipient name
     * @param string $token Password reset token
     * @return string Email content
     */
    private function getPasswordResetEmailText(string $name, string $token): string
    {
        return <<<TEXT
BDE INFORM'AIX
Réinitialisation de mot de passe
================================

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

---
(c) 2025 BDE Inform'Aix - Tous droits réservés
Cet email a été envoyé automatiquement
TEXT;
    }

    /**
     * Generate email content for email verification operations
     *
     * @param string $name Recipient name
     * @param string $verifyUrl Verification URL with token
     * @return string Email content
     */
    private function getVerificationEmailText(string $name, string $verifyUrl): string
    {
        return <<<TEXT
BDE INFORM'AIX
Vérification de votre adresse email
====================================

Bonjour {$name},

Merci de vous être inscrit sur le site du BDE Inform'Aix !

Pour activer votre compte, veuillez cliquer sur le lien ci-dessous :

{$verifyUrl}

Ce lien est valable de manière permanente jusqu'à vérification.

IMPORTANT : Si vous n'avez pas créé de compte sur notre site,
ignorez cet email. Aucune action ne sera effectuée.

Cordialement,
L'équipe du BDE Inform'Aix

---
(c) 2025 BDE Inform'Aix - Tous droits réservés
Cet email a été envoyé automatiquement
TEXT;
    }

    /**
     * Generate email content for security alert operations
     *
     * @param string $name Recipient name
     * @param string $alertType Type of alert (email_change or password_change)
     * @return string Email content
     */
    private function getSecurityAlertEmailText(string $name, string $alertType): string
    {
        $alertMessage = $alertType === 'email_change'
            ? "de modification de votre adresse email"
            : "de modification de votre mot de passe";

        return <<<TEXT
ALERTE DE SÉCURITÉ - BDE INFORM'AIX
===================================

Bonjour {$name},

Nous avons détecté plusieurs tentatives infructueuses {$alertMessage} sur votre compte.

MESURE DE SÉCURITÉ APPLIQUÉE :
La modification des informations de confidentialité a été temporairement
bloquée sur votre compte pour une durée de 30 minutes.

Si vous êtes à l'origine de ces tentatives, vous pourrez réessayer
après la fin du blocage.

IMPORTANT :
Si vous n'êtes pas à l'origine de ces tentatives, nous vous recommandons
de changer votre mot de passe dès que possible.

Cordialement,
L'équipe du BDE Inform'Aix

---
Cet email a été envoyé automatiquement
TEXT;
    }

    /**
     * Generate email content for email verification code operations
     *
     * @param string $name Recipient name
     * @param string $code The 6-digit verification code
     * @return string Email content
     */
    private function getEmailVerificationCodeText(string $name, string $code): string
    {
        return <<<TEXT
VÉRIFICATION D'EMAIL - BDE INFORM'AIX
Confirmez votre nouvelle adresse
=================================

Bonjour {$name},

Vous avez demandé à modifier votre adresse email sur votre compte BDE Inform'Aix.

VOTRE CODE DE VÉRIFICATION :
{$code}

Ce code expire dans 10 minutes.

Saisissez-le sur la page de modification pour confirmer votre nouvelle
adresse email.

IMPORTANT :
Si vous n'avez pas demandé cette modification, ignorez cet email.
Votre adresse email actuelle reste inchangée.

Cordialement,
L'équipe du BDE Inform'Aix

---
Cet email a été envoyé automatiquement
TEXT;
    }

    /**
     * Generate email content for password change verification code operations
     *
     * @param string $name Recipient name
     * @param string $code The 6-digit verification code
     * @return string Email content
     */
    private function getPasswordChangeCodeEmailText(string $name, string $code): string
    {
        return <<<TEXT
CODE DE VÉRIFICATION - BDE INFORM'AIX
Modification du mot de passe
=============================

Bonjour {$name},

Vous avez demandé à modifier votre mot de passe.

VOTRE CODE DE VÉRIFICATION :
{$code}

Ce code expire dans 10 minutes.

Saisissez-le sur la page de modification pour confirmer le changement de mot de passe.

IMPORTANT : Si vous n'avez pas demandé cette modification, ignorez cet email. Votre mot de passe actuel reste inchangé.

Cordialement,
L'équipe du BDE Inform'Aix

---
Cet email a été envoyé automatiquement
TEXT;
    }

    /**
     * Generate email content for team invitation operations
     *
     * @param string $name Recipient name
     * @param string $eventName Event name
     * @param string $creatorName Team creator name
     * @param int $teamNumber Team number
     * @param int $teamSize Team size
     * @param string $validationUrl Validation URL
     * @return string Email content
     */
    private function getTeamInvitationEmailText(
        string $name,
        string $eventName,
        string $creatorName,
        int $teamNumber,
        int $teamSize,
        string $validationUrl
    ): string {
        return <<<TEXT
INVITATION AU GROUPE {$teamNumber} - BDE INFORM'AIX
===================================================

Bonjour {$name},

{$creatorName} vous invite à rejoindre son groupe pour l'événement :

ÉVÉNEMENT : {$eventName}
GROUPE : {$teamNumber}
NOMBRE DE MEMBRES : {$teamSize}

VOIR L'INVITATION ET RÉPONDRE :
{$validationUrl}

COMMENT ÇA FONCTIONNE ?
En cliquant sur le lien ci-dessus, vous pourrez voir les détails du groupe
et choisir d'accepter ou de refuser l'invitation. Le groupe sera validé
uniquement lorsque tous les membres auront confirmé leur participation.

IMPORTANT :
Si vous refusez l'invitation, le groupe entier sera annulé. Les autres membres devront reformer un nouveau groupe.

Cordialement,
L'équipe du BDE Inform'Aix

---
Cet email a été envoyé automatiquement
TEXT;
    }

    /**
     * Generate email content for team confirmed notification operations
     *
     * @param string $name Creator's name
     * @param string $eventName Event name
     * @param int $teamNumber Team number
     * @return string Email content
     */
    private function getTeamConfirmedEmailText(string $name, string $eventName, int $teamNumber): string
    {
        return <<<TEXT
GROUPE {$teamNumber} CONFIRMÉ - BDE INFORM'AIX
==============================================

Bonjour {$name},

Bonne nouvelle ! Tous les membres de votre groupe ont confirmé leur participation.

VOTRE GROUPE EST MAINTENANT INSCRIT À :
{$eventName}

Rendez-vous le jour de l'événement !

Cordialement,
L'équipe du BDE Inform'Aix

---
Cet email a été envoyé automatiquement
TEXT;
    }

    /**
     * Configure SMTP settings for PHPMailer
     *
     * Sets up the SMTP connection parameters using AlwaysData server settings.
     *
     * @param PHPMailer $mail PHPMailer instance to configure
     * @return void
     */
    private function smtpConfiguration(PHPMailer $mail): void
    {
        $mail->isSMTP();
        $mail->Host = $_ENV['SMTP_HOST'] ?? '';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_USER'] ?? '';
        $mail->Password = $_ENV['SMTP_PASSWORD'] ?? '';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
    }
}
