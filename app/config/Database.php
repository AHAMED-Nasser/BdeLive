<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

/**
 * Class Database
 * Singleton class for managing database connection using PDO
 * Ensures only one PDO instance exists throughout the application
 */
class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    /**
     * Private constructor to prevent direct instantiation
     * Initializes PDO connection with proper error handling
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
            ];
            
            $this->pdo = new PDO($dsn, DB_USER, DB_PASSWORD, $options);
        } catch (PDOException $e) {
            error_log('Database connection error: ' . $e->getMessage());
            throw new PDOException('Unable to connect to database');
        }
    }

    /**
     * Get singleton instance of Database
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
     * Get PDO connection instance
     * 
     * @return PDO The PDO connection object
     */
    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    /**
     * Prevent cloning of the singleton instance
     */
    private function __clone() {}

    /**
     * Prevent deserialization of the singleton instance
     * 
     * @throws Exception Always throws to prevent deserialization
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }
}
