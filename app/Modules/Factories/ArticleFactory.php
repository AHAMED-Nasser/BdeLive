<?php

declare(strict_types=1);

namespace App\Modules\Factories;

use App\Modules\Entities\Article;

/**
 * ArticleFactory - Transforms raw database data into Article entities
 *
 * This factory class acts as a translator between the database layer
 * (PDO arrays with column names) and the domain layer (Article entities).
 *
 * Responsibilities:
 * - Hydrate Article entities from PDO result arrays
 * - Handle type conversions and null values
 * - Provide clean, type-safe entities to controllers
 *
 * Design Pattern: Factory Pattern
 * Related to: Data Mapper Pattern
 *
 * @package BdeLive\Factories
 * @author BdeLive - Group 8
 * @version 1.0.0
 */
class ArticleFactory
{
    /**
     * Create an Article entity from database row data
     *
     * Transforms a raw PDO array (from ARTICLES table) into a clean,
     * type-safe Article entity. Handles all type conversions and
     * ensures data integrity.
     *
     * Database columns mapping:
     * - id → id (int|null)
     * - title → title (string)
     * - slug → slug (string)
     * - description → description (string)
     * - image_url → imageUrl (string|null)
     * - author → author (string)
     * - created_at → createdAt (string, datetime)
     *
     * @param array<string, mixed> $data Raw database row from ARTICLES table
     * @return Article Hydrated Article entity
     */
    public static function createFromDatabase(array $data): Article
    {
        return new Article(
            id: isset($data['id']) ? (int) $data['id'] : null,
            title: (string) ($data['title'] ?? ''),
            slug: (string) ($data['slug'] ?? ''),
            description: (string) ($data['description'] ?? ''),
            imageUrl: !empty($data['image_url']) ? (string) $data['image_url'] : null,
            author: (string) ($data['author'] ?? ''),
            createdAt: (string) ($data['created_at'] ?? date('Y-m-d H:i:s'))
        );
    }

    /**
     * Create multiple Article entities from database result set
     *
     * Batch version of createFromDatabase for paginated results.
     * Useful when retrieving multiple articles from findPaginated() or findLatestArticles().
     *
     * @param array<int, array<string, mixed>> $dataSet Array of database rows
     * @return array<int, Article> Array of hydrated Article entities
     */
    public static function createCollectionFromDatabase(array $dataSet): array
    {
        $articles = [];
        foreach ($dataSet as $data) {
            $articles[] = self::createFromDatabase($data);
        }
        return $articles;
    }

    /**
     * Convert an Article entity back to database-ready array
     *
     * Useful for UPDATE and INSERT operations. Transforms the entity
     * back into an array format compatible with PDO prepared statements.
     *
     * Note: This method excludes the ID for INSERT operations (handled separately).
     * Note: The created_at timestamp is excluded as it's auto-managed by database.
     *
     * @param Article $article Article entity to convert
     * @return array<string, mixed> Database-ready associative array
     */
    public static function toDatabase(Article $article): array
    {
        return [
            'title' => $article->getTitle(),
            'slug' => $article->getSlug(),
            'description' => $article->getDescription(),
            'image_url' => $article->getImageUrl(),
            'author' => $article->getAuthor()
        ];
    }
}
