<?php

declare(strict_types=1);

namespace App\Core\Markdown;

/**
 * Centralized Markdown rendering for events, articles, and other content.
 *
 * Uses Parsedown with safe mode enabled to prevent XSS.
 * Reusable across the application for consistent Markdown-to-HTML conversion.
 *
 * @package App\Core\Markdown
 */
final class MarkdownRenderer
{
    private static ?\Parsedown $parsedown = null;

    /**
     * Convert Markdown string to safe HTML.
     *
     * @param string|null $markdown Raw Markdown content (null treated as empty)
     * @return string Rendered HTML (safe, escaped via Parsedown safe mode)
     */
    public static function toHtml(?string $markdown): string
    {
        $raw = $markdown ?? '';
        if ($raw === '') {
            return '';
        }

        $parser = self::getParser();
        return $parser->text($raw);
    }

    /**
     * Get a plain-text preview (no Markdown, no HTML) for lists/previews.
     *
     * @param string|null $markdown Raw Markdown content
     * @param int $maxLength Maximum length before truncation
     * @return string Plain text preview
     */
    public static function toPlainPreview(?string $markdown, int $maxLength = 150): string
    {
        $raw = $markdown ?? '';
        if ($raw === '') {
            return '';
        }

        // Remove common Markdown syntax for preview
        $plain = preg_replace('/\*\*(.+?)\*\*/s', '$1', $raw);
        $plain = preg_replace('/\*(.+?)\*/s', '$1', $plain ?? '');
        $plain = preg_replace('/^#+\s+/m', '', $plain ?? '');
        $plain = preg_replace('/\[(.+?)\]\(.+?\)/s', '$1', $plain ?? '');
        $plain = trim((string) $plain);

        if (mb_strlen($plain) <= $maxLength) {
            return $plain;
        }

        return mb_substr($plain, 0, $maxLength) . '…';
    }

    /**
     * Get or create the Parsedown instance (singleton)
     *
     * @return \Parsedown Parser with safe mode enabled
     */
    private static function getParser(): \Parsedown
    {
        if (self::$parsedown === null) {
            self::$parsedown = new \Parsedown();
            self::$parsedown->setSafeMode(true);
        }

        return self::$parsedown;
    }
}
