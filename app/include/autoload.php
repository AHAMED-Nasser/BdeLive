<?php

declare(strict_types=1);

spl_autoload_register(function ($className) {
    // Handle namespaced classes (App\Modules\Controllers\Public\HomeController)
    if (strpos($className, 'App\\') === 0) {
        // Remove 'App\' prefix
        $relativePath = substr($className, 4);
        // Convert namespace separators to directory separators and lowercase directories
        $path = str_replace('\\', '/', $relativePath);
        // Convert to lowercase for directories but keep class name case
        $parts = explode('/', $path);
        $classNamePart = array_pop($parts);
        $dirParts = array_map('strtolower', $parts);
        $dirPath = implode('/', $dirParts);
        $fullPath = __DIR__ . '/../' . $dirPath . '/' . $classNamePart . '.php';

        if (file_exists($fullPath)) {
            require_once $fullPath;
            return;
        }
    }

    // Backward compatibility: non-namespaced classes
    $searchPaths = [
        // Models
        __DIR__ . '/../modules/models/' . $className . '.php',
        __DIR__ . '/../modules/models/events/' . $className . '.php',
        __DIR__ . '/../modules/models/users/' . $className . '.php',
        __DIR__ . '/../modules/models/pwd/' . $className . '.php',
        __DIR__ . '/../modules/models/public/' . $className . '.php',

        // Controllers
        __DIR__ . '/../modules/controllers/' . $className . '.php',
        __DIR__ . '/../modules/controllers/cookie/' . $className . '.php',
        __DIR__ . '/../modules/controllers/events/' . $className . '.php',
        __DIR__ . '/../modules/controllers/public/' . $className . '.php',
        __DIR__ . '/../modules/controllers/pwd/' . $className . '.php',
        __DIR__ . '/../modules/controllers/users/' . $className . '.php',

        // Helpers
        __DIR__ . '/../modules/helpers/' . $className . '.php',

        // Repositories
        __DIR__ . '/../modules/repositories/' . $className . '.php',

        // Core
        __DIR__ . '/../core/' . $className . '.php',
        __DIR__ . '/../config/' . $className . '.php',

        // services
        __DIR__ . '/../services/' . $className . '.php'
    ];

    foreach ($searchPaths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

if (class_exists('App\\Services\\CloudinaryService')) {
    class_alias('App\\Services\\CloudinaryService', 'CloudinaryService');
}
