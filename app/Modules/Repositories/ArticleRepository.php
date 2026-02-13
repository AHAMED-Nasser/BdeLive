<?php

declare(strict_types=1);

namespace App\Modules\Repositories;

use App\Modules\Entities\Article;
use App\Modules\Factories\ArticleFactory;
use App\Modules\Helpers\SlugGenerator;
use PDO;
use PDOException;

/**
 * ArticleRepository - Data Mapper for Article entities
 *
 * This repository implements the Repository Pattern and Data Mapper Pattern,
 * providing a clean separation between the domain layer (Article entities)
 * and the database layer (PDO).
 *
 * Uses ArticleFactory to transform PDO arrays into Article entities.
 *
 * @package BdeLive\Repositories
 * @author BdeLive - Group 8
 * @version 2.0.0
 */
class ArticleRepository
{
    /**
     * PDO database connection instance
     *
     * @var PDO
     */
    private PDO $pdo;

    /**
     * Constructor - Dependency Injection of PDO connection
     *
     * Following best practices (CM4 Slide 22), the PDO connection
     * is injected via constructor to facilitate testing and respect
     * the Dependency Inversion Principle.
     *
     * @param PDO $pdo Database connection instance
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // =========================================================================
    // READ OPERATIONS
    // =========================================================================

    /**
     * Find an article by its unique identifier
     *
     * @param int $id Article ID
     * @return Article|null Article entity or null if not found
     */
    public function findById(int $id): ?Article
    {
        try {
            $query = "SELECT * FROM ARTICLES WHERE id = :id";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([':id' => $id]);

            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            return $data ? ArticleFactory::createFromDatabase($data) : null;
        } catch (PDOException $e) {
            error_log('ArticleRepository::findById - ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Find an article by its SEO-friendly slug
     *
     * @param string $slug URL-friendly slug
     * @return Article|null Article entity or null if not found
     */
    public function findBySlug(string $slug): ?Article
    {
        try {
            $query = "SELECT * FROM ARTICLES WHERE slug = :slug";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([':slug' => $slug]);

            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            return $data ? ArticleFactory::createFromDatabase($data) : null;
        } catch (PDOException $e) {
            error_log('ArticleRepository::findBySlug - ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Retrieve paginated articles ordered by creation date (descending)
     *
     * @param int $offset Starting offset
     * @param int $limit Number of articles to retrieve
     * @return array<int, Article> Array of Article entities
     */
    public function findPaginated(int $offset, int $limit): array
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

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return ArticleFactory::createCollectionFromDatabase($results);
        } catch (PDOException $e) {
            error_log('ArticleRepository::findPaginated - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Retrieve latest articles ordered by creation date (descending)
     *
     * @param int $limit Maximum number of articles to retrieve
     * @return array<int, Article> Array of latest Article entities
     */
    public function findLatestArticles(int $limit): array
    {
        try {
            $query = "SELECT id, title, slug, description, image_url, author, created_at 
                      FROM ARTICLES 
                      ORDER BY created_at DESC 
                      LIMIT :limit";

            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return ArticleFactory::createCollectionFromDatabase($results);
        } catch (PDOException $e) {
            error_log('ArticleRepository::findLatestArticles - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Count total number of articles
     *
     * @return int Total article count
     */
    public function count(): int
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
            error_log('ArticleRepository::count - ' . $e->getMessage());
            return 0;
        }
    }

    // =========================================================================
    // WRITE OPERATIONS
    // =========================================================================

    /**
     * Save an article (insert or update)
     *
     * If the article has no ID (null), it will be inserted.
     * If the article has an ID, it will be updated.
     *
     * This method implements the "smart save" pattern recommended
     * for clean controller code.
     *
     * @param Article $article Article entity to save
     * @return bool True if save succeeded, false otherwise
     */
    public function save(Article $article): bool
    {
        if ($article->getId() === null) {
            return $this->insert($article);
        }

        return $this->update($article);
    }

    /**
     * Delete an article by its ID
     *
     * @param int $id Article ID to delete
     * @return bool True if deletion succeeded, false otherwise
     */
    public function delete(int $id): bool
    {
        try {
            $query = "DELETE FROM ARTICLES WHERE id = :id";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([':id' => $id]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('ArticleRepository::delete - ' . $e->getMessage());
            return false;
        }
    }

    // =========================================================================
    // PRIVATE HELPER METHODS - Internal use only
    // =========================================================================

    /**
     * Insert a new article into the database
     *
     * @param Article $article Article entity to insert
     * @return bool True if insertion succeeded, false otherwise
     */
    private function insert(Article $article): bool
    {
        try {
            // Generate unique slug from article title
            $slug = $this->generateUniqueSlug($article->getTitle());

            $query = "INSERT INTO ARTICLES (title, slug, description, image_url, author) 
                      VALUES (:title, :slug, :description, :image_url, :author)";

            $stmt = $this->pdo->prepare($query);

            return $stmt->execute([
                ':title' => $article->getTitle(),
                ':slug' => $slug,
                ':description' => $article->getDescription(),
                ':image_url' => $article->getImageUrl(),
                ':author' => $article->getAuthor()
            ]);
        } catch (PDOException $e) {
            error_log('ArticleRepository::insert - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update an existing article in the database
     *
     * @param Article $article Article entity to update
     * @return bool True if update succeeded, false otherwise
     */
    private function update(Article $article): bool
    {
        $articleId = $article->getId();
        if ($articleId === null) {
            error_log('ArticleRepository::update - Cannot update article without ID');
            return false;
        }

        try {
            // Regenerate slug if title has changed
            $currentArticle = $this->findById($articleId);
            if ($currentArticle === null) {
                error_log('ArticleRepository::update - Article not found: ' . $articleId);
                return false;
            }

            $slug = $article->getSlug();
            if ($currentArticle->getTitle() !== $article->getTitle()) {
                $slug = $this->generateUniqueSlugForUpdate($article->getTitle(), $articleId);
            }

            // Build query based on image handling
            $imageUrl = $article->getImageUrl();

            // Explicit delete: null or 'DELETE' → set image_url = NULL in DB
            if ($imageUrl === null || $imageUrl === 'DELETE') {
                $query = "UPDATE ARTICLES 
                         SET title = :title, slug = :slug, description = :description, 
                             image_url = NULL, author = :author 
                         WHERE id = :id";
                $params = [
                    ':title' => $article->getTitle(),
                    ':slug' => $slug,
                    ':description' => $article->getDescription(),
                    ':author' => $article->getAuthor(),
                    ':id' => $articleId
                ];
            } elseif (!empty($imageUrl)) {
                $query = "UPDATE ARTICLES 
                         SET title = :title, slug = :slug, description = :description, 
                             image_url = :image_url, author = :author 
                         WHERE id = :id";
                $params = [
                    ':title' => $article->getTitle(),
                    ':slug' => $slug,
                    ':description' => $article->getDescription(),
                    ':image_url' => $imageUrl,
                    ':author' => $article->getAuthor(),
                    ':id' => $articleId
                ];
            } else {
                $query = "UPDATE ARTICLES 
                         SET title = :title, slug = :slug, description = :description, 
                             author = :author 
                         WHERE id = :id";
                $params = [
                    ':title' => $article->getTitle(),
                    ':slug' => $slug,
                    ':description' => $article->getDescription(),
                    ':author' => $article->getAuthor(),
                    ':id' => $articleId
                ];
            }

            $stmt = $this->pdo->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log('ArticleRepository::update - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate a unique slug from a title using SlugGenerator
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
     * @return bool True if exists, false otherwise
     */
    private function slugExists(string $slug): bool
    {
        try {
            $query = "SELECT COUNT(*) FROM ARTICLES WHERE slug = :slug";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([':slug' => $slug]);

            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('ArticleRepository::slugExists - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate a unique slug for an article update
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
     * Check if a slug exists excluding a specific article ID
     *
     * @param string $slug Slug to check
     * @param int $excludeId ID of the article to exclude from check
     * @return bool True if exists, false otherwise
     */
    private function slugExistsExcludingId(string $slug, int $excludeId): bool
    {
        try {
            $query = "SELECT COUNT(*) FROM ARTICLES WHERE slug = :slug AND id != :id";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([':slug' => $slug, ':id' => $excludeId]);

            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('ArticleRepository::slugExistsExcludingId - ' . $e->getMessage());
            return false;
        }
    }
}
