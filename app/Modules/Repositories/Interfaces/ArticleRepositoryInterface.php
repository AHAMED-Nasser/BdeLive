<?php

declare(strict_types=1);

namespace App\Modules\Repositories\Interfaces;

use App\Modules\Entities\Article;

/**
 * ArticleRepositoryInterface - Contract for Article data persistence
 *
 * Defines the standard operations for Article repository following
 * the Dependency Inversion Principle (SOLID).
 *
 * This interface allows controllers to depend on abstractions rather than
 * concrete implementations, making the code more flexible and testable.
 *
 * @package BdeLive\Repositories\Interfaces
 * @author BdeLive - Group 8
 * @version 1.0.0
 */
interface ArticleRepositoryInterface
{
    /**
     * Find an article by its unique identifier
     *
     * @param int $id Article ID
     * @return Article|null Article entity or null if not found
     */
    public function findById(int $id): ?Article;

    /**
     * Find an article by its SEO-friendly slug
     *
     * @param string $slug URL-friendly slug
     * @return Article|null Article entity or null if not found
     */
    public function findBySlug(string $slug): ?Article;

    /**
     * Retrieve paginated articles ordered by creation date (descending)
     *
     * @param int $offset Starting offset
     * @param int $limit Number of articles to retrieve
     * @return array<int, Article> Array of Article entities
     */
    public function findPaginated(int $offset, int $limit): array;

    /**
     * Retrieve latest articles ordered by creation date (descending)
     *
     * @param int $limit Maximum number of articles to retrieve
     * @return array<int, Article> Array of latest Article entities
     */
    public function findLatestArticles(int $limit): array;

    /**
     * Save an article (insert or update)
     *
     * If the article has no ID (null), it will be inserted.
     * If the article has an ID, it will be updated.
     *
     * @param Article $article Article entity to save
     * @return bool True if save succeeded, false otherwise
     */
    public function save(Article $article): bool;

    /**
     * Delete an article by its ID
     *
     * @param int $id Article ID to delete
     * @return bool True if deletion succeeded, false otherwise
     */
    public function delete(int $id): bool;

    /**
     * Count total number of articles
     *
     * @return int Total article count
     */
    public function count(): int;
}
