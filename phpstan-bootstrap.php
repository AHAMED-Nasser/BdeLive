<?php
// phpstan-bootstrap.php
require_once __DIR__ . '/vendor/autoload.php';
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'test');
if (!defined('DB_USER')) define('DB_USER', 'user');
if (!defined('DB_PASSWORD')) define('DB_PASSWORD', 'pass_test');
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');
if (!defined('ADMIN_EMAIL')) define('ADMIN_EMAIL', 'events@example.com');
if (!defined('ADMIN_PWD')) define('ADMIN_PWD', 'pass_admin_test');

// Stub pour App\Config\Mailer (dossier exclu de l'analyse)
// Utilisation de eval pour créer la classe dans le bon namespace
if (!class_exists('App\\Config\\Mailer')) {
    eval('
        namespace App\\Config {
            class Mailer {
                public function sendPasswordResetEmail(string $to_email, string $to_name, string $token): bool {
                    return true;
                }
            }
        }
    ');
}

// Mock de Mailer (legacy)
if (!class_exists('Mailer')) {
    class Mailer {
        public function sendPasswordResetEmail(string $to_email, string $to_name, string $token) {}
    }
}

// Map namespaced classes to legacy names for PHPStan symbol discovery
@class_alias('App\\Core\\Database', 'Database');
@class_alias('App\\Config\\Mailer', 'Mailer');
@class_alias('App\\Modules\\Repositories\\EventRepository', 'EventRepository');
@class_alias('App\\Modules\\Repositories\\EventRegistrationRepository', 'EventRegistrationRepository');
@class_alias('App\\Modules\\Models\\Pwd\\PasswordReset', 'PasswordReset');
@class_alias('App\\Modules\\Models\\Admin\\EventCreationModel', 'EventCreationModel');
@class_alias('App\\Modules\\Models\\Users\\UserManager', 'UserManager');
@class_alias('App\\Modules\\Helpers\\Pagination', 'Pagination');
