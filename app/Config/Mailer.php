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
     *
     * Sends an email with a password reset token in HTML format with a plain text fallback.
     *
     * @param string $to_email Recipient email address
     * @param string $to_name Recipient name
     * @param string $token Password reset token
     * @return bool True if email sent successfully, false otherwise
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

            // HTML email with UTF-8 encoding
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'Réinitialisation de votre mot de passe - BDE Inform\'Aix';
            $mail->Body = $this->getPasswordResetEmailHTML($to_name, $token);
            $mail->AltBody = $this->getEmailTextVersion($to_name, $token);

            $mail->send();
            error_log("Mailer::sendEmail - Email sent successfully to: " . $to_email);
            return true;
        } catch (PHPMailerException $e) {
            error_log("Mailer::sendEmail (PHPMailer) - " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("Mailer::sendEmail - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a verification email
     *
     * Sends an email with a verification link to activate the user's account.
     * The email is sent in HTML format with a plain text fallback.
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

            // HTML email with UTF-8 encoding
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'Vérification de votre adresse email - BDE Inform\'Aix';

            // Construire l'URL de vérification
            $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') .
                       '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
            $verifyUrl = rtrim($baseUrl, '/') . '/index.php?page=verify_email&token=' . urlencode($token);

            $mail->Body = $this->getVerificationEmailHTML($to_name, $verifyUrl);
            $mail->AltBody = $this->getVerificationEmailText($to_name, $verifyUrl);

            $mail->send();
            error_log("Mailer::sendVerificationEmail - Email sent successfully to: " . $to_email);
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
     * Generate HTML email content for password reset
     *
     * @param string $name Recipient name
     * @param string $token Password reset token
     * @return string Email content in HTML format
     */
    private function getPasswordResetEmailHTML(string $name, string $token): string
    {
        $escapedName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $escapedToken = htmlspecialchars($token, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Réinitialisation de votre mot de passe</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f5f7fa; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="width: 100%; border-collapse: collapse; background-color: #f5f7fa; padding: 40px 20px;">
        <tr>
            <td align="center" style="padding: 0;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="width: 100%; max-width: 600px; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); overflow: hidden;">
                    <!-- Header avec dégradé professionnel -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 50px 40px; text-align: center;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="width: 100%;">
                                <tr>
                                    <td style="padding-bottom: 15px;">
                                        <h1 style="margin: 0; color: #ffffff; font-size: 32px; font-weight: 700; letter-spacing: -0.5px; line-height: 1.2;">BDE INFORM'AIX</h1>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <p style="margin: 0; color: rgba(255,255,255,0.95); font-size: 18px; font-weight: 400; letter-spacing: 0.3px;">Réinitialisation de mot de passe</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <!-- Contenu principal -->
                    <tr>
                        <td style="padding: 50px 40px;">
                            <!-- Salutation -->
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="width: 100%; margin-bottom: 30px;">
                                <tr>
                                    <td>
                                        <p style="margin: 0 0 15px 0; color: #2d3748; font-size: 18px; line-height: 1.6; font-weight: 400;">
                                            Bonjour <strong style="color: #1a202c; font-weight: 600;">{$escapedName}</strong>,
                                        </p>
                                        <p style="margin: 0; color: #4a5568; font-size: 16px; line-height: 1.7;">
                                            Vous avez demandé la réinitialisation de votre mot de passe. Utilisez le code ci-dessous pour procéder à la réinitialisation.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Code de vérification - Simple et facile à copier -->
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="width: 100%; margin: 35px 0;">
                                <tr>
                                    <td align="center" style="padding: 30px; background-color: #ffffff; border: 3px solid #667eea; border-radius: 12px;">
                                        <p style="margin: 0 0 20px 0; color: #2d3748; font-size: 18px; font-weight: 700; text-align: center;">
                                            📋 Votre code de vérification
                                        </p>
                                        
                                        <!-- Code complet - Très visible et sélectionnable -->
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="width: 100%; margin: 20px 0;">
                                            <tr>
                                                <td style="padding: 20px; background-color: #f7fafc; border: 2px solid #e2e8f0; border-radius: 8px;">
                                                    <p style="margin: 0; color: #1a202c; font-size: 14px; font-weight: 700; font-family: 'Courier New', 'Monaco', monospace; text-align: center; letter-spacing: 1px; word-break: break-all; line-height: 1.8;">
                                                        {$escapedToken}
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                        
                                        <p style="margin: 15px 0 0 0; color: #4a5568; font-size: 14px; text-align: center; line-height: 1.6;">
                                            Copier le code
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Instructions étape par étape -->
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="width: 100%; margin: 35px 0;">
                                <tr>
                                    <td style="padding: 25px; background-color: #ebf8ff; border-left: 5px solid #3182ce; border-radius: 8px;">
                                        <p style="margin: 0 0 15px 0; color: #2c5282; font-size: 15px; font-weight: 600; display: flex; align-items: center;">
                                            <span style="display: inline-block; width: 24px; height: 24px; background-color: #3182ce; color: #ffffff; border-radius: 50%; text-align: center; line-height: 24px; margin-right: 10px; font-size: 14px;">📋</span>
                                            Comment utiliser ce code ?
                                        </p>
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="width: 100%;">
                                            <tr>
                                                <td style="padding: 8px 0; color: #2d3748; font-size: 15px; line-height: 1.8;">
                                                    <span style="display: inline-block; width: 28px; height: 28px; background-color: #3182ce; color: #ffffff; border-radius: 50%; text-align: center; line-height: 28px; font-weight: 600; font-size: 13px; margin-right: 12px; vertical-align: middle;">1</span>
                                                    Retournez sur la page de vérification
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #2d3748; font-size: 15px; line-height: 1.8;">
                                                    <span style="display: inline-block; width: 28px; height: 28px; background-color: #3182ce; color: #ffffff; border-radius: 50%; text-align: center; line-height: 28px; font-weight: 600; font-size: 13px; margin-right: 12px; vertical-align: middle;">2</span>
                                                    Sélectionnez le code complet dans l'email, copiez-le (Ctrl+C ou Cmd+C), puis collez-le dans le champ de vérification
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #2d3748; font-size: 15px; line-height: 1.8;">
                                                    <span style="display: inline-block; width: 28px; height: 28px; background-color: #3182ce; color: #ffffff; border-radius: 50%; text-align: center; line-height: 28px; font-weight: 600; font-size: 13px; margin-right: 12px; vertical-align: middle;">3</span>
                                                    Définissez votre nouveau mot de passe sécurisé
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Informations importantes -->
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="width: 100%; margin: 25px 0;">
                                <tr>
                                    <td style="padding: 20px; background-color: #fffbeb; border-left: 5px solid #f59e0b; border-radius: 8px;">
                                        <p style="margin: 0; color: #92400e; font-size: 14px; line-height: 1.7;">
                                            <strong style="display: block; margin-bottom: 5px; font-size: 15px;">⏰ Validité du code</strong>
                                            Ce code est valable pendant <strong style="color: #b45309;">3 heures</strong> à compter de la réception de cet email. Après ce délai, vous devrez en demander un nouveau.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Avertissement sécurité -->
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="width: 100%; margin: 25px 0 0 0;">
                                <tr>
                                    <td style="padding: 20px; background-color: #fef2f2; border-left: 5px solid #ef4444; border-radius: 8px;">
                                        <p style="margin: 0; color: #991b1b; font-size: 14px; line-height: 1.7;">
                                            <strong style="display: block; margin-bottom: 5px; font-size: 15px;">🔒 Sécurité</strong>
                                            Si vous n'avez pas demandé cette réinitialisation, ignorez cet email. Votre mot de passe actuel reste inchangé et votre compte est en sécurité.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <!-- Footer professionnel -->
                    <tr>
                        <td style="background: linear-gradient(to bottom, #f8f9fa 0%, #ffffff 100%); padding: 40px; text-align: center; border-top: 1px solid #e9ecef;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="width: 100%;">
                                <tr>
                                    <td style="padding-bottom: 20px;">
                                        <p style="margin: 0 0 8px 0; color: #4a5568; font-size: 16px; line-height: 1.6;">
                                            Cordialement,
                                        </p>
                                        <p style="margin: 0; color: #2d3748; font-size: 17px; font-weight: 600;">
                                            L'équipe du BDE Inform'Aix
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-top: 25px; border-top: 1px solid #e2e8f0;">
                                        <p style="margin: 0; color: #a0aec0; font-size: 13px; line-height: 1.6;">
                                            © 2025 BDE Inform'Aix - Tous droits réservés<br>
                                            <span style="color: #cbd5e0;">Cet email a été envoyé automatiquement, merci de ne pas y répondre.</span>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    /**
     * Generate plain text email content for password reset
     *
     * @param string $name Recipient name
     * @param string $token Password reset token
     * @return string Email content in plain text
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

    /**
     * Generate HTML email content for email verification
     *
     * @param string $name Recipient name
     * @param string $verifyUrl Verification URL with token
     * @return string Email content in HTML format
     */
    private function getVerificationEmailHTML(string $name, string $verifyUrl): string
    {
        $escapedName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $escapedUrl = htmlspecialchars($verifyUrl, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Vérification de votre adresse email</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f5f7fa; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="width: 100%; border-collapse: collapse; background-color: #f5f7fa; padding: 40px 20px;">
        <tr>
            <td align="center" style="padding: 0;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="width: 100%; max-width: 600px; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); overflow: hidden;">
                    <!-- Header avec dégradé professionnel -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 50px 40px; text-align: center;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="width: 100%;">
                                <tr>
                                    <td style="padding-bottom: 15px;">
                                        <h1 style="margin: 0; color: #ffffff; font-size: 32px; font-weight: 700; letter-spacing: -0.5px; line-height: 1.2;">BDE INFORM'AIX</h1>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <p style="margin: 0; color: rgba(255,255,255,0.95); font-size: 18px; font-weight: 400; letter-spacing: 0.3px;">Vérification de votre adresse email</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <!-- Contenu principal -->
                    <tr>
                        <td style="padding: 50px 40px;">
                            <!-- Salutation -->
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="width: 100%; margin-bottom: 30px;">
                                <tr>
                                    <td>
                                        <p style="margin: 0 0 15px 0; color: #2d3748; font-size: 18px; line-height: 1.6; font-weight: 400;">
                                            Bonjour <strong style="color: #1a202c; font-weight: 600;">{$escapedName}</strong>,
                                        </p>
                                        <p style="margin: 0 0 12px 0; color: #4a5568; font-size: 16px; line-height: 1.7;">
                                            Merci de vous être inscrit sur le site du <strong style="color: #2d3748;">BDE Inform'Aix</strong> !
                                        </p>
                                        <p style="margin: 0; color: #4a5568; font-size: 16px; line-height: 1.7;">
                                            Pour activer votre compte et commencer à utiliser nos services, veuillez vérifier votre adresse email en cliquant sur le bouton ci-dessous.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Bouton CTA professionnel -->
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="width: 100%; margin: 40px 0;">
                                <tr>
                                    <td align="center" style="padding: 0;">
                                        <a href="{$escapedUrl}" style="display: inline-block; padding: 18px 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; text-decoration: none; border-radius: 10px; font-weight: 600; font-size: 17px; letter-spacing: 0.3px; box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4); transition: all 0.3s ease;">
                                            ✓ Vérifier mon email
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Lien alternatif -->
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="width: 100%; margin: 30px 0;">
                                <tr>
                                    <td align="center" style="padding: 20px; background-color: #f7fafc; border-radius: 8px;">
                                        <p style="margin: 0 0 10px 0; color: #718096; font-size: 13px; font-weight: 500;">
                                            Le bouton ne fonctionne pas ?
                                        </p>
                                        <p style="margin: 0; color: #4a5568; font-size: 13px; line-height: 1.6; word-break: break-all;">
                                            Copiez et collez ce lien dans votre navigateur :<br>
                                            <a href="{$escapedUrl}" style="color: #667eea; text-decoration: underline; font-size: 13px;">{$escapedUrl}</a>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Informations -->
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="width: 100%; margin: 30px 0;">
                                <tr>
                                    <td style="padding: 20px; background-color: #ebf8ff; border-left: 5px solid #667eea; border-radius: 8px;">
                                        <p style="margin: 0; color: #2c5282; font-size: 14px; line-height: 1.7;">
                                            <strong style="display: block; margin-bottom: 5px; font-size: 15px; color: #1e40af;">ℹ️ Information importante</strong>
                                            Ce lien de vérification est valable de manière permanente jusqu'à ce que vous ayez vérifié votre adresse email. Une fois vérifiée, vous pourrez accéder à tous les services du BDE Inform'Aix.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Avertissement sécurité -->
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="width: 100%; margin: 25px 0 0 0;">
                                <tr>
                                    <td style="padding: 20px; background-color: #fffbeb; border-left: 5px solid #f59e0b; border-radius: 8px;">
                                        <p style="margin: 0; color: #92400e; font-size: 14px; line-height: 1.7;">
                                            <strong style="display: block; margin-bottom: 5px; font-size: 15px; color: #b45309;">🔒 Sécurité</strong>
                                            Si vous n'avez pas créé de compte sur notre site, ignorez cet email. Aucune action ne sera effectuée et votre adresse email ne sera pas utilisée.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <!-- Footer professionnel -->
                    <tr>
                        <td style="background: linear-gradient(to bottom, #f8f9fa 0%, #ffffff 100%); padding: 40px; text-align: center; border-top: 1px solid #e9ecef;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="width: 100%;">
                                <tr>
                                    <td style="padding-bottom: 20px;">
                                        <p style="margin: 0 0 8px 0; color: #4a5568; font-size: 16px; line-height: 1.6;">
                                            Cordialement,
                                        </p>
                                        <p style="margin: 0; color: #2d3748; font-size: 17px; font-weight: 600;">
                                            L'équipe du BDE Inform'Aix
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-top: 25px; border-top: 1px solid #e2e8f0;">
                                        <p style="margin: 0; color: #a0aec0; font-size: 13px; line-height: 1.6;">
                                            © 2025 BDE Inform'Aix - Tous droits réservés<br>
                                            <span style="color: #cbd5e0;">Cet email a été envoyé automatiquement, merci de ne pas y répondre.</span>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
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
Vérification de votre adresse email

Bonjour {$name},

Merci de vous être inscrit sur le site du BDE Inform'Aix !

Pour activer votre compte, veuillez cliquer sur le lien ci-dessous :

{$verifyUrl}

Ce lien est valable de manière permanente jusqu'à vérification.

IMPORTANT : Si vous n'avez pas créé de compte sur notre site,
ignorez cet email. Aucune action ne sera effectuée.

Cordialement,
L'équipe du BDE Inform'Aix

(c) 2025 BDE Inform'Aix - Tous droits réservés
Cet email a été envoyé automatiquement
TEXT;
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

            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host = 'smtp-bdelivesae.alwaysdata.net';
            $mail->SMTPAuth = true;
            $mail->Username = 'bdelivesae@alwaysdata.net';
            $mail->Password = 'bdelive+6';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom($this->from_email, $this->from_name);
            $mail->addAddress($to_email, $to_name);

            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = '⚠️ Alerte de sécurité - BDE Inform\'Aix';
            $mail->Body = $this->getSecurityAlertEmailHTML($to_name, $alertType);
            $mail->AltBody = $this->getSecurityAlertEmailText($to_name, $alertType);

            $mail->send();
            error_log("Mailer::sendSecurityAlertEmail - Security alert sent to: " . $to_email);
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

            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host = 'smtp-bdelivesae.alwaysdata.net';
            $mail->SMTPAuth = true;
            $mail->Username = 'bdelivesae@alwaysdata.net';
            $mail->Password = 'bdelive+6';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom($this->from_email, $this->from_name);
            $mail->addAddress($to_email, $to_name);

            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'Code de vérification - Modification d\'email - BDE Inform\'Aix';
            $mail->Body = $this->getEmailVerificationCodeHTML($to_name, $code);
            $mail->AltBody = $this->getEmailVerificationCodeText($to_name, $code);

            $mail->send();
            error_log("Mailer::sendEmailVerificationCodeEmail - Code sent to: " . $to_email);
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

            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host = 'smtp-bdelivesae.alwaysdata.net';
            $mail->SMTPAuth = true;
            $mail->Username = 'bdelivesae@alwaysdata.net';
            $mail->Password = 'bdelive+6';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom($this->from_email, $this->from_name);
            $mail->addAddress($to_email, $to_name);

            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'Code de vérification - Modification du mot de passe - BDE Inform\'Aix';
            $mail->Body = $this->getPasswordChangeCodeEmailHTML($to_name, $code);
            $mail->AltBody = $this->getPasswordChangeCodeEmailText($to_name, $code);

            $mail->send();
            error_log("Mailer::sendPasswordChangeCodeEmail - Code sent to: " . $to_email);
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
     * Generate HTML email content for email verification code
     *
     * @param string $name Recipient name
     * @param string $code The 6-digit verification code
     * @return string Email content in HTML format
     */
    private function getEmailVerificationCodeHTML(string $name, string $code): string
    {
        $escapedName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $escapedCode = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification de votre nouvelle adresse email</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #f5f7fa;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="width: 100%; background-color: #f5f7fa; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="width: 100%; max-width: 600px; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); overflow: hidden;">
                    <tr>
                        <td style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); padding: 40px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px;">📧 Vérification d'email</h1>
                            <p style="margin: 10px 0 0 0; color: rgba(255,255,255,0.9); font-size: 16px;">Confirmez votre nouvelle adresse</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px;">
                            <p style="margin: 0 0 20px 0; color: #2d3748; font-size: 18px;">
                                Bonjour <strong>{$escapedName}</strong>,
                            </p>
                            <p style="margin: 0 0 25px 0; color: #4a5568; font-size: 16px; line-height: 1.7;">
                                Vous avez demandé à modifier votre adresse email sur votre compte BDE Inform'Aix. Pour confirmer que cette adresse email vous appartient, veuillez saisir le code ci-dessous.
                            </p>
                            <div style="text-align: center; margin: 30px 0;">
                                <div style="display: inline-block; padding: 25px 50px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border-radius: 12px;">
                                    <span style="font-size: 36px; font-weight: bold; color: #ffffff; letter-spacing: 8px; font-family: 'Courier New', monospace;">{$escapedCode}</span>
                                </div>
                            </div>
                            <div style="padding: 20px; background-color: #e6fffa; border-left: 5px solid #28a745; border-radius: 8px; margin: 25px 0;">
                                <p style="margin: 0; color: #276749; font-size: 14px; line-height: 1.7;">
                                    <strong>⏰ Ce code expire dans 10 minutes.</strong><br>
                                    Saisissez-le sur la page de modification pour confirmer votre nouvelle adresse email.
                                </p>
                            </div>
                            <div style="padding: 20px; background-color: #fef2f2; border-left: 5px solid #ef4444; border-radius: 8px;">
                                <p style="margin: 0; color: #991b1b; font-size: 14px; line-height: 1.7;">
                                    <strong>🔒 Sécurité :</strong><br>
                                    Si vous n'avez pas demandé cette modification, ignorez cet email. Votre adresse email actuelle reste inchangée.
                                </p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 30px; text-align: center; border-top: 1px solid #e9ecef;">
                            <p style="margin: 0; color: #6c757d; font-size: 14px;">
                                L'équipe du BDE Inform'Aix<br>
                                <span style="font-size: 12px; color: #adb5bd;">Cet email a été envoyé automatiquement</span>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
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
📧 VÉRIFICATION D'EMAIL - BDE INFORM'AIX
Confirmez votre nouvelle adresse

Bonjour {$name},

Vous avez demandé à modifier votre adresse email sur votre compte BDE Inform'Aix.

VOTRE CODE DE VÉRIFICATION :
{$code}

Ce code expire dans 10 minutes.

Saisissez-le sur la page de modification pour confirmer votre nouvelle adresse email.

IMPORTANT : Si vous n'avez pas demandé cette modification, ignorez cet email. Votre adresse email actuelle reste inchangée.

Cordialement,
L'équipe du BDE Inform'Aix

Cet email a été envoyé automatiquement
TEXT;
    }

    /**
     * Generate HTML email content for security alert
     *
     * @param string $name Recipient name
     * @param string $alertType Type of alert
     * @return string Email content in HTML format
     */
    private function getSecurityAlertEmailHTML(string $name, string $alertType): string
    {
        $escapedName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $alertMessage = $alertType === 'email_change'
            ? "de modification de votre adresse email"
            : "de modification de votre mot de passe";

        return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerte de sécurité</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #f5f7fa;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="width: 100%; background-color: #f5f7fa; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="width: 100%; max-width: 600px; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); overflow: hidden;">
                    <tr>
                        <td style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); padding: 40px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px;">⚠️ ALERTE DE SÉCURITÉ</h1>
                            <p style="margin: 10px 0 0 0; color: rgba(255,255,255,0.9); font-size: 16px;">BDE Inform'Aix</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px;">
                            <p style="margin: 0 0 20px 0; color: #2d3748; font-size: 18px;">
                                Bonjour <strong>{$escapedName}</strong>,
                            </p>
                            <p style="margin: 0 0 25px 0; color: #4a5568; font-size: 16px; line-height: 1.7;">
                                Nous avons détecté plusieurs tentatives infructueuses {$alertMessage} sur votre compte.
                            </p>
                            <div style="padding: 20px; background-color: #fef2f2; border-left: 5px solid #dc3545; border-radius: 8px; margin: 25px 0;">
                                <p style="margin: 0; color: #991b1b; font-size: 15px; line-height: 1.7;">
                                    <strong>🔒 Mesure de sécurité appliquée :</strong><br>
                                    La modification des informations de confidentialité a été temporairement bloquée sur votre compte pour une durée de <strong>30 minutes</strong>.
                                </p>
                            </div>
                            <p style="margin: 25px 0; color: #4a5568; font-size: 16px; line-height: 1.7;">
                                Si vous êtes à l'origine de ces tentatives, vous pourrez réessayer après la fin du blocage.
                            </p>
                            <div style="padding: 20px; background-color: #fffbeb; border-left: 5px solid #f59e0b; border-radius: 8px;">
                                <p style="margin: 0; color: #92400e; font-size: 14px; line-height: 1.7;">
                                    <strong>⚠️ Si vous n'êtes pas à l'origine de ces tentatives :</strong><br>
                                    Nous vous recommandons de changer votre mot de passe dès que le délai est passé et de vérifier l'activité de votre compte. Si besoin, vous pouvez rentrer en contact avec un administrateur
                                </p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 30px; text-align: center; border-top: 1px solid #e9ecef;">
                            <p style="margin: 0; color: #6c757d; font-size: 14px;">
                                L'équipe du BDE Inform'Aix<br>
                                <span style="font-size: 12px; color: #adb5bd;">Cet email a été envoyé automatiquement</span>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    /**
     * Generate plain text email content for security alert
     *
     * @param string $name Recipient name
     * @param string $alertType Type of alert
     * @return string Email content in plain text
     */
    private function getSecurityAlertEmailText(string $name, string $alertType): string
    {
        $alertMessage = $alertType === 'email_change'
            ? "de modification de votre adresse email"
            : "de modification de votre mot de passe";

        return <<<TEXT
⚠️ ALERTE DE SÉCURITÉ - BDE INFORM'AIX

Bonjour {$name},

Nous avons détecté plusieurs tentatives infructueuses {$alertMessage} sur votre compte.

MESURE DE SÉCURITÉ APPLIQUÉE :
La modification des informations de confidentialité a été temporairement bloquée sur votre compte pour une durée de 30 minutes.

Si vous êtes à l'origine de ces tentatives, vous pourrez réessayer après la fin du blocage.

IMPORTANT : Si vous n'êtes pas à l'origine de ces tentatives, nous vous recommandons de changer votre mot de passe dès que possible.

Cordialement,
L'équipe du BDE Inform'Aix

Cet email a été envoyé automatiquement
TEXT;
    }

    /**
     * Generate HTML email content for password change verification code
     *
     * @param string $name Recipient name
     * @param string $code The 6-digit verification code
     * @return string Email content in HTML format
     */
    private function getPasswordChangeCodeEmailHTML(string $name, string $code): string
    {
        $escapedName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $escapedCode = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Code de vérification</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #f5f7fa;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="width: 100%; background-color: #f5f7fa; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="width: 100%; max-width: 600px; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); overflow: hidden;">
                    <tr>
                        <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px;">🔐 Code de vérification</h1>
                            <p style="margin: 10px 0 0 0; color: rgba(255,255,255,0.9); font-size: 16px;">Modification du mot de passe</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px;">
                            <p style="margin: 0 0 20px 0; color: #2d3748; font-size: 18px;">
                                Bonjour <strong>{$escapedName}</strong>,
                            </p>
                            <p style="margin: 0 0 25px 0; color: #4a5568; font-size: 16px; line-height: 1.7;">
                                Vous avez demandé à modifier votre mot de passe. Utilisez le code ci-dessous pour confirmer cette modification.
                            </p>
                            <div style="text-align: center; margin: 30px 0;">
                                <div style="display: inline-block; padding: 25px 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px;">
                                    <span style="font-size: 36px; font-weight: bold; color: #ffffff; letter-spacing: 8px; font-family: 'Courier New', monospace;">{$escapedCode}</span>
                                </div>
                            </div>
                            <div style="padding: 20px; background-color: #ebf8ff; border-left: 5px solid #3182ce; border-radius: 8px; margin: 25px 0;">
                                <p style="margin: 0; color: #2c5282; font-size: 14px; line-height: 1.7;">
                                    <strong>⏰ Ce code expire dans 10 minutes.</strong><br>
                                    Saisissez-le sur la page de modification pour confirmer le changement de mot de passe.
                                </p>
                            </div>
                            <div style="padding: 20px; background-color: #fef2f2; border-left: 5px solid #ef4444; border-radius: 8px;">
                                <p style="margin: 0; color: #991b1b; font-size: 14px; line-height: 1.7;">
                                    <strong>🔒 Sécurité :</strong><br>
                                    Si vous n'avez pas demandé cette modification, ignorez cet email. Votre mot de passe actuel reste inchangé.
                                </p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 30px; text-align: center; border-top: 1px solid #e9ecef;">
                            <p style="margin: 0; color: #6c757d; font-size: 14px;">
                                L'équipe du BDE Inform'Aix<br>
                                <span style="font-size: 12px; color: #adb5bd;">Cet email a été envoyé automatiquement</span>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
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
🔐 CODE DE VÉRIFICATION - BDE INFORM'AIX
Modification du mot de passe

Bonjour {$name},

Vous avez demandé à modifier votre mot de passe.

VOTRE CODE DE VÉRIFICATION :
{$code}

Ce code expire dans 10 minutes.

Saisissez-le sur la page de modification pour confirmer le changement de mot de passe.

IMPORTANT : Si vous n'avez pas demandé cette modification, ignorez cet email. Votre mot de passe actuel reste inchangé.

Cordialement,
L'équipe du BDE Inform'Aix

Cet email a été envoyé automatiquement
TEXT;
    }
}
