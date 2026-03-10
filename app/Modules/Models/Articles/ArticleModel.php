<?php

declare(strict_types=1);

namespace App\Modules\Models\Articles;

use PDO;
use PDOException;
use App\Modules\Helpers\SlugGenerator;

/**

 * Responsibilities:
 * - Read: Retrieve articles with pagination, search by slug/ID
 * - Write: Create, update and delete articles
 * - Automatic management of unique slugs
 *
 * @package BdeLive\Models\Articles
 * @author BdeLive - Group 8
 * @version 2.0.0
 */
class ArticleModel
{
    private PDO $pdo;

    /**
     * Constructor - PDO dependency injection
     *

     *
     * @param PDO $pdo Database connection instance
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }



    /**
     * Insert a new article into the database
     *
     * Creates a new article record with all provided information.
     * Automatically generates a unique slug from the title.
     *
     * @param string $title Article title
     * @param string $description Article description/content
     * @param string $imageUrl Cloudinary image URL
     * @param string $author Author full name
     * @return bool True on success, false on failure
     */
    public function insertArticle(
        string $title,
        string $description,
        string $imageUrl,
        string $author
    ): bool {
        try {
            // Generate a unique slug from the title
            $slug = $this->generateUniqueSlug($title);

            $query = "INSERT INTO ARTICLES (title, slug, description, image_url, author)
                      VALUES (:title, :slug, :description, :image_url, :author)";

            $stmt = $this->pdo->prepare($query);

            return $stmt->execute([
                ':title' => $title,
                ':slug' => $slug,
                ':description' => $description,
                ':image_url' => $imageUrl,
                ':author' => $author
            ]);
        } catch (PDOException $e) {
            error_log('ArticleModel::insertArticle - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update an existing article in the database
     *
     * Updates information of an existing article identified by its ID.
     * If the title changes, a new unique slug is generated.
     * The image URL is updated only if a new image is provided.
     *
     * @param int $articleId ID of the article to update
     * @param string $title New article title
     * @param string $description New description/content
     * @param string $imageUrl New Cloudinary image URL (empty string to keep existing)
     * @param string $author New author name
     * @return bool True on success, false on failure
     */
    public function updateArticle(
        int $articleId,
        string $title,
        string $description,
        string $imageUrl,
        string $author
    ): bool {
        try {
            // Retrieve current article to check if title has changed
            $currentArticle = $this->getArticleById($articleId);
            if ($currentArticle === null) {
                error_log('ArticleModel::updateArticle - Article not found: ' . $articleId);
                return false;
            }

            // Generate a new slug if the title has changed
            $slug = $currentArticle['slug'];
            if ($currentArticle['title'] !== $title) {
                $slug = $this->generateUniqueSlugForUpdate($title, $articleId);
            }

            // Build the SQL query depending on image handling
            if ($imageUrl === 'DELETE') {
                $query = "UPDATE ARTICLES
                         SET title = :title, slug = :slug, description = :description,
                             image_url = NULL, author = :author
                         WHERE id = :id";
                $params = [
                    ':title' => $title,
                    ':slug' => $slug,
                    ':description' => $description,
                    ':author' => $author,
                    ':id' => $articleId
                ];
            } elseif (!empty($imageUrl)) {
                $query = "UPDATE ARTICLES
                         SET title = :title, slug = :slug, description = :description,
                             image_url = :image_url, author = :author
                         WHERE id = :id";
                $params = [
                    ':title' => $title,
                    ':slug' => $slug,
                    ':description' => $description,
                    ':image_url' => $imageUrl,
                    ':author' => $author,
                    ':id' => $articleId
                ];
            } else {
                $query = "UPDATE ARTICLES
                         SET title = :title, slug = :slug, description = :description,
                             author = :author
                         WHERE id = :id";
                $params = [
                    ':title' => $title,
                    ':slug' => $slug,
                    ':description' => $description,
                    ':author' => $author,
                    ':id' => $articleId
                ];
            }

            $stmt = $this->pdo->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log('ArticleModel::updateArticle - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete an article from the database
     *
     * Permanently deletes an article identified by its ID.
     *
     * @param int $articleId ID of the article to delete
     * @return bool True on success, false on failure
     */
    public function deleteArticle(int $articleId): bool
    {
        try {
            $query = "DELETE FROM ARTICLES WHERE id = :id";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([':id' => $articleId]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('ArticleModel::deleteArticle - ' . $e->getMessage());
            return false;
        }
    }



    /**
     * Retrieve an article by its identifier
     *
     * @param int $articleId Unique article identifier
     * @return array<string, mixed>|null Article data or null if not found
     */
    public function getArticleById(int $articleId): ?array
    {
        try {
            $query = "SELECT * FROM ARTICLES WHERE id = :id";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([':id' => $articleId]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result !== false ? $result : null;
        } catch (PDOException $e) {
            error_log('ArticleModel::getArticleById - ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Retrieve an article by its slug
     *
     * @param string $slug Unique article slug
     * @return array<string, mixed>|null Article data or null if not found
     */
    public function getArticleBySlug(string $slug): ?array
    {
        try {
            $query = "SELECT * FROM ARTICLES WHERE slug = :slug";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([':slug' => $slug]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result !== false ? $result : null;
        } catch (PDOException $e) {
            error_log('ArticleModel::getArticleBySlug - ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Retrieve paginated articles sorted by creation date (descending)
     *
     * @param int $offset Start offset
     * @param int $limit Number of articles to retrieve
     * @return array<int, array<string, mixed>> Array of articles
     */
    public function getPaginatedArticles(int $offset, int $limit): array
    {
        try {
            $query = "SELECT id, title, slug, description, image_url,
                      author, created_at
                      FROM ARTICLES
                      ORDER BY created_at DESC
                      LIMIT :limit OFFSET :offset";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result ?: [];
        } catch (PDOException $e) {
            error_log('ArticleModel::getPaginatedArticles - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Retrieve latest articles sorted by creation date (descending)
     *
     * Retrieves a specified number of the most recent articles from the database.
     * The query is optimized to select only required columns for performance.
     *
     * @param int $limit Number of articles to retrieve (default: 2)
     * @return array<int, array<string, mixed>> Array of articles, empty array if none found
     */
    public function getLatestArticles(int $limit = 2): array
    {
        try {
            $query = "SELECT id, title, slug, description, image_url, author, created_at
                      FROM ARTICLES
                      ORDER BY created_at DESC
                      LIMIT :limit";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result ?: [];
        } catch (PDOException $e) {
            error_log('ArticleModel::getLatestArticles - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Count total number of articles
     *
     * @return int Total number of articles
     */
    public function countArticles(): int
    {
        try {
            $query = "SELECT COUNT(*) as total FROM ARTICLES";
            $stmt = $this->pdo->query($query);

            if ($stmt === false) {
                return 0;
            }

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int) ($result['total'] ?? 0);
        } catch (PDOException $e) {
            error_log('ArticleModel::countArticles - ' . $e->getMessage());
            return 0;
        }
    }



    /**
     * Generate a unique slug from a title using reusable SlugGenerator
     *
     * If a slug already exists, appends a number to make it unique.
     * Example: "my-article", "my-article-2", "my-article-3"
     *
     * @param string $title Title to convert
     * @return string Unique slug
     */
    private function generateUniqueSlug(string $title): string
    {
        return SlugGenerator::generateUnique($title, function (string $slug): bool {
            return $this->slugExists($slug);
        });
    }

    /**
     * Check if a slug already exists in the database
     *
     * @param string $slug Slug to check
     * @return bool True if the slug exists, false otherwise
     */
    private function slugExists(string $slug): bool
    {
        try {
            $query = "SELECT COUNT(*) FROM ARTICLES WHERE slug = :slug";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([':slug' => $slug]);

            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('ArticleModel::slugExists - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate a unique slug for an article update
     *
     * Similar to generateUniqueSlug but excludes the current article from the uniqueness check.
     * Allows updating an article without changing its slug if the title did not change,
     * or generating a new unique slug if the title changed.
     *
     * @param string $title Title to convert
     * @param int $excludeId ID of the article to exclude from uniqueness check
     * @return string Unique slug
     */
    private function generateUniqueSlugForUpdate(string $title, int $excludeId): string
    {
        return SlugGenerator::generateUnique($title, function (string $slug) use ($excludeId): bool {
            return $this->slugExistsExcludingId($slug, $excludeId);
        });
    }

    /**
     * Check if a slug already exists in the database, excluding a specific article ID
     *
     * @param string $slug Slug to check
     * @param int $excludeId ID of the article to exclude from the check
     * @return bool True if the slug exists (excluding the specified ID), false otherwise
     */
    private function slugExistsExcludingId(string $slug, int $excludeId): bool
    {
        try {
            $query = "SELECT COUNT(*) FROM ARTICLES WHERE slug = :slug AND id != :id";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([':slug' => $slug, ':id' => $excludeId]);

            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('ArticleModel::slugExistsExcludingId - ' . $e->getMessage());
            return false;
        }
    }
}
