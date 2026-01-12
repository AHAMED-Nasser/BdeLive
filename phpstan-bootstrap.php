<?php
// phpstan-bootstrap.php
require_once __DIR__ . '/vendor/autoload.php';

$neededConstantes = [
    'DB_HOST' => 'localhost',
    'DB_NAME' => 'test',
    'DB_USER' => 'user',
    'DB_PASSWORD' => 'pass_test',
    'DB_CHARSET' => 'utf8mb4',
    'SMTP_HOST' => 'smpt@smtp.com',
    'SMTP_USER' => 'smtp.user',
    'SMTP_PASSWORD' => '......',
    'FROM_EMAIL' => 'no_reply@exemple.com'
];

foreach ($neededConstantes as $constant => $defaultValue) {
    if (!defined($constant)) {
        define($constant, $defaultValue);
    }
}

// Stub pour App\Config\Mailer (si nécessaire pour l'analyse)
// Note: Maintenant que app/Config/* n'est plus exclu, ce stub ne devrait plus être nécessaire
// mais on le garde pour compatibilité avec les anciennes configurations
if (!class_exists('App\\Config\\Mailer')) {
    eval('
        namespace App\\Config {
            class Mailer {
                public function sendPasswordResetEmail(string $to_email, string $to_name, string $token): bool {
                    return true;
                }
                public function sendVerificationEmail(string $to_email, string $to_name, string $token): bool {
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
