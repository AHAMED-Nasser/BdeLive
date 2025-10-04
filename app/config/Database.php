<?php

    class Database {

        private static $instance = null;
        private $pdo;

        private function __construct() {
            try {
                $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
                $this -> pdo = new PDO($dsn, DB_USER, DB_PASSWORD);
            } catch (PDOException $e) {
                die('Erreur de connexion à la base de données : ' . $e->getMessage());
            }
        }

        public static function getInstance() {
            if (self::$instance == null) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function getConnexion() {
            return $this -> pdo;
        }

        public function __clone() {}

        public function __wakeup() {
            throw new Exception("Pas de déserialisation");
        }
    }