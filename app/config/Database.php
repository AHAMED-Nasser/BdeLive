<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

/** 
 * This class manages a single PDO database connection instance throughout
 * the application lifecycle. It ensures only one connection exists and
 * provides access to it via the getInstance() method.
 * 
 * @package BdeLive\Config
 * @author Mohamed-Amine Boudhib, Thomas Palot 
 * @version 1.0.0
 */
class Database
{
    /**
     * Single instance of the Database class
     * 
     * @var Database|null
     */
    private static ?Database $instance = null;
    
    /**
     * PDO database connection instance
     * 
     * @var PDO
     */
    private PDO $pdo;

    /**
     * Private constructor to prevent direct instantiation
     * 
     * Initializes the PDO connection with MySQL database using configuration
     * from config.php. Sets error mode to exceptions and default fetch mode
     * to associative arrays.
     * 
     * @throws PDOException If database connection fails
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
     * Get the single instance of the Database class
     * 
     * Creates a new instance if one doesn't exist, otherwise returns
     * the existing instance. This ensures only one database connection
     * exists throughout the application.
     * 
     * @return Database The singleton instance
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
     * Returns the active PDO connection that can be used to execute
     * database queries throughout the application.
     * 
     * @return PDO The PDO database connection
     */
    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    /**
     * Prevent cloning of the singleton instance
     * 
     * @return void
     */
    private function __clone() {}

    /**
     * Prevent unserialization of the singleton instance
     * 
     * @throws Exception Always throws exception to prevent unserialization
     * @return void
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }
}
