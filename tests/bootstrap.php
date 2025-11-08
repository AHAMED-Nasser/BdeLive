<?php

declare(strict_types=1);

/**
 * PHPUnit Bootstrap File
 *
 * Loads Composer autoloader, custom autoloader, and defines constants needed for tests.
 * 
 * Note: If config.php exists and defines database constants, they will be used.
 * Otherwise, test defaults or environment variables will be used.
 */

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load custom autoloader (handles namespaces)
require_once __DIR__ . '/../app/include/autoload.php';

// Try to load config.php if it exists (it will define DB constants)
// If config.php doesn't exist or constants are not defined, use test defaults
$configPath = __DIR__ . '/../app/config/config.php';
if (file_exists($configPath)) {
    // Load config but don't fail if it errors (tests should still work)
    try {
        require_once $configPath;
    } catch (\Throwable $e) {
        // Config file exists but may have errors, continue with test defaults
    }
}

// Load Cloudinary configuration for tests
$cloudinaryConfigPath = __DIR__ . '/../app/config/cloudinary.php';
if (file_exists($cloudinaryConfigPath)) {
    try {
        require_once $cloudinaryConfigPath;
    } catch (\Throwable $e) {
        // Cloudinary config exists but may have errors, tests will be skipped
        error_log('Warning: Cloudinary config could not be loaded: ' . $e->getMessage());
    }
}

// Define database constants if not already defined (for tests)
// This allows using environment variables or test database if config.php doesn't define them
if (!defined('DB_HOST')) {
    define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', $_ENV['DB_NAME'] ?? 'test_db');
}
if (!defined('DB_USER')) {
    define('DB_USER', $_ENV['DB_USER'] ?? 'test_user');
}
if (!defined('DB_PASSWORD')) {
    define('DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? 'test_password');
}
if (!defined('DB_CHARSET')) {
    define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? 'utf8mb4');
}

