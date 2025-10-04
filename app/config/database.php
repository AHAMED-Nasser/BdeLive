<?php
require_once __DIR__ . '/config/database.php';
class Database {

    private static ?Database $instance = null;
    private ?PDO $connection = null;


    private function __construct() {
        try {
            $dsn = sprintf(
                "mysql:host=%s;dbname=%s;charset=%s",
                self::HOST,
                self::DB_NAME,
                self::DB_CHARSET
            );

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false
            ];

            $this->connection = new PDO($dsn, self::DB_USERNAME, self::DB_PASSWORD, $options);

        } catch (PDOException $e) {
            error_log("Erreur de connexion à la base de données : " . $e->getMessage());
            throw new Exception("Impossible de se connecter à la base de données");
        }
    }


    private function __clone() {}


    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    public function getConnection(): PDO {
        return $this->connection;
    }

    public function query(string $sql, array $params = []): array {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur de requête : " . $e->getMessage());
            throw new Exception("Erreur lors de l'exécution de la requête");
        }
    }


    public function queryOne(string $sql, array $params = []): ?array {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Erreur de requête : " . $e->getMessage());
            throw new Exception("Erreur lors de l'exécution de la requête");
        }
    }


    public function execute(string $sql, array $params = []): bool {
        try {
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Erreur d'exécution : " . $e->getMessage());
            throw new Exception("Erreur lors de l'exécution de la requête");
        }
    }


    public function lastInsertId(): string {
        return $this->connection->lastInsertId();
    }


    public function beginTransaction(): bool {
        return $this->connection->beginTransaction();
    }


    public function commit(): bool {
        return $this->connection->commit();
    }


    public function rollback(): bool {
        return $this->connection->rollBack();
    }


    public function inTransaction(): bool {
        return $this->connection->inTransaction();
    }
}
?>