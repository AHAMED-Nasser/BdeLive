<?php
declare(strict_types=1);

require_once __DIR__ . '/Config.php';

class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        $config = new Config();

        try {
            $dsn = 'mysql:host=' . $config->getDbHost() . ';dbname=' . $config->getDbName() . ';charset=' . $config->getDbCharset();
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            
            $this->pdo = new PDO($dsn, $config->getDbUser(), $config->getDbPassword(), $options);
        } catch (PDOException $e) {
            error_log('Database connection error: ' . $e->getMessage());
            throw new PDOException('Unable to connect to database');
        }
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    public function getConnection(): PDO
    {
        return $this->pdo;
    }


    private function __clone() {}

    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }
}
