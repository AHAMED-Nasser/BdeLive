<?php

declare(strict_types=1);

namespace App\Modules\Helpers;

/**
 * Image Helper - Optimized image rendering utilities
 *
 * Provides helper functions for rendering optimized images with lazy loading,
 * proper dimensions, and Cloudinary transformations when applicable.
 *
 * @package App\Modules\Helpers
 */
class ImageHelper
{
    /**
     * Render an optimized image tag with lazy loading and proper attributes
     *
     * @param string $src Image source URL
     * @param string $alt Alt text for accessibility
     * @param array<string, mixed> $options Additional options:
     *   - 'class' (string): CSS classes
     *   - 'width' (int): Image width in pixels
     *   - 'height' (int): Image height in pixels
     *   - 'loading' (string): Loading strategy ('lazy', 'eager', 'auto') - default 'lazy'
     *   - 'fetchpriority' (string): Fetch priority ('high', 'low', 'auto') - default 'auto'
     *   - 'decoding' (string): Decoding strategy ('async', 'sync', 'auto') - default 'async'
     *   - 'sizes' (string): Responsive image sizes attribute
     *   - 'srcset' (string): Responsive image srcset attribute
     * @return string HTML image tag
     */
    public static function renderImage(
        string $src,
        string $alt,
        array $options = []
    ): string {
        $class = $options['class'] ?? '';
        $width = $options['width'] ?? null;
        $height = $options['height'] ?? null;
        $loading = $options['loading'] ?? 'lazy';
        $fetchpriority = $options['fetchpriority'] ?? 'auto';
        $decoding = $options['decoding'] ?? 'async';
        $sizes = $options['sizes'] ?? null;
        $srcset = $options['srcset'] ?? null;

        // Build attributes array
        $attributes = [
            'src' => htmlspecialchars($src, ENT_QUOTES, 'UTF-8'),
            'alt' => htmlspecialchars($alt, ENT_QUOTES, 'UTF-8'),
            'loading' => $loading,
            'decoding' => $decoding,
        ];

        // Add optional attributes
        if ($class !== '') {
            $attributes['class'] = htmlspecialchars($class, ENT_QUOTES, 'UTF-8');
        }

        if ($width !== null) {
            $attributes['width'] = (int)$width;
        }

        if ($height !== null) {
            $attributes['height'] = (int)$height;
        }

        if ($fetchpriority !== 'auto') {
            $attributes['fetchpriority'] = $fetchpriority;
        }

        if ($sizes !== null) {
            $attributes['sizes'] = htmlspecialchars($sizes, ENT_QUOTES, 'UTF-8');
        }

        if ($srcset !== null) {
            $attributes['srcset'] = htmlspecialchars($srcset, ENT_QUOTES, 'UTF-8');
        }

        // Build HTML string
        $html = '<img';
        foreach ($attributes as $key => $value) {
            $html .= ' ' . $key . '="' . $value . '"';
        }
        $html .= '>';

        return $html;
    }

    /**
     * Optimize Cloudinary image URL with transformations
     *
     * @param string $url Original Cloudinary URL
     * @param array<string, mixed> $transformations Cloudinary transformations:
     *   - 'width' (int): Resize width
     *   - 'height' (int): Resize height
     *   - 'quality' (string): Image quality ('auto', 'auto:best', 'auto:good', 'auto:eco', 'auto:low')
     *   - 'format' (string): Image format ('auto', 'webp', 'avif', 'jpg', 'png')
     *   - 'crop' (string): Crop mode ('fill', 'fit', 'limit', 'scale', 'thumb')
     * @return string Optimized Cloudinary URL
     */
    public static function optimizeCloudinaryUrl(string $url, array $transformations = []): string
    {
        // Check if URL is from Cloudinary
        if (strpos($url, 'res.cloudinary.com') === false) {
            return $url;
        }

        // Parse URL to extract path
        $parsedUrl = parse_url($url);
        if ($parsedUrl === false || !isset($parsedUrl['path'])) {
            return $url;
        }

        $path = $parsedUrl['path'];
        $query = $parsedUrl['query'] ?? '';

        // Extract existing transformations if any
        // Cloudinary URL format: /v{version}/{cloud_name}/{resource_type}/{type}/{transformations}/{public_id}.{format}
        $pathParts = explode('/', trim($path, '/'));
        
        // Build transformation string
        $transformationParts = [];
        
        if (isset($transformations['width']) || isset($transformations['height'])) {
            $width = $transformations['width'] ?? null;
            $height = $transformations['height'] ?? null;
            $crop = $transformations['crop'] ?? 'limit';
            
            if ($width !== null && $height !== null) {
                $transformationParts[] = "w_{$width},h_{$height},c_{$crop}";
            } elseif ($width !== null) {
                $transformationParts[] = "w_{$width},c_limit";
            } elseif ($height !== null) {
                $transformationParts[] = "h_{$height},c_limit";
            }
        }

        if (isset($transformations['quality'])) {
            $quality = $transformations['quality'];
            $transformationParts[] = "q_{$quality}";
        } else {
            // Default to auto quality for better compression
            $transformationParts[] = 'q_auto';
        }

        if (isset($transformations['format'])) {
            $format = $transformations['format'];
            $transformationParts[] = "f_{$format}";
        } else {
            // Default to auto format (WebP/AVIF when supported)
            $transformationParts[] = 'f_auto';
        }

        // If no transformations needed, return original URL
        if (empty($transformationParts)) {
            return $url;
        }

        $transformationString = implode(',', $transformationParts);

        // Insert transformation into URL path
        // Format: /v{version}/{cloud_name}/{resource_type}/{type}/{transformations}/{public_id}.{format}
        // We need to insert transformations before the filename
        
        // Simple approach: add transformations as query parameter if not already in path
        if (strpos($path, '/upload/') !== false) {
            // Insert transformations into path
            $path = str_replace('/upload/', '/upload/' . $transformationString . '/', $path);
        }

        // Rebuild URL
        $scheme = $parsedUrl['scheme'] ?? 'https';
        $host = $parsedUrl['host'] ?? '';
        $newUrl = $scheme . '://' . $host . $path;
        
        if ($query !== '') {
            $newUrl .= '?' . $query;
        }

        return $newUrl;
    }
}
