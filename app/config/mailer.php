<?php

declare(strict_types=1);

// Charger PHPMailer
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';

/**
 * Email Service Provider
 * 
 * Handles email sending functionality using PHPMailer library.
 * Configured to work with AlwaysData SMTP server for sending
 * password reset emails and other application notifications.
 * 
 * @package BdeLive\Services
 * @author Mohamed-Amine Boudhib, Thomas Palot, Amin Helali, Willem Chetioui, Nasser Ahamed, Romain Cantor
 * @version 1.0.0
 */
class Mailer {
    /**
     * Sender email address
     * 
     * @var string
     */
    private string $from_email = 'noreply@bdelivesae.alwaysdata.net';
    
    /**
     * Sender display name
     * 
     * @var string
     */
    private string $from_name = 'BDE Inform\'Aix';
    
    /**
     * Send a password reset email
     * 
     * Sends an email containing a password reset token to the specified recipient.
     * The email is sent via SMTP using the AlwaysData mail server.
     * 
     * @param string $to_email Recipient's email address
     * @param string $to_name Recipient's full name
     * @param string $token The password reset token (64-character hex string)
     * @return bool True if email sent successfully, false otherwise
     */
    public function sendPasswordResetEmail(string $to_email, string $to_name, string $token): bool {
        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            
            // Configuration SMTP pour AlwaysData
            $mail->isSMTP();
            $mail->Host = 'smtp-bdelivesae.alwaysdata.net';
            $mail->SMTPAuth = true;
            $mail->Username = 'bdelivesae@alwaysdata.net';
            $mail->Password = 'bdelive+6';
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            
            // Configuration expéditeur
            $mail->setFrom($this->from_email, $this->from_name);
            $mail->addAddress($to_email, $to_name);
            
            // Contenu en texte brut uniquement
            $mail->isHTML(false);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'Réinitialisation de votre mot de passe - BDE Inform\'Aix';
            $mail->Body = $this->getEmailTextVersion($to_name, $token);
            
            $result = $mail->send();
            error_log("Email envoyé avec succès à : " . $to_email);
            return $result;
            
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            error_log("Erreur PHPMailer : " . $mail->ErrorInfo);
            return false;
        } catch (Exception $e) {
            error_log("Erreur envoi email : " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate plain text email content for password reset
     * 
     * Creates a formatted plain text email body containing the password reset
     * token and instructions for the user.
     * 
     * @param string $name Recipient's name to personalize the email
     * @param string $token The password reset token to include in the email
     * @return string The formatted email body text
     */
    private function getEmailTextVersion(string $name, string $token): string {
               return "BDE INFORM'AIX\n" .
               "Reinitialisation de mot de passe\n" .
               "\n\n" .
               "Bonjour " . $name . ",\n\n" .
               "Vous avez demande la reinitialisation de votre mot de passe.\n\n" .
               "VOTRE CODE DE VERIFICATION :\n" .
               $token . "\n\n" .
               "Ce code est valable pendant 3 heures.\n\n" .
               "COMMENT L'UTILISER ?\n" .
               "1. Retournez sur la page de verification\n" .
               "2. Saisissez ce code\n" .
               "3. Definissez votre nouveau mot de passe\n\n" .
               "IMPORTANT : Si vous n'avez pas demande cette reinitialisation,\n" .
               "ignorez cet email. Votre mot de passe actuel reste inchange.\n\n" .
               "Cordialement,\n" .
               "L'equipe du BDE Inform'Aix\n\n" .
               "\n\n" .
               "(c) 2025 BDE Inform'Aix - Tous droits reserves\n" .
               "Cet email a ete envoye automatiquement\n" .
               "
    }
}
