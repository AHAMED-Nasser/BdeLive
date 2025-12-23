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
     * @param string $authorFirstname Author's first name
     * @param string $authorLastname Author's last name
     * @return bool True if insertion successful, false otherwise
     * @throws PDOException If database query fails
     */
    public function insertArticle(
        string $title,
        string $description,
        string $imageUrl,
        string $authorFirstname,
        string $authorLastname
    ): bool {
        try {
            // Generate unique slug from title
            $slug = $this->generateUniqueSlug($title);

            $query = "INSERT INTO articles (title, slug, description, image_url, author_firstname, author_lastname) 
                      VALUES (:title, :slug, :description, :image_url, :author_firstname, :author_lastname)";

            $stmt = $this->pdo->prepare($query);

            return $stmt->execute([
                ':title' => $title,
                ':slug' => $slug,
                ':description' => $description,
                ':image_url' => $imageUrl,
                ':author_firstname' => $authorFirstname,
                ':author_lastname' => $authorLastname
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
            $query = "SELECT COUNT(*) FROM articles WHERE slug = :slug";
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
            $query = "SELECT * FROM articles WHERE id = :id";
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
            $query = "SELECT * FROM articles WHERE slug = :slug";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([':slug' => $slug]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result !== false ? $result : null;
        } catch (PDOException $e) {
            error_log('ArticleModel::getArticleBySlug - ' . $e->getMessage());
            return null;
        }
    }
}
