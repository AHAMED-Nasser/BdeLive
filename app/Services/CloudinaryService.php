<?php

declare(strict_types=1);

namespace App\Services;

use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Api\Admin\AdminApi;
use Exception;

/**
 * CloudinaryService - Image Upload and Management Service
 *
 * Handles all Cloudinary operations for image uploads, transformations,
 * and deletions. Provides validation and error handling for file uploads.
 *
 * Features:
 * - Single and multiple image uploads
 * - Automatic image optimization and resizing
 * - File validation (type, size, MIME type)
 * - Image deletion from Cloudinary
 * - Comprehensive error logging
 *
 * @package App\Services
 * @version 1.0.0
 * @author BdeLive Team
 */
class CloudinaryService
{
    private UploadApi $uploadApi;

    /**
     * Constructor - Initialize Cloudinary service
     *
     * Loads Cloudinary SDK and configuration, initializes API client.
     *
     * @throws Exception If Cloudinary SDK is not installed or config file is missing
     */
    public function __construct()
    {
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
     * Upload a single image to Cloudinary
     *
     * Validates the file, uploads it to Cloudinary with automatic optimization,
     * and returns the URL and public ID.
     *
     * Transformations applied:
     * - Max dimensions: 1920x1080 (limit crop)
     * - Quality: auto:good (automatic optimization)
     * - Unique filename generation
     *
     * @param array{name: string, type: string, tmp_name: string, error: int, size: int} $file File from $_FILES
     * @param string $folder Cloudinary folder path (default: 'events')
     * @return array{url: string, public_id: string}|null Array with URL and public_id on success, null on failure
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
            return null;
        }
    }

    /**
     * Upload multiple images to Cloudinary
     *
     * Processes an array of files from a multi-file input field.
     * Each file is validated and uploaded individually.
     * Skips files with upload errors.
     *
     * @param array{
     *     name: array<int, string>,
     *     type: array<int, string>,
     *     tmp_name: array<int, string>,
     *     error: array<int, int>,
     *     size: array<int, int>
     * } $files Files array from $_FILES
     * @param string $folder Cloudinary folder path (default: 'events')
     * @return array<int, array{url: string, public_id: string}> Array of successfully uploaded images
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
                    }
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
     * Delete a single image from Cloudinary
     *
     * Removes an image from Cloudinary storage using its public ID.
     *
     * @param string $publicId The Cloudinary public_id of the image to delete
     * @return bool True if deletion successful, false otherwise
     */
    public function deleteImage(string $publicId): bool
    {
        try {
            $this->uploadApi->destroy($publicId);
            return true;
        } catch (Exception $e) {
            error_log('CloudinaryService::deleteImage - ERROR: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete multiple images from Cloudinary
     *
     * Removes multiple images from Cloudinary storage.
     * Iterates through each public ID and deletes individually.
     *
     * @param array<int, string> $publicIds Array of Cloudinary public_ids to delete
     * @return bool True if all deletions successful, false if any failed
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
     * Validate an image file before upload
     *
     * Performs comprehensive validation:
     * - Checks for upload errors
     * - Verifies file exists in tmp directory
     * - Checks file size (max 10MB)
     * - Validates MIME type (jpeg, jpg, png, gif, webp)
     *
     * @param array{name: string, type: string, tmp_name: string, error: int, size: int} $file File array from $_FILES
     * @return bool True if file is valid, false otherwise
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
     *
     * Converts PHP upload error codes to descriptive messages.
     *
     * @param int $errorCode PHP upload error code (UPLOAD_ERR_* constants)
     * @return string Human-readable error description
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
     * Generate a transformed image URL
     *
     * Creates a Cloudinary URL with transformation parameters.
     * Can be used to generate thumbnails, crops, or other transformations on-the-fly.
     *
     * Example transformations:
     * - ['width' => 400, 'height' => 300, 'crop' => 'fill']
     * - ['quality' => 'auto:low', 'format' => 'webp']
     *
     * @param string $url Original Cloudinary image URL
     * @param array<string, mixed> $transformations Transformation parameters
     * @return string Transformed image URL (currently returns original URL - to be implemented)
     */
    public function getTransformedURL(string $url, array $transformations = []): string
    {
        // This method can be used to generate thumbnails on the fly
        // For example: ['width' => 400, 'height' => 300, 'crop' => 'fill']
        return $url; // Simplified for the example - to be fully implemented
    }
}
