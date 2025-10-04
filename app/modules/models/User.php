<?php
require_once __DIR__ . '/../../config/database.php';

class User {
    private $db;
    
    public function __construct(
        private $id, 
        private $nom, 
        private $prenom, 
        private $email, 
        private $password,
        private $classe_annee = null
    ) {
        $this->db = Database::getInstance();
    }

    public function register() {
        try {
            $existingUser = $this->db->queryOne(
                'SELECT utilisateur_id FROM Utilisateur WHERE email = ?', 
                [$this->email]
            );
            
            if ($existingUser) {
                return false;
            }
            
            $hashedPassword = sha1($this->password);
            
            $result = $this->db->execute(
                'INSERT INTO Utilisateur (nom, prenom, email, mdp, classe_annee) VALUES (?, ?, ?, ?, ?)',
                [$this->nom, $this->prenom, $this->email, $hashedPassword, $this->classe_annee]
            );

            return $result;

        } catch (Exception $e) {
            error_log("Erreur inscription : " . $e->getMessage());
            return false;
        }
    }
    
    public function login() {
        try {
            $user = $this->db->queryOne(
                'SELECT utilisateur_id, nom, prenom, email, mdp, classe_annee FROM Utilisateur WHERE email = ?',
                [$this->email]
            );
            
            if (!$user) {
                return false;
            }
            
            if (sha1($this->password) === $user['mdp']) {
                unset($user['mdp']);
                return $user;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Erreur connexion : " . $e->getMessage());
            return false;
        }
    }
}
?>
