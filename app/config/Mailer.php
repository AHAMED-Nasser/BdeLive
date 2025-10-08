<?php

declare(strict_types=1);

// Charger PHPMailer
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';

class Mailer {
    private string $from_email = 'noreply@bdelivesae.alwaysdata.net';
    private string $from_name = 'BDE Inform\'Aix';
    
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
    
    private function getEmailTextVersion(string $name, string $token): string {
        return "========================================\n" .
               "BDE INFORM'AIX\n" .
               "Reinitialisation de mot de passe\n" .
               "========================================\n\n" .
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
               "========================================\n" .
               "(c) 2025 BDE Inform'Aix - Tous droits reserves\n" .
               "Cet email a ete envoye automatiquement\n" .
               "========================================";
    }
}
