<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/Database.php';

/**
 * Password Reset Model
 * 
 * Handles password reset functionality including token generation, validation,
 * and password updates. Manages the MDP_OUBLIES_TOKEN table for secure
 * password reset operations with time-limited tokens.
 * 
 * @package BdeLive
 * @author Mohamed-Amine Boudhib, Thomas Palot, Amin Helali, Willem Chetioui, Nasser Ahamed, Romain Cantor
 * @version 1.0.0
 */
class PasswordReset {
    /**
     * PDO database connection instance
     * 
     * @var PDO
     */
    private PDO $pdo;
    
    /**
     * Constructor - Initialize the PasswordReset model
     * 
     * Retrieves the database connection from the Database singleton.
     */
    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }
    
    /**
     * Get user information by email address
     * 
     * Retrieves basic user information (without password) for password reset purposes.
     * 
     * @param string $email The user's email address
     * @return array|false Array containing user data if found, false otherwise
     */
    public function getUserByEmail(string $email): array|false {
        try {
            $stmt = $this->pdo->prepare('SELECT utilisateur_id, nom, prenom, email FROM Utilisateur WHERE email = ?');
            $stmt->execute([$email]);
            $result = $stmt->fetch();
            
            return $result ?: false;
        } catch (PDOException $e) {
            error_log("Erreur getUserByEmail : " . $e->getMessage());
            return false;
        }
    }


    /**
     * Create a password reset token
     *
     * Generates a secure random token valid for 3 hours and stores it in the database.
     * The token is a 64-character hexadecimal string.
     *
     * @param int $utilisateur_id The ID of the user requesting password reset
     * @return string|false The generated token if successful, false otherwise
     *
     */
    public function createToken(int $utilisateur_id): string|false {
        try {
            $token = bin2hex(random_bytes(32));
            date_default_timezone_set('Europe/Paris');
            $expire_dans = date('Y-m-d H:i:s', strtotime('+3 hours'));
            
            $stmt = $this->pdo->prepare(
                'INSERT INTO MDP_OUBLIES_TOKEN (utilisateur_id, token, expire_dans) VALUES (?, ?, ?)'
            );
            $stmt->execute([$utilisateur_id, $token, $expire_dans]);
            
            return $token;
        } catch (PDOException $e) {
            error_log("Erreur createToken : " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verify a password reset token
     * 
     * Checks if a token is valid, not expired, and not already used.
     * Automatically deletes expired tokens.
     * 
     * @param string $token The token to verify
     * @return array Associative array with 'valid' (bool), and if valid: 'utilisateur_id' and 'token_id',
     *               or if invalid: 'message' (string) explaining why
     */
    public function verifyToken(string $token): array {
        try {
            $stmt = $this->pdo->prepare(
                'SELECT id, utilisateur_id, expire_dans, utilise FROM MDP_OUBLIES_TOKEN WHERE token = ?'
            );
            $stmt->execute([$token]);
            $result = $stmt->fetch();
            
            if (!$result) {
                return ['valid' => false, 'message' => 'Code invalide'];
            }
            
            if ($result['utilise'] == 1) {
                return ['valid' => false, 'message' => 'Ce code a déjà été utilisé'];
            }
            
            $expire_time = strtotime($result['expire_dans']);
            $current_time = time();
            
            if ($current_time > $expire_time) {
                $deleteStmt = $this->pdo->prepare('DELETE FROM MDP_OUBLIES_TOKEN WHERE id = ?');
                $deleteStmt->execute([$result['id']]);
                return ['valid' => false, 'message' => 'Ce code a expiré'];
            }
            
            return [
                'valid' => true, 
                'utilisateur_id' => $result['utilisateur_id'], 
                'token_id' => $result['id']
            ];
            
        } catch (PDOException $e) {
            error_log("Erreur verifyToken : " . $e->getMessage());
            return ['valid' => false, 'message' => 'Erreur lors de la vérification'];
        }
    }
    
    /**
     * Mark a token as used
     * 
     * Prevents a token from being reused for multiple password resets.
     * Should be called after a successful password reset.
     * 
     * @param string $token The token to mark as used
     * @return bool True if successful, false otherwise
     */
    public function markTokenAsUsed(string $token): bool {
        try {
            $stmt = $this->pdo->prepare('UPDATE MDP_OUBLIES_TOKEN SET utilise = 1 WHERE token = ?');
            return $stmt->execute([$token]);
        } catch (PDOException $e) {
            error_log("Erreur markTokenAsUsed : " . $e->getMessage());
            return false;
        }
    }
    

    
    /**
     * Update a user's password
     * 
     * Changes the user's password to a new value. The password is hashed using SHA-1
     * before being stored in the database.
     * 
     * @param int $utilisateur_id The ID of the user whose password to update
     * @param string $new_password The new password (plain text, will be hashed)
     * @return bool True if successful, false otherwise
     */
    public function updatePassword(int $utilisateur_id, string $new_password): bool {
        try {
            $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
            
            $stmt = $this->pdo->prepare('UPDATE Utilisateur SET mdp = ? WHERE utilisateur_id = ?');
            return $stmt->execute([$hashedPassword, $utilisateur_id]);
        } catch (PDOException $e) {
            error_log("Erreur updatePassword : " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Clean expired and used tokens
     * 
     * Removes all expired tokens and used tokens from the database.
     * This method should be called periodically (e.g., via cron job) to maintain
     * database hygiene.
     * 
     * @return bool True if successful, false otherwise
     */
    public function cleanExpiredTokens(): bool {
        try {
            $stmt = $this->pdo->prepare(
                'DELETE FROM MDP_OUBLIES_TOKEN WHERE expire_dans < NOW() OR utilise = 1'
            );
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur cleanExpiredTokens : " . $e->getMessage());
            return false;
        }
    }
}
