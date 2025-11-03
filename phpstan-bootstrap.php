<?php
// phpstan-bootstrap.php
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'test');
if (!defined('DB_USER')) define('DB_USER', 'user');
if (!defined('DB_PASSWORD')) define('DB_PASSWORD', 'pass_test');
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');
if (!defined('ADMIN_EMAIL')) define('ADMIN_EMAIL', 'admin@example.com');
if (!defined('ADMIN_PWD')) define('ADMIN_PWD', 'pass_admin_test');

// Mock de Mailer
if (!class_exists('Mailer')) {
    class Mailer {
        public function sendPasswordResetEmail() {}
    }
}