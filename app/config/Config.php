<?php
declare(strict_types=1);


    class Config {
        const DB_HOST = 'mysql-bdelivesae.alwaysdata.net';
        const DB_NAME = 'bdelivesae_db';
        const DB_USER = '429915';
        const DB_PASSWORD = 'bdelive+6';
        const DB_CHARSET = 'utf8mb4';

        public function getDbHost() { return self::DB_HOST; }
        public function getDbName() { return self::DB_NAME; }
        public function getDbUser() { return self::DB_USER; }
        public function getDbPassword() { return self::DB_PASSWORD; }
        public function getDbCharset() { return self::DB_CHARSET; }
    }


