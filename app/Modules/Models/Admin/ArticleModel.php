<?php

declare(strict_types=1);

namespace App\Modules\Models\Admin;

use PDO;
use PDOException;
use App\Core\Database;
use App\Modules\Helpers\SlugGenerator;

/**
 * ArticleModel - Article Creation and Management
 *
 * Handles database operations for article management (admin functions).
 * Provides methods to insert articles with automatic slug generation.
 *
 * @package App\Modules\Models\Admin
 * @version 1.0.0
 * @author BdeLive Team
 */
class ArticleModel
{
    /**
     * PDO database connection instance
     *
     * @var PDO
     */
    private PDO $pdo;

    /**
     * Constructor - Initialize database connection
     *
     * @return void
     */
    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Insert a new article into the database
     *
     * Creates a new article record with all provided information including image URL.
     * Automatically generates a unique slug from the title.
     *
     * @param string $title Article title
     * @param string $description Article description/content
     * @param string $imageUrl Cloudinary image URL
     * @param string $author Author's full name
     * @return bool True if insertion successful, false otherwise
     * @throws PDOException If database query fails
     */
    public function insertArticle(
        string $title,
        string $description,
        string $imageUrl,
        string $author
    ): bool {
        try {
            // Generate unique slug from title
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
     * Generate a unique slug from a title using the reusable SlugGenerator
     *
     * If a slug already exists, appends a number to make it unique.
     * Example: "mon-article", "mon-article-2", "mon-article-3"
     *
     * @param string $title The title to convert
     * @return string A unique slug
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
     * @param string $slug The slug to check
     * @return bool True if slug exists, false otherwise
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
     * Get an article by its ID
     *
     * @param int $articleId The article ID
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
     * Get an article by its slug
     *
     * @param string $slug The article slug
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
     * Get paginated articles ordered by creation date (newest first)
     *
     * @param int $offset Starting offset
     * @param int $limit Number of articles to fetch
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
     * Get the latest articles ordered by creation date (newest first)
     *
     * Retrieves a specified number of the most recent articles from the database.
     * Optimized query that only selects necessary columns for performance.
     *
     * @param int $limit Number of articles to fetch (default: 2)
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
     * @return int Total count
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
            return (int)($result['total'] ?? 0);
        } catch (PDOException $e) {
            error_log('ArticleModel::countArticles - ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Update an existing article in the database
     *
     * Updates an article record with new information. If the title has changed,
     * generates a new unique slug. The image URL is only updated if a new image
     * is provided (non-empty string).
     *
     * @param int $articleId The ID of the article to update
     * @param string $title New article title
     * @param string $description New article description/content
     * @param string $imageUrl New Cloudinary image URL (empty string to keep existing)
     * @param string $author New author's full name
     * @return bool True if update successful, false otherwise
     * @throws PDOException If database query fails
     */
    public function updateArticle(
        int $articleId,
        string $title,
        string $description,
        string $imageUrl,
        string $author
    ): bool {
        try {
            // Get current article to check if title changed
            $currentArticle = $this->getArticleById($articleId);
            if ($currentArticle === null) {
                error_log('ArticleModel::updateArticle - Article not found: ' . $articleId);
                return false;
            }

            // Generate new slug if title changed
            $slug = $currentArticle['slug'];
            if ($currentArticle['title'] !== $title) {
                $slug = $this->generateUniqueSlugForUpdate($title, $articleId);
            }

            // Build query - handle image update, deletion, or keep existing
            if ($imageUrl === 'DELETE') {
                // Supprimer l'image (mettre à NULL)
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
                // Mettre à jour avec une nouvelle image
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
                // Conserver l'image existante (ne pas modifier image_url)
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
     * Generate a unique slug for an article update
     *
     * Similar to generateUniqueSlug but excludes the current article from uniqueness check.
     * This allows updating an article without changing its slug if the title hasn't changed,
     * or generating a new unique slug if the title has changed.
     *
     * @param string $title The title to convert
     * @param int $excludeId The article ID to exclude from uniqueness check
     * @return string A unique slug
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
     * @param string $slug The slug to check
     * @param int $excludeId The article ID to exclude from the check
     * @return bool True if slug exists (excluding the specified ID), false otherwise
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

    /**
     * Delete an article from the database
     *
     * Removes an article record by its ID. Returns true if deletion was successful,
     * false otherwise (e.g., article not found or database error).
     *
     * @param int $articleId The ID of the article to delete
     * @return bool True if deletion successful, false otherwise
     * @throws PDOException If database query fails
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
}
