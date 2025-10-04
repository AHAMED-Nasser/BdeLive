<?php
class User
{
    public function __construct(private $id, private $username, private $email, private $password)
    {
        Database::connect();
    }

    public function register()
    {
        try {
            $stmt = $this->connection->prepare('INSERT INTO Utilisateurs (nom, classe_annee, prenom, email, mdp) VALUES(?, ?, ?, ?, ?)');

            if (!$stmt) {
                throw new Exception("Erreur lors de la préparation de la requête");
            }
            $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);

            $stmt->bind_param("sss", $this->username, $this->email, $hashedPassword);

            if ($stmt->execute()) {
                $stmt->close();
                return true;
            } else {
                $stmt->close();
                throw new Exception("Erreur lors de l'insertion : " . $stmt->error);
            }

        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function login($email, $password)
    {
        try {

            $stmt = $this->connection->prepare('SELECT email, mdp FROM Inscription WHERE email = ?');

            if (!$stmt) {
                throw new Exception("Erreur lors de la préparation de la requête");
            }

            $stmt->bind_param("s", $email);

            if (!$stmt->execute()) {
                $stmt->close();
                throw new Exception("Erreur lors de l'exécution de la requête");
            }

            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                $stmt->close();
                return false;
            }

            $user = $result->fetch_assoc();
            $stmt->close();

            // Vérifier le mot de passe
            if (password_verify($password, $user['password'])) {

                $this->id = $user['id'];
                $this->email = $user['email'];
                $this->password = $user['password'];

                // Démarrer une session
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];

                return true;
            } else {
                return false;
            }

        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}
?>
