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
 * @package BdeLive\Services
 */
class Mailer
{
    /**
     * Sender email address
     *
     * @var string
     */
    private string $from_email = FROM_EMAIL;

    /**
     * Sender display name
     *
     * @var string
     */
    private string $from_name = 'BDELive';

    /**
     * Send a password reset email
     *
     * Sends an email with a password reset token in plain text format.
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
            $mail->Subject = 'Reinitialisation de votre mot de passe - BDE Inform\'Aix';
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
     * The email is sent in plain text format.
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
            $mail->Subject = 'Verification de votre adresse email - BDE Inform\'Aix';

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
            $mail->Subject = 'Alerte de securite - BDE Inform\'Aix';
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
            $mail->Subject = 'Code de verification - Modification d\'email - BDE Inform\'Aix';
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
            $mail->Subject = 'Code de verification - Modification du mot de passe - BDE Inform\'Aix';
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
     * Sends an email inviting someone to join a team for a group event.
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
            $mail->Subject = "Invitation a rejoindre le Groupe {$teamNumber} - {$eventName} - BDE Inform'Aix";

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
            $mail->Subject = 'Groupe confirme - ' . $eventName . ' - BDE Inform\'Aix';
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
     * Generate plain text email content for password reset
     *
     * @param string $name Recipient name
     * @param string $token Password reset token
     * @return string Email content in plain text
     */
    private function getPasswordResetEmailText(string $name, string $token): string
    {
        return <<<TEXT
BDE INFORM'AIX
Reinitialisation de mot de passe
================================

Bonjour {$name},

Vous avez demande la reinitialisation de votre mot de passe.

VOTRE CODE DE VERIFICATION :
{$token}

Ce code est valable pendant 3 heures.

COMMENT L'UTILISER ?
1. Retournez sur la page de verification
2. Saisissez ce code
3. Definissez votre nouveau mot de passe

IMPORTANT : Si vous n'avez pas demande cette reinitialisation,
ignorez cet email. Votre mot de passe actuel reste inchange.

Cordialement,
L'equipe du BDE Inform'Aix

---
(c) 2025 BDE Inform'Aix - Tous droits reserves
Cet email a ete envoye automatiquement
TEXT;
    }

    /**
     * Generate plain text email content for email verification
     *
     * @param string $name Recipient name
     * @param string $verifyUrl Verification URL with token
     * @return string Email content in plain text
     */
    private function getVerificationEmailText(string $name, string $verifyUrl): string
    {
        return <<<TEXT
BDE INFORM'AIX
Verification de votre adresse email
====================================

Bonjour {$name},

Merci de vous etre inscrit sur le site du BDE Inform'Aix !

Pour activer votre compte, veuillez cliquer sur le lien ci-dessous :

{$verifyUrl}

Ce lien est valable de maniere permanente jusqu'a verification.

IMPORTANT : Si vous n'avez pas cree de compte sur notre site,
ignorez cet email. Aucune action ne sera effectuee.

Cordialement,
L'equipe du BDE Inform'Aix

---
(c) 2025 BDE Inform'Aix - Tous droits reserves
Cet email a ete envoye automatiquement
TEXT;
    }

    /**
     * Generate plain text email content for security alert
     *
     * @param string $name Recipient name
     * @param string $alertType Type of alert (email_change or password_change)
     * @return string Email content in plain text
     */
    private function getSecurityAlertEmailText(string $name, string $alertType): string
    {
        $alertMessage = $alertType === 'email_change'
            ? "de modification de votre adresse email"
            : "de modification de votre mot de passe";

        return <<<TEXT
ALERTE DE SECURITE - BDE INFORM'AIX
===================================

Bonjour {$name},

Nous avons detecte plusieurs tentatives infructueuses {$alertMessage} sur votre compte.

MESURE DE SECURITE APPLIQUEE :
La modification des informations de confidentialite a ete temporairement
bloquee sur votre compte pour une duree de 30 minutes.

Si vous etes a l'origine de ces tentatives, vous pourrez reessayer
apres la fin du blocage.

IMPORTANT :
Si vous n'etes pas a l'origine de ces tentatives, nous vous recommandons
de changer votre mot de passe des que possible.

Cordialement,
L'equipe du BDE Inform'Aix

---
Cet email a ete envoye automatiquement
TEXT;
    }

    /**
     * Generate plain text email content for email verification code
     *
     * @param string $name Recipient name
     * @param string $code The 6-digit verification code
     * @return string Email content in plain text
     */
    private function getEmailVerificationCodeText(string $name, string $code): string
    {
        return <<<TEXT
VERIFICATION D'EMAIL - BDE INFORM'AIX
Confirmez votre nouvelle adresse
=================================

Bonjour {$name},

Vous avez demande a modifier votre adresse email sur votre compte BDE Inform'Aix.

VOTRE CODE DE VERIFICATION :
{$code}

Ce code expire dans 10 minutes.

Saisissez-le sur la page de modification pour confirmer votre nouvelle
adresse email.

IMPORTANT :
Si vous n'avez pas demande cette modification, ignorez cet email.
Votre adresse email actuelle reste inchangee.

Cordialement,
L'equipe du BDE Inform'Aix

---
Cet email a ete envoye automatiquement
TEXT;
    }

    /**
     * Generate plain text email content for password change verification code
     *
     * @param string $name Recipient name
     * @param string $code The 6-digit verification code
     * @return string Email content in plain text
     */
    private function getPasswordChangeCodeEmailText(string $name, string $code): string
    {
        return <<<TEXT
CODE DE VERIFICATION - BDE INFORM'AIX
Modification du mot de passe
=============================

Bonjour {$name},

Vous avez demande a modifier votre mot de passe.

VOTRE CODE DE VERIFICATION :
{$code}

Ce code expire dans 10 minutes.

Saisissez-le sur la page de modification pour confirmer le changement de mot de passe.

IMPORTANT : Si vous n'avez pas demande cette modification, ignorez cet email. Votre mot de passe actuel reste inchange.

Cordialement,
L'equipe du BDE Inform'Aix

---
Cet email a ete envoye automatiquement
TEXT;
    }

    /**
     * Generate plain text email content for team invitation
     *
     * @param string $name Recipient name
     * @param string $eventName Event name
     * @param string $creatorName Team creator name
     * @param int $teamNumber Team number
     * @param int $teamSize Team size
     * @param string $validationUrl Validation URL
     * @return string Email content in plain text
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

{$creatorName} vous invite a rejoindre son groupe pour l'evenement :

EVENEMENT : {$eventName}
GROUPE : {$teamNumber}
NOMBRE DE MEMBRES : {$teamSize}

VOIR L'INVITATION ET REPONDRE :
{$validationUrl}

COMMENT CA FONCTIONNE ?
En cliquant sur le lien ci-dessus, vous pourrez voir les details du groupe
et choisir d'accepter ou de refuser l'invitation. Le groupe sera valide
uniquement lorsque tous les membres auront confirme leur participation.

IMPORTANT :
Si vous refusez l'invitation, le groupe entier sera annule. Les autres membres devront reformer un nouveau groupe.

Cordialement,
L'equipe du BDE Inform'Aix

---
Cet email a ete envoye automatiquement
TEXT;
    }

    /**
     * Generate plain text email content for team confirmed notification
     *
     * @param string $name Creator's name
     * @param string $eventName Event name
     * @param int $teamNumber Team number
     * @return string Email content in plain text
     */
    private function getTeamConfirmedEmailText(string $name, string $eventName, int $teamNumber): string
    {
        return <<<TEXT
GROUPE {$teamNumber} CONFIRME - BDE INFORM'AIX
==============================================

Bonjour {$name},

Bonne nouvelle ! Tous les membres de votre groupe ont confirme leur participation.

VOTRE GROUPE EST MAINTENANT INSCRIT A :
{$eventName}

Rendez-vous le jour de l'evenement !

Cordialement,
L'equipe du BDE Inform'Aix

---
Cet email a ete envoye automatiquement
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
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
    }
}
