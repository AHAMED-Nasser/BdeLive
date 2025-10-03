<?php
class User {
    public function __construct(private $id, private $username, private $email, private $password) {
            database::connect();
        }

    public function register() {
        try {
            $stmt = $this->connection->prepare('INSERT INTO Inscription (username, email, password) VALUES(?, ?, ?)');

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
}
?>
