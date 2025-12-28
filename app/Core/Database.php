<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use Exception;

$configPath = __DIR__ . '/../Config/config.php';
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
 * Database - Database Connection Singleton
 *
 * Manages a single PDO connection throughout the application lifecycle.
 * Implements the Singleton pattern to ensure only one database connection exists.
 *
 * Features:
 * - Single PDO connection instance
 * - Automatic connection configuration from config file or environment variables
 * - UTF-8 charset support
 * - Exception mode for errors
 * - Prevents cloning and unserialization
 *
 * @package App\Core
 * @version 1.0.0
 */
class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    /**
     * Private constructor to prevent direct instantiation
     *
     * Establishes database connection using configuration from config.php
     * or environment variables as fallback.
     *
     * @throws PDOException If connection fails
     */
    private function __construct()
    {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];

            $this->pdo = new PDO($dsn, DB_USER, DB_PASSWORD, $options);
        } catch (PDOException $e) {
            error_log('Database connection error: ' . $e->getMessage());
            throw new PDOException('Unable to connect to database', (int) $e->getCode(), $e);
        }
    }

    /**
     * Get the singleton instance of Database
     *
     * Creates the instance on first call, then returns the same instance.
     *
     * @return Database The unique Database instance
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Get the PDO connection object
     *
     * Returns the PDO instance for executing database queries.
     *
     * @return PDO The PDO database connection
     */
    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    /**
     * Prevent cloning (Singleton pattern)
     *
     * @throws \Error Always throws to prevent cloning
     */
    private function __clone(): void
    {
        throw new \Error('Cloning of Database is not allowed');
    }

    /**
     * Prevent unserialization (Singleton pattern)
     *
     * @throws Exception Always throws to prevent unserialization
     */
    public function __wakeup(): void
    {
        throw new Exception('Cannot unserialize singleton');
    }
}
