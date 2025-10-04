<?php

    class User {
        private $db;

        public function __construct() {
            $this -> db = Database::getInstance() -> getConnexion();
        }

        // Get All users
        public function getAllUsers() {
            $query = "SELECT * FROM users";
            $stmt = $this -> db -> query($query);
            return $stmt -> fetchAll();
        }

        public function getUserByEmail($email) {
            $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
            $stmt = $this -> db -> prepare($query);
            $stmt -> execute(['email' => $email]);
            return $stmt -> fetch();
        }
    }