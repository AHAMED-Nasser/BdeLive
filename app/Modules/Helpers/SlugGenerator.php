<?php

declare(strict_types=1);

namespace App\Modules\Helpers;

/**
 * SlugGenerator - Reusable Slug Generation Helper
 *
 * Provides utility methods to generate URL-friendly slugs from strings.
 * Handles accent removal, special character sanitization, and slug uniqueness.
 *
 * Usage:
 * ```php
 * $slug = SlugGenerator::generate('Mon Premier Article');
 * // Returns: "mon-premier-article"
 *
 * $uniqueSlug = SlugGenerator::generateUnique('Mon Article', function($slug) {
 *     // Check if slug exists in database
 *     return $this->slugExists($slug);
 * });
 * ```
 *
 * @package App\Modules\Helpers
 * @version 1.0.0
 * @author BdeLive Team
 */
class SlugGenerator
{
    /**
     * Generate a URL-friendly slug from a string
     *
     * Converts a string to lowercase, replaces spaces and special characters with hyphens,
     * removes accents, and ensures the slug is URL-safe.
     *
     * Examples:
     * - "Mon Premier Article" → "mon-premier-article"
     * - "Événement Spécial!" → "evenement-special"
     * - "Article   avec    espaces" → "article-avec-espaces"
     *
     * @param string $string The string to convert
     * @return string The generated slug
     */
    public static function generate(string $string): string
    {
        // Convert to lowercase and trim
        $slug = strtolower(trim($string));

        // Remove accents (é → e, à → a, etc.)
        $slug = self::removeAccents($slug);

        // Replace non-alphanumeric characters with hyphens
        $slug = (string) preg_replace('/[^a-z0-9-]/', '-', $slug);

        // Replace multiple consecutive hyphens with single hyphen
        $slug = (string) preg_replace('/-+/', '-', $slug);

        // Remove leading and trailing hyphens
        return trim($slug, '-');
    }

    /**
     * Generate a unique slug from a string
     *
     * If a slug already exists (according to the provided callback), appends a number to make it unique.
     * Example: "mon-article", "mon-article-2", "mon-article-3"
     *
     * @param string $string The string to convert
     * @param callable $existsCallback Callback function that checks if slug exists. Returns bool.
     * @return string A unique slug
     */
    public static function generateUnique(string $string, callable $existsCallback): string
    {
        $baseSlug = self::generate($string);
        $slug = $baseSlug;
        $counter = 2;

        // Check if slug exists, if so, add a number
        while ($existsCallback($slug)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Remove accents from a string
     *
     * Converts accented characters to their non-accented equivalents.
     * Examples: é→e, à→a, ç→c, ñ→n
     *
     * @param string $string The string to process
     * @return string The string without accents
     */
    private static function removeAccents(string $string): string
    {
        $unwantedArray = [
            'á' => 'a', 'à' => 'a', 'â' => 'a', 'ä' => 'a', 'ã' => 'a', 'å' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'ö' => 'o', 'õ' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ý' => 'y', 'ÿ' => 'y',
            'ñ' => 'n', 'ç' => 'c',
            'Á' => 'a', 'À' => 'a', 'Â' => 'a', 'Ä' => 'a', 'Ã' => 'a', 'Å' => 'a',
            'É' => 'e', 'È' => 'e', 'Ê' => 'e', 'Ë' => 'e',
            'Í' => 'i', 'Ì' => 'i', 'Î' => 'i', 'Ï' => 'i',
            'Ó' => 'o', 'Ò' => 'o', 'Ô' => 'o', 'Ö' => 'o', 'Õ' => 'o',
            'Ú' => 'u', 'Ù' => 'u', 'Û' => 'u', 'Ü' => 'u',
            'Ý' => 'y',
            'Ñ' => 'n', 'Ç' => 'c'
        ];

        return strtr($string, $unwantedArray);
    }

    /**
     * Validate if a string is a valid slug format
     *
     * A valid slug contains only lowercase letters, numbers, and hyphens.
     * It should not start or end with a hyphen.
     *
     * @param string $slug The slug to validate
     * @return bool True if valid, false otherwise
     */
    public static function isValid(string $slug): bool
    {
        // Check if slug matches the pattern: lowercase letters, numbers, and hyphens
        // Should not start or end with hyphen, and no consecutive hyphens
        return (bool) preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $slug);
    }
}
