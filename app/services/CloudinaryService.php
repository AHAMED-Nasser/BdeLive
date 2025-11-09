<?php

declare(strict_types=1);

namespace App\Services;

use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Api\Admin\AdminApi;
use Exception;

class CloudinaryService
{
    private UploadApi $uploadApi;

    public function __construct()
    {
        // Load the Composer autoloader IF not already loaded
        if (!class_exists('Cloudinary\Configuration\Configuration')) {
            $autoloadPath = __DIR__ . '/../../vendor/autoload.php';
            if (file_exists($autoloadPath)) {
                require_once realpath($autoloadPath);
            } else {
                throw new Exception('Composer autoloader not found at: ' . $autoloadPath);
            }
        }

        // Verify that Cloudinary SDK is loaded
        if (!class_exists('Cloudinary\Api\Upload\UploadApi')) {
            throw new Exception('Cloudinary SDK not loaded. Please run "composer install"');
        }

        // Load Cloudinary configuration
        $configPath = __DIR__ . '/../Config/cloudinary.php';
        if (file_exists($configPath)) {
            require_once $configPath;
        } else {
            throw new Exception('Cloudinary configuration file not found: ' . $configPath);
        }

        // Initialize Cloudinary API clients
        $this->uploadApi = new UploadApi();
    }

    /**
     * Upload an image to Cloudinary with UNSIGNED upload
     * @param array{name: string, type: string, tmp_name: string, error: int, size: int} $file
     * @return array{url: string, public_id: string}|null
     */
    public function uploadImage(array $file, string $folder = 'events'): ?array
    {
        try {
            // file validation (type, size, etc.)
            if (!$this->validateImageFile($file)) {
                error_log(
                    'CloudinaryService::uploadImage - Validation failed for file: ' . $file['name']
                );
                return null;
            }

            // Log avant upload
            error_log(
                'CloudinaryService::uploadImage - Uploading: ' . $file['name'] .
                ' (Size: ' . $file['size'] . ' bytes)'
            );

            // Upload to Cloudinary with options for images (destination folder, resize with | height | crop | etc)
            // quality optimization with 'quality' => 'auto:good'
            // unique filename to avoid overwriting

            $result = $this->uploadApi->upload($file['tmp_name'], [
                'folder' => $folder,
                'resource_type' => 'image',
                'type' => 'upload',
                'overwrite' => false,
                'use_filename' => true,
                'unique_filename' => true,
                'transformation' => [
                    'width' => 1920,
                    'height' => 1080,
                    'crop' => 'limit',
                    'quality' => 'auto:good'
                ]
            ]);

            // Log succès
            error_log('CloudinaryService::uploadImage - SUCCESS: ' . $result['secure_url']);
            // if upload success, return url and public_id in JSON format else return NULL and write error log in PHP error log
            return [
                'url' => $result['secure_url'],
                'public_id' => $result['public_id']
            ];
        } catch (\Cloudinary\Api\Exception\ApiError $e) {
            error_log('CloudinaryService::uploadImage - Cloudinary API ERROR: ' . $e->getMessage());
            error_log('CloudinaryService::uploadImage - HTTP Code: ' . $e->getCode());
            return null;
        } catch (Exception $e) {
            error_log('CloudinaryService::uploadImage - ERROR: ' . $e->getMessage());
            error_log('CloudinaryService::uploadImage - File details: ' . print_r($file, true));
            return null;
        }
    }

    /**
     * Upload multiple images
     * @param array{name: array<int, string>, type: array<int, string>, tmp_name: array<int, string>, error: array<int, int>, size: array<int, int>} $files
     * @return array<int, array{url: string, public_id: string}>
     */
    public function uploadMultipleImages(array $files, string $folder = 'events'): array
    {
        $uploadedImages = []; // uploaded image empty array by default

        error_log(
            'CloudinaryService::uploadMultipleImages - Starting upload of ' .
            count($files['name']) . ' files'
        );

        // Management in the case where multiple files are sended
        if (is_array($files['tmp_name'])) {
            $fileCount = count($files['tmp_name']);

            for ($i = 0; $i < $fileCount; $i++) {
                $file = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                ];

                error_log(
                    "CloudinaryService::uploadMultipleImages - Processing file $i: {$file['name']} " .
                    "(error code: {$file['error']})"
                );

                if ($file['error'] === UPLOAD_ERR_OK) { // Verify no upload error
                    $result = $this->uploadImage($file, $folder); // call uploadImage to upload each file on Cloudinary
                    if ($result) {
                        $uploadedImages[] = $result; // stock result to uploadedImages array
                        error_log("CloudinaryService::uploadMultipleImages - File $i uploaded successfully");
                    } else {
                        error_log(
                            "CloudinaryService::uploadMultipleImages - File $i upload FAILED"
                        );
                    }
                } else {
                    error_log(
                        "CloudinaryService::uploadMultipleImages - File $i has upload error: " .
                        $this->getUploadErrorMessage($file['error'])
                    );
                }
            }
        }

        error_log(
            'CloudinaryService::uploadMultipleImages - Completed. ' .
            count($uploadedImages) . ' files uploaded successfully'
        );
        return $uploadedImages;
    }

    /**
     * Delete an image of Cloudinary
     */
    public function deleteImage(string $publicId): bool
    {
        try {
            $this->uploadApi->destroy($publicId);
            error_log('CloudinaryService::deleteImage - SUCCESS: ' . $publicId);
            return true;
        } catch (Exception $e) {
            error_log('CloudinaryService::deleteImage - ERROR: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete multiple images
     * @param array<int, string> $publicIds
     */
    public function deleteMultipleImages(array $publicIds): bool
    {
        try {
            foreach ($publicIds as $publicId) {
                $this->deleteImage($publicId);
            }
            return true;
        } catch (Exception $e) {
            error_log('CloudinaryService::deleteMultipleImages - ERROR: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Valid an image file
     * @param array{name: string, type: string, tmp_name: string, error: int, size: int} $file
     */
    private function validateImageFile(array $file): bool
    {
        // Verify upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            error_log(
                'CloudinaryService::validateImageFile - Upload error code: ' .
                $file['error'] . ' - ' . $this->getUploadErrorMessage($file['error'])
            );
            return false;
        }

        // Verify that the file exists
        if (!file_exists($file['tmp_name'])) {
            error_log('CloudinaryService::validateImageFile - Temporary file does not exist: ' . $file['tmp_name']);
            return false;
        }

        // Verify the size (10Mb max)
        if ($file['size'] > 10 * 1024 * 1024) {
            error_log('CloudinaryService::validateImageFile - File too large: ' . $file['size'] . ' bytes');
            return false;
        }

        // Verify that the file isn't empty
        if ($file['size'] === 0) {
            error_log('CloudinaryService::validateImageFile - File is empty');
            return false;
        }

        // Verifie MIME type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];

        // Use mime_content_type if available (more reliable)
        if (function_exists('mime_content_type')) {
            $mimeType = mime_content_type($file['tmp_name']);
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo === false) {
                error_log('CloudinaryService::validateImageFile - Failed to open finfo');
                return false;
            }
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
        }

        if (!in_array($mimeType, $allowedTypes)) {
            error_log(
                'CloudinaryService::validateImageFile - Invalid mime type: ' . $mimeType .
                ' (allowed: ' . implode(', ', $allowedTypes) . ')'
            );
            return false;
        }

        error_log('CloudinaryService::validateImageFile - File is valid: ' . $file['name'] . ' (' . $mimeType . ')');
        return true;
    }

    /**
     * Get human-readable upload error message
     */
    private function getUploadErrorMessage(int $errorCode): string
    {
        $errors = [
            UPLOAD_ERR_OK => 'No error',
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE in HTML form',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
        ];

        return $errors[$errorCode] ?? 'Unknown error';
    }

    /**
     * Generate an URL with transformation
     * This method can be used to generate thumbnails on the fly later...
     * @param array<string, mixed> $transformations
     */
    public function getTransformedURL(string $url, array $transformations = []): string
    {
        // This method can be used for generate miniatures on the fly
        // For exemple: ['width' => 400, 'height' => 300, 'crop' => 'fill']
        return $url; // Simplifed for the exemple
    }
}
