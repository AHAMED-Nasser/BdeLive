<?php
// config/database.php

class Database {
    // Configuration de la base de données
    private const HOST = 'mysql-bdelivesae.alwaysdata.net';
    private const DB_NAME = 'bdelivesae_db';
    private const USERNAME = '429915';
    private const PASSWORD = 'bdelive+6';
    private const CHARSET = 'utf8mb4';

    // Instance unique (Singleton)
    private static ?Database $instance = null;
    private ?PDO $connection = null;

    /**
     * Constructeur privé pour empêcher l'instanciation directe
     */
    private function __construct() {
        try {
            $dsn = sprintf(
                "mysql:host=%s;dbname=%s;charset=%s",
                self::HOST,
                self::DB_NAME,
                self::CHARSET
            );

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false
            ];

            $this->connection = new PDO($dsn, self::USERNAME, self::PASSWORD, $options);

        } catch (PDOException $e) {
            error_log("Erreur de connexion à la base de données : " . $e->getMessage());
            throw new Exception("Impossible de se connecter à la base de données");
        }
    }

    /**
     * Empêcher le clonage de l'instance
     */
    private function __clone() {}

    /**
     * Empêcher la désérialisation
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }

    /**
     * Obtenir l'instance unique de Database (Singleton)
     */
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Obtenir la connexion PDO
     */
    public function getConnection(): PDO {
        return $this->connection;
    }

    /**
     * Exécuter une requête SELECT et retourner tous les résultats
     */
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

    /**
     * Exécuter une requête SELECT et retourner une seule ligne
     */
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

    /**
     * Exécuter une requête INSERT, UPDATE ou DELETE
     */
    public function execute(string $sql, array $params = []): bool {
        try {
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Erreur d'exécution : " . $e->getMessage());
            throw new Exception("Erreur lors de l'exécution de la requête");
        }
    }

    /**
     * Obtenir l'ID du dernier enregistrement inséré
     */
    public function lastInsertId(): string {
        return $this->connection->lastInsertId();
    }

    /**
     * Démarrer une transaction
     */
    public function beginTransaction(): bool {
        return $this->connection->beginTransaction();
    }

    /**
     * Valider une transaction
     */
    public function commit(): bool {
        return $this->connection->commit();
    }

    /**
     * Annuler une transaction
     */
    public function rollback(): bool {
        return $this->connection->rollBack();
    }

    /**
     * Vérifier si une transaction est en cours
     */
    public function inTransaction(): bool {
        return $this->connection->inTransaction();
    }
}
?>