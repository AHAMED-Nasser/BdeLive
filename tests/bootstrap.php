<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/../app/include/autoload.php';

$cloudinaryConfigPath = __DIR__ . '/../app/config/cloudinary.php';
if (file_exists($cloudinaryConfigPath)) {
    try {
        require_once $cloudinaryConfigPath;
    } catch (\Throwable $e) {
        error_log('Warning: Cloudinary config could not be loaded: ' . $e->getMessage());
    }
}
