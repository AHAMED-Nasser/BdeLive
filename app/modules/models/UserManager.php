<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/Database.php';

class UserManager
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }


    public function hashPassword(string $password): string
    {
        // SHA-1 generates exactly 40 hexadecimal characters
        return hash('sha1', $password);
    }

    public function verifyPassword(string $password, string $hashedPassword): bool
    {
        return hash('sha1', $password) === $hashedPassword;
    }


    public function findUserByEmail(string $email): array|false
    {
        try {
            $query = "SELECT utilisateur_id, nom, prenom, classe_annee, email, mdp 
                      FROM Utilisateur 
                      WHERE email = :email 
                      LIMIT 1";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['email' => $email]);
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log('UserManager::findUserByEmail - ' . $e->getMessage());
            throw $e;
        }
    }

    public function findUserById(int $utilisateurId): array|false
    {
        try {
            $query = "SELECT utilisateur_id, nom, prenom, classe_annee, email 
                      FROM Utilisateur 
                      WHERE utilisateur_id = :utilisateur_id 
                      LIMIT 1";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['utilisateur_id' => $utilisateurId]);
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log('UserManager::findUserById - ' . $e->getMessage());
            throw $e;
        }
    }


    public function getAllUsers(): array
    {
        try {
            $query = "SELECT utilisateur_id, nom, prenom, classe_annee, email 
                      FROM Utilisateur 
                      ORDER BY nom, prenom";
            
            $stmt = $this->pdo->query($query);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('UserManager::getAllUsers - ' . $e->getMessage());
            throw $e;
        }
    }

    public function findUsersByClasseAnnee(string $classeAnnee): array
    {
        try {
            $query = "SELECT utilisateur_id, nom, prenom, classe_annee, email 
                      FROM Utilisateur 
                      WHERE classe_annee = :classe_annee 
                      ORDER BY nom, prenom";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['classe_annee' => $classeAnnee]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('UserManager::findUsersByClasseAnnee - ' . $e->getMessage());
            throw $e;
        }
    }


    public function createUser(string $nom, string $prenom, string $classeAnnee, string $email, string $mdp): int|false
    {
        try {
            $hashedPassword = $this->hashPassword($mdp);
            
            $query = "INSERT INTO Utilisateur (nom, prenom, classe_annee, email, mdp) 
                      VALUES (:nom, :prenom, :classe_annee, :email, :mdp)";
            
            $stmt = $this->pdo->prepare($query);
            
            $success = $stmt->execute([
                'nom' => $nom,
                'prenom' => $prenom,
                'classe_annee' => $classeAnnee,
                'email' => $email,
                'mdp' => $hashedPassword
            ]);
            
            return $success ? (int)$this->pdo->lastInsertId() : false;
        } catch (PDOException $e) {
            error_log('UserManager::createUser - ' . $e->getMessage());
            throw $e;
        }
    }


    public function updateUser(int $utilisateurId, string $nom, string $prenom, string $classeAnnee, string $email): bool
    {
        try {
            $query = "UPDATE Utilisateur 
                      SET nom = :nom, 
                          prenom = :prenom, 
                          classe_annee = :classe_annee, 
                          email = :email 
                      WHERE utilisateur_id = :utilisateur_id";
            
            $stmt = $this->pdo->prepare($query);
            
            return $stmt->execute([
                'utilisateur_id' => $utilisateurId,
                'nom' => $nom,
                'prenom' => $prenom,
                'classe_annee' => $classeAnnee,
                'email' => $email
            ]);
        } catch (PDOException $e) {
            error_log('UserManager::updateUser - ' . $e->getMessage());
            throw $e;
        }
    }

    public function updatePassword(int $utilisateurId, string $newMdp): bool
    {
        try {
            $hashedPassword = $this->hashPassword($newMdp);
            
            $query = "UPDATE Utilisateur 
                      SET mdp = :mdp 
                      WHERE utilisateur_id = :utilisateur_id";
            
            $stmt = $this->pdo->prepare($query);
            
            return $stmt->execute([
                'utilisateur_id' => $utilisateurId,
                'mdp' => $hashedPassword
            ]);
        } catch (PDOException $e) {
            error_log('UserManager::updatePassword - ' . $e->getMessage());
            throw $e;
        }
    }


    public function deleteUser(int $utilisateurId): bool
    {
        try {
            $query = "DELETE FROM Utilisateur WHERE utilisateur_id = :utilisateur_id";
            
            $stmt = $this->pdo->prepare($query);
            
            return $stmt->execute(['utilisateur_id' => $utilisateurId]);
        } catch (PDOException $e) {
            error_log('UserManager::deleteUser - ' . $e->getMessage());
            throw $e;
        }
    }

    public function emailExists(string $email): bool
    {
        try {
            $query = "SELECT COUNT(*) as count 
                      FROM Utilisateur 
                      WHERE email = :email";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['email' => $email]);
            $result = $stmt->fetch();
            
            return $result['count'] > 0;
        } catch (PDOException $e) {
            error_log('UserManager::emailExists - ' . $e->getMessage());
            throw $e;
        }
    }
}

