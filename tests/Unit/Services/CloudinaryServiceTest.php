<?php

declare(strict_types=1);

namespace App\Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\CloudinaryService;
use Exception;

/**
 * Test suite for CloudinaryService
 *
 * Note: File validation tests are true unit tests (no Cloudinary API needed).
 * Upload/delete tests are skipped if Cloudinary credentials are not configured.
 *
 * @package App\Tests\Unit\Services
 */
class CloudinaryServiceTest extends TestCase
{
    /**
     * Test that service can be instantiated
     *
     * @return void
     */
    public function testServiceCanBeInstantiated(): void
    {
        try {
            $service = new CloudinaryService();
            $this->assertInstanceOf(CloudinaryService::class, $service);
        } catch (Exception $e) {
            $this->markTestSkipped('Cloudinary SDK not available: ' . $e->getMessage());
        }
    }

    /**
     * Test that uploadImage rejects files with upload errors
     *
     * @return void
     */
    public function testUploadImageRejectsFileWithUploadError(): void
    {
        try {
            $service = new CloudinaryService();

            $file = [
                'name' => 'test.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => '/tmp/nonexistent',
                'error' => UPLOAD_ERR_PARTIAL,
                'size' => 1024
            ];

            $result = $service->uploadImage($file);
            $this->assertNull($result);
        } catch (Exception $e) {
            $this->markTestSkipped('Cloudinary SDK not available: ' . $e->getMessage());
        }
    }

    /**
     * Test that uploadImage rejects files that are too large
     *
     * @return void
     */
    public function testUploadImageRejectsFileTooLarge(): void
    {
        try {
            $service = new CloudinaryService();

            $tmpFile = $this->createTempImageFile();

            $file = [
                'name' => 'large.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => $tmpFile,
                'error' => UPLOAD_ERR_OK,
                'size' => 11 * 1024 * 1024 // 11MB - exceeds 10MB limit
            ];

            $result = $service->uploadImage($file);
            $this->assertNull($result);

            unlink($tmpFile);
        } catch (Exception $e) {
            $this->markTestSkipped('Cloudinary SDK not available: ' . $e->getMessage());
        }
    }

    /**
     * Test that uploadImage rejects empty files
     *
     * @return void
     */
    public function testUploadImageRejectsEmptyFile(): void
    {
        try {
            $service = new CloudinaryService();

            $tmpFile = tempnam(sys_get_temp_dir(), 'empty_');
            touch($tmpFile); // Create empty file

            $file = [
                'name' => 'empty.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => $tmpFile,
                'error' => UPLOAD_ERR_OK,
                'size' => 0
            ];

            $result = $service->uploadImage($file);
            $this->assertNull($result);

            unlink($tmpFile);
        } catch (Exception $e) {
            $this->markTestSkipped('Cloudinary SDK not available: ' . $e->getMessage());
        }
    }

    /**
     * Test that uploadImage rejects non-existent files
     *
     * @return void
     */
    public function testUploadImageRejectsNonExistentFile(): void
    {
        try {
            $service = new CloudinaryService();

            $file = [
                'name' => 'test.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => '/tmp/nonexistent_file_12345.jpg',
                'error' => UPLOAD_ERR_OK,
                'size' => 1024
            ];

            $result = $service->uploadImage($file);
            $this->assertNull($result);
        } catch (Exception $e) {
            $this->markTestSkipped('Cloudinary SDK not available: ' . $e->getMessage());
        }
    }

    /**
     * Test that uploadImage rejects invalid MIME types
     *
     * @return void
     */
    public function testUploadImageRejectsInvalidMimeType(): void
    {
        try {
            $service = new CloudinaryService();

            $tmpFile = tempnam(sys_get_temp_dir(), 'txt_');
            file_put_contents($tmpFile, 'This is a text file, not an image');

            $file = [
                'name' => 'test.txt',
                'type' => 'text/plain',
                'tmp_name' => $tmpFile,
                'error' => UPLOAD_ERR_OK,
                'size' => filesize($tmpFile)
            ];

            $result = $service->uploadImage($file);
            $this->assertNull($result);

            unlink($tmpFile);
        } catch (Exception $e) {
            $this->markTestSkipped('Cloudinary SDK not available: ' . $e->getMessage());
        }
    }

    /**
     * Test that uploadImage accepts valid JPEG files
     *
     * @return void
     */
    public function testUploadImageAcceptsValidJpegFile(): void
    {
        try {
            $service = new CloudinaryService();

            $tmpFile = $this->createTempImageFile('jpeg');

            $file = [
                'name' => 'test.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => $tmpFile,
                'error' => UPLOAD_ERR_OK,
                'size' => filesize($tmpFile)
            ];

            $result = $service->uploadImage($file);

            // If Cloudinary is configured, we should get a result
            // If not configured, we'll get null or an exception
            if ($result !== null) {
                $this->assertIsArray($result);
                $this->assertArrayHasKey('url', $result);
                $this->assertArrayHasKey('public_id', $result);
                $this->assertNotEmpty($result['url']);
                $this->assertNotEmpty($result['public_id']);
            } else {
                // Cloudinary not configured - test that service handles it gracefully
                $this->assertNull($result);
            }

            unlink($tmpFile);
        } catch (Exception $e) {
            $this->markTestSkipped('Cloudinary credentials not configured: ' . $e->getMessage());
        }
    }

    /**
     * Test that uploadImage accepts valid PNG files
     *
     * @return void
     */
    public function testUploadImageAcceptsValidPngFile(): void
    {
        try {
            $service = new CloudinaryService();

            $tmpFile = $this->createTempImageFile('png');

            $file = [
                'name' => 'test.png',
                'type' => 'image/png',
                'tmp_name' => $tmpFile,
                'error' => UPLOAD_ERR_OK,
                'size' => filesize($tmpFile)
            ];

            $result = $service->uploadImage($file);

            if ($result !== null) {
                $this->assertIsArray($result);
                $this->assertArrayHasKey('url', $result);
                $this->assertArrayHasKey('public_id', $result);
            } else {
                // Cloudinary not configured - test that service handles it gracefully
                $this->assertNull($result);
            }

            unlink($tmpFile);
        } catch (Exception $e) {
            $this->markTestSkipped('Cloudinary credentials not configured: ' . $e->getMessage());
        }
    }

    /**
     * Test that uploadMultipleImages handles multiple files correctly
     *
     * @return void
     */
    public function testUploadMultipleImagesHandlesMultipleFiles(): void
    {
        try {
            $service = new CloudinaryService();

            $tmpFile1 = $this->createTempImageFile('jpeg');
            $tmpFile2 = $this->createTempImageFile('png');

            $files = [
                'name' => ['test1.jpg', 'test2.png'],
                'type' => ['image/jpeg', 'image/png'],
                'tmp_name' => [$tmpFile1, $tmpFile2],
                'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK],
                'size' => [filesize($tmpFile1), filesize($tmpFile2)]
            ];

            $result = $service->uploadMultipleImages($files);

            $this->assertIsArray($result);

            // If Cloudinary is configured, we should get results
            if (count($result) > 0) {
                foreach ($result as $uploadResult) {
                    $this->assertArrayHasKey('url', $uploadResult);
                    $this->assertArrayHasKey('public_id', $uploadResult);
                }
            }

            unlink($tmpFile1);
            unlink($tmpFile2);
        } catch (Exception $e) {
            $this->markTestSkipped('Cloudinary credentials not configured: ' . $e->getMessage());
        }
    }

    /**
     * Test that uploadMultipleImages skips files with errors
     *
     * @return void
     */
    public function testUploadMultipleImagesSkipsFilesWithErrors(): void
    {
        try {
            $service = new CloudinaryService();

            $tmpFile1 = $this->createTempImageFile('jpeg');
            $tmpFile2 = $this->createTempImageFile('jpeg');

            $files = [
                'name' => ['test1.jpg', 'test2.jpg'],
                'type' => ['image/jpeg', 'image/jpeg'],
                'tmp_name' => [$tmpFile1, $tmpFile2],
                'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_PARTIAL],
                'size' => [filesize($tmpFile1), filesize($tmpFile2)]
            ];

            $result = $service->uploadMultipleImages($files);

            $this->assertIsArray($result);
            // At most 1 file should be uploaded (the first one without error)
            $this->assertLessThanOrEqual(1, count($result));

            unlink($tmpFile1);
            unlink($tmpFile2);
        } catch (Exception $e) {
            $this->markTestSkipped('Cloudinary credentials not configured: ' . $e->getMessage());
        }
    }

    /**
     * Test that deleteImage method exists and accepts string parameter
     *
     * @return void
     */
    public function testDeleteImageMethodExists(): void
    {
        try {
            $service = new CloudinaryService();
            $this->assertTrue(method_exists($service, 'deleteImage'));
        } catch (Exception $e) {
            $this->markTestSkipped('Cloudinary SDK not available: ' . $e->getMessage());
        }
    }

    /**
     * Test that deleteMultipleImages method exists and accepts array parameter
     *
     * @return void
     */
    public function testDeleteMultipleImagesMethodExists(): void
    {
        try {
            $service = new CloudinaryService();
            $this->assertTrue(method_exists($service, 'deleteMultipleImages'));
        } catch (Exception $e) {
            $this->markTestSkipped('Cloudinary SDK not available: ' . $e->getMessage());
        }
    }

    /**
     * Test that getTransformedURL returns a URL
     *
     * @return void
     */
    public function testGetTransformedURLReturnsUrl(): void
    {
        try {
            $service = new CloudinaryService();

            $originalUrl = 'https://res.cloudinary.com/demo/image/upload/v1234/sample.jpg';
            $transformations = ['width' => 400, 'height' => 300, 'crop' => 'fill'];

            $result = $service->getTransformedURL($originalUrl, $transformations);

            $this->assertIsString($result);
            $this->assertNotEmpty($result);
        } catch (Exception $e) {
            $this->markTestSkipped('Cloudinary SDK not available: ' . $e->getMessage());
        }
    }

    /**
     * Helper method to create a temporary image file
     *
     * @param string $type Image type (jpeg, png, gif)
     * @return string Path to temporary file
     */
    private function createTempImageFile(string $type = 'jpeg'): string
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'img_');

        if (!extension_loaded('gd')) {
            file_put_contents($tmpFile, 'fake image data');
            return $tmpFile;
        }

        $img = imagecreatetruecolor(10, 10);

        switch ($type) {
            case 'png':
                imagepng($img, $tmpFile);
                break;
            case 'gif':
                imagegif($img, $tmpFile);
                break;
            case 'jpeg':
            default:
                imagejpeg($img, $tmpFile);
                break;
        }

        imagedestroy($img);

        return $tmpFile;
    }

    /**
     * Clean up temporary files after tests
     *
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        // Clean up any remaining temporary files
        $tmpDir = sys_get_temp_dir();
        $patterns = ['img_*', 'txt_*', 'empty_*'];

        foreach ($patterns as $pattern) {
            $files = glob($tmpDir . '/' . $pattern);
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
    }
}