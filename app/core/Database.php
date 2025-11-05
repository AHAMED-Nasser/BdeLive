<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use Exception;

$configPath = __DIR__ . '/../config/config.php';
if (file_exists($configPath)) {
    require_once $configPath;
} else {
    if (!defined('DB_HOST')) {
        define('DB_HOST', $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? '127.0.0.1');
    }
    if (!defined('DB_NAME')) {
        define('DB_NAME', $_ENV['DB_NAME'] ?? $_SERVER['DB_NAME'] ?? 'bdelive_test');
    }
    if (!defined('DB_USER')) {
        define('DB_USER', $_ENV['DB_USER'] ?? $_SERVER['DB_USER'] ?? 'root');
    }
    if (!defined('DB_PASSWORD')) {
        define('DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? $_SERVER['DB_PASSWORD'] ?? 'root');
    }
    if (!defined('DB_CHARSET')) {
        define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? $_SERVER['DB_CHARSET'] ?? 'utf8mb4');
    }
}

/**
 * Classe Database : singleton gérant une seule connexion PDO.
 */
class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    /**
     * Constructeur privé pour empêcher l'instanciation directe.
     *
     * @throws PDOException
     */
    private function __construct()
    {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            $this->pdo = new PDO($dsn, DB_USER, DB_PASSWORD, $options);
        } catch (PDOException $e) {
            error_log('Database connection error: ' . $e->getMessage());
            throw new PDOException('Unable to connect to database', (int) $e->getCode(), $e);
        }
    }

    /**
     * Récupère l'instance unique du singleton.
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Récupère l'objet PDO pour exécuter des requêtes.
     */
    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    /**
     * Empêche le clonage.
     *
     * @throws \Error
     */
    private function __clone(): void
    {
        throw new \Error('Cloning of Database is not allowed');
    }

    /**
     * Empêche la désérialisation.
     *
     * @throws Exception
     */
    public function __wakeup(): void
    {
        throw new Exception('Cannot unserialize singleton');
    }
}
