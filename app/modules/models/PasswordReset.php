<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

class PasswordReset {
    private Database $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getUserByEmail(string $email): array|false {
        try {
            $result = $this->db->queryOne(
                'SELECT utilisateur_id, nom, prenom, email FROM Utilisateur WHERE email = ?',
                [$email]
            );
            
            // Convertir null en false pour respecter le type de retour
            return $result ?: false;
        } catch (Exception $e) {
            error_log("Erreur getUserByEmail : " . $e->getMessage());
            return false;
        }
    }
    
    public function createToken(int $utilisateur_id): string|false {
        try {
            $token = bin2hex(random_bytes(32));
            
            $expire_dans = date('Y-m-d H:i:s', strtotime('+3 hours'));
            
            $this->db->execute(
                'INSERT INTO MDP_OUBLIES_TOKEN (utilisateur_id, token, expire_dans) VALUES (?, ?, ?)',
                [$utilisateur_id, $token, $expire_dans]
            );
            
            return $token;
        } catch (Exception $e) {
            error_log("Erreur createToken : " . $e->getMessage());
            return false;
        }
    }
    
    public function verifyToken(string $token): array {
        try {
            $result = $this->db->queryOne(
                'SELECT id, utilisateur_id, expire_dans, utilise FROM MDP_OUBLIES_TOKEN WHERE token = ?',
                [$token]
            );
            
            // Vérifier si le token existe (null ou false)
            if (!$result || $result === null) {
                return ['valid' => false, 'message' => 'Code invalide'];
            }
            
            if ($result['utilise'] == 1) {
                return ['valid' => false, 'message' => 'Ce code a déjà été utilisé'];
            }
            
            $expire_time = strtotime($result['expire_dans']);
            $current_time = time();
            
            if ($current_time > $expire_time) {
                $this->db->execute('DELETE FROM MDP_OUBLIES_TOKEN WHERE id = ?', [$result['id']]);
                return ['valid' => false, 'message' => 'Ce code a expiré'];
            }
            
            return ['valid' => true, 'utilisateur_id' => $result['utilisateur_id'], 'token_id' => $result['id']];
            
        } catch (Exception $e) {
            error_log("Erreur verifyToken : " . $e->getMessage());
            return ['valid' => false, 'message' => 'Erreur lors de la vérification'];
        }
    }
    
    public function markTokenAsUsed(string $token): bool {
        try {
            return $this->db->execute(
                'UPDATE MDP_OUBLIES_TOKEN SET utilise = 1 WHERE token = ?',
                [$token]
            );
        } catch (Exception $e) {
            error_log("Erreur markTokenAsUsed : " . $e->getMessage());
            return false;
        }
    }
    
    public function deleteToken(string $token): bool {
        try {
            return $this->db->execute(
                'DELETE FROM MDP_OUBLIES_TOKEN WHERE token = ?',
                [$token]
            );
        } catch (Exception $e) {
            error_log("Erreur deleteToken : " . $e->getMessage());
            return false;
        }
    }
    
    public function updatePassword(int $utilisateur_id, string $new_password): bool {
        try {
            $hashedPassword = sha1($new_password);
            
            return $this->db->execute(
                'UPDATE Utilisateur SET mdp = ? WHERE utilisateur_id = ?',
                [$hashedPassword, $utilisateur_id]
            );
        } catch (Exception $e) {
            error_log("Erreur updatePassword : " . $e->getMessage());
            return false;
        }
    }
    
    public function cleanExpiredTokens(): bool {
        try {
            return $this->db->execute(
                'DELETE FROM MDP_OUBLIES_TOKEN WHERE expire_dans < NOW() OR utilise = 1'
            );
        } catch (Exception $e) {
            error_log("Erreur cleanExpiredTokens : " . $e->getMessage());
            return false;
        }
    }
}


