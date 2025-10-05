<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/Database.php';

class PasswordReset {
    private PDO $pdo;
    
    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }
    
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
    
    public function createToken(int $utilisateur_id): string|false {
        try {
            $this -> cleanExpiredTokens();
            $stmt = $this->pdo->prepare('DELETE FROM MDP_OUBLIES_TOKEN WHERE utilisateur_id = ?');
            $stmt -> execute([$utilisateur_id]);

            $token = bin2hex(random_bytes(32));
            $expire_dans = date('Y-m-d H:i:s', strtotime('+3 minutes'));
            
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
    
    public function markTokenAsUsed(string $token): bool {
        try {
            $stmt = $this->pdo->prepare('UPDATE MDP_OUBLIES_TOKEN SET utilise = 1 WHERE token = ?');
            return $stmt->execute([$token]);
        } catch (PDOException $e) {
            error_log("Erreur markTokenAsUsed : " . $e->getMessage());
            return false;
        }
    }
    
//    public function deleteToken(string $token): bool {
//        try {
//            $stmt = $this->pdo->prepare('DELETE FROM MDP_OUBLIES_TOKEN WHERE token = ?');
//            return $stmt->execute([$token]);
//        } catch (PDOException $e) {
//            error_log("Erreur deleteToken : " . $e->getMessage());
//            return false;
//        }
//    }
    
    public function updatePassword(int $utilisateur_id, string $new_password): bool {
        try {
            $hashedPassword = sha1($new_password);
            
            $stmt = $this->pdo->prepare('UPDATE Utilisateur SET mdp = ? WHERE utilisateur_id = ?');
            return $stmt->execute([$hashedPassword, $utilisateur_id]);
        } catch (PDOException $e) {
            error_log("Erreur updatePassword : " . $e->getMessage());
            return false;
        }
    }
    
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
