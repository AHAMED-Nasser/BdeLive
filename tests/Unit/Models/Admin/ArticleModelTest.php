<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Admin;

use PHPUnit\Framework\TestCase;
use App\Modules\Models\Admin\ArticleModel;
use App\Core\Database;
use PDO;
use PDOStatement;
use PDOException;
use ReflectionClass;

/**
 * Unit tests for ArticleModel
 */
class ArticleModelTest extends TestCase
{
    private ArticleModel $model;
    private PDO $mockPdo;
    private PDOStatement $mockStmt;

    protected function setUp(): void
    {
        $this->mockPdo = $this->createMock(PDO::class);
        $this->mockStmt = $this->createMock(PDOStatement::class);

        $mockDatabase = $this->createMock(Database::class);
        $mockDatabase->method('getConnection')->willReturn($this->mockPdo);

        $reflection = new ReflectionClass(Database::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null, $mockDatabase);

        $this->model = new ArticleModel();
    }

    protected function tearDown(): void
    {
        $reflection = new ReflectionClass(Database::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null, null);
    }

    /**
     * Test successful article insertion with all fields
     */
    public function testInsertArticleWithAllFields(): void
    {
        $title = 'Mon Premier Article';
        $description = 'Ceci est une description de test pour l\'article.';
        $imageUrl = 'https://res.cloudinary.com/test/image/upload/v123/articles/test.jpg';
        $author = 'Jean Dupont';

        // Mock slug existence check (slug doesn't exist)
        $checkStmt = $this->createMock(PDOStatement::class);
        $checkStmt->method('execute')->willReturn(true);
        $checkStmt->method('fetchColumn')->willReturn(0);

        $insertStmt = $this->createMock(PDOStatement::class);
        $insertStmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) use ($title, $description, $imageUrl, $author) {
                return $params[':title'] === $title &&
                       $params[':slug'] === 'mon-premier-article' &&
                       $params[':description'] === $description &&
                       $params[':image_url'] === $imageUrl &&
                       $params[':author'] === $author;
            }))
            ->willReturn(true);

        $this->mockPdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($checkStmt, $insertStmt);

        $result = $this->model->insertArticle(
            $title,
            $description,
            $imageUrl,
            $author
        );

        $this->assertTrue($result);
    }

    /**
     * Test slug generation from title with special characters
     */
    public function testSlugSanitization(): void
    {
        $title = 'Article Spécial 2024';
        $description = 'Description test';
        $imageUrl = 'https://cloudinary.com/test.jpg';
        $author = 'Marie Martin';

        // Mock for slug existence check
        $checkStmt = $this->createMock(PDOStatement::class);
        $checkStmt->method('execute')->willReturn(true);
        $checkStmt->method('fetchColumn')->willReturn(0);

        // Mock for insert
        $insertStmt = $this->createMock(PDOStatement::class);
        $insertStmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) {
                // Should convert accents and remove special characters
                // é becomes e, special chars become hyphens
                return $params[':slug'] === 'article-special-2024';
            }))
            ->willReturn(true);

        $this->mockPdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($checkStmt, $insertStmt);

        $result = $this->model->insertArticle(
            $title,
            $description,
            $imageUrl,
            $author
        );

        $this->assertTrue($result);
    }

    /**
     * Test slug generation with multiple consecutive spaces
     */
    public function testSlugWithMultipleSpaces(): void
    {
        $title = 'Article   avec    espaces';
        $description = 'Test';
        $imageUrl = 'https://cloudinary.com/test.jpg';
        $author = 'Paul Durand';

        $checkStmt = $this->createMock(PDOStatement::class);
        $checkStmt->method('execute')->willReturn(true);
        $checkStmt->method('fetchColumn')->willReturn(0);

        $insertStmt = $this->createMock(PDOStatement::class);
        $insertStmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) {
                // Should replace multiple spaces with single hyphen
                return $params[':slug'] === 'article-avec-espaces';
            }))
            ->willReturn(true);

        $this->mockPdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($checkStmt, $insertStmt);

        $result = $this->model->insertArticle(
            $title,
            $description,
            $imageUrl,
            $author
        );

        $this->assertTrue($result);
    }

    /**
     * Test slug uniqueness - should append number if slug exists
     */
    public function testSlugUniqueness(): void
    {
        $title = 'Article Test';
        $description = 'Description';
        $imageUrl = 'https://cloudinary.com/test.jpg';
        $author = 'Sophie Bernard';

        // Mock slug existence checks
        $checkStmt1 = $this->createMock(PDOStatement::class);
        $checkStmt1->method('execute')->willReturn(true);
        $checkStmt1->method('fetchColumn')->willReturn(1); // First slug exists

        $checkStmt2 = $this->createMock(PDOStatement::class);
        $checkStmt2->method('execute')->willReturn(true);
        $checkStmt2->method('fetchColumn')->willReturn(0); // Second slug doesn't exist

        $insertStmt = $this->createMock(PDOStatement::class);
        $insertStmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) {
                // Should append -2 since first slug exists
                return $params[':slug'] === 'article-test-2';
            }))
            ->willReturn(true);

        $this->mockPdo->expects($this->exactly(3))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($checkStmt1, $checkStmt2, $insertStmt);

        $result = $this->model->insertArticle(
            $title,
            $description,
            $imageUrl,
            $author
        );

        $this->assertTrue($result);
    }

    /**
     * Test article insertion returns false on PDO exception
     */
    public function testInsertArticleReturnsFalseOnException(): void
    {
        $title = 'Test Article';
        $description = 'Description';
        $imageUrl = 'https://cloudinary.com/test.jpg';
        $author = 'Test User';

        // First call succeeds (slug check), second call throws exception (insert)
        $checkStmt = $this->createMock(PDOStatement::class);
        $checkStmt->method('execute')->willReturn(true);
        $checkStmt->method('fetchColumn')->willReturn(0);

        $this->mockPdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls(
                $checkStmt,
                $this->throwException(new PDOException('Database error'))
            );

        $result = $this->model->insertArticle(
            $title,
            $description,
            $imageUrl,
            $author
        );

        $this->assertFalse($result);
    }

    /**
     * Test getArticleById returns article data
     */
    public function testGetArticleByIdReturnsArticle(): void
    {
        $articleId = 1;
        $expectedArticle = [
            'id' => 1,
            'title' => 'Test Article',
            'slug' => 'test-article',
            'description' => 'Test description',
            'image_url' => 'https://cloudinary.com/test.jpg',
                'author' => 'Jean Dupont',
            'created_at' => '2024-01-01 10:00:00',
            'updated_at' => '2024-01-01 10:00:00'
        ];

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([':id' => $articleId])
            ->willReturn(true);

        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expectedArticle);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with('SELECT * FROM ARTICLES WHERE id = :id')
            ->willReturn($this->mockStmt);

        $result = $this->model->getArticleById($articleId);

        $this->assertEquals($expectedArticle, $result);
    }

    /**
     * Test getArticleById returns null when article not found
     */
    public function testGetArticleByIdReturnsNullWhenNotFound(): void
    {
        $articleId = 999;

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn(false);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->model->getArticleById($articleId);

        $this->assertNull($result);
    }

    /**
     * Test getArticleBySlug returns article data
     */
    public function testGetArticleBySlugReturnsArticle(): void
    {
        $slug = 'test-article';
        $expectedArticle = [
            'id' => 1,
            'title' => 'Test Article',
            'slug' => $slug,
            'description' => 'Test description',
            'image_url' => 'https://cloudinary.com/test.jpg',
            'author' => 'Jean Dupont'
        ];

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([':slug' => $slug])
            ->willReturn(true);

        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expectedArticle);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with('SELECT * FROM ARTICLES WHERE slug = :slug')
            ->willReturn($this->mockStmt);

        $result = $this->model->getArticleBySlug($slug);

        $this->assertEquals($expectedArticle, $result);
    }

    /**
     * Test getArticleBySlug returns null on exception
     */
    public function testGetArticleBySlugReturnsNullOnException(): void
    {
        $slug = 'test-article';

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->will($this->throwException(new PDOException('Database error')));

        $result = $this->model->getArticleBySlug($slug);

        $this->assertNull($result);
    }

    /**
     * Test slug generation with only special characters
     */
    public function testSlugWithOnlySpecialCharacters(): void
    {
        $title = '!!!###$$$';
        $description = 'Test';
        $imageUrl = 'https://cloudinary.com/test.jpg';
        $author = 'Test User';

        $checkStmt = $this->createMock(PDOStatement::class);
        $checkStmt->method('execute')->willReturn(true);
        $checkStmt->method('fetchColumn')->willReturn(0);

        $insertStmt = $this->createMock(PDOStatement::class);
        $insertStmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) {
                // Should be empty or just hyphens removed
                return $params[':slug'] === '';
            }))
            ->willReturn(true);

        $this->mockPdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($checkStmt, $insertStmt);

        $result = $this->model->insertArticle(
            $title,
            $description,
            $imageUrl,
            $author
        );

        $this->assertTrue($result);
    }

    /**
     * Test getPaginatedArticles returns correct articles
     */
    public function testGetPaginatedArticlesReturnsArticles(): void
    {
        $expectedArticles = [
            [
                'id' => 1,
                'title' => 'Article 1',
                'slug' => 'article-1',
                'description' => 'Description 1',
                'image_url' => 'https://cloudinary.com/image1.jpg',
                'author' => 'John Doe',
                'created_at' => '2024-01-01 10:00:00'
            ],
            [
                'id' => 2,
                'title' => 'Article 2',
                'slug' => 'article-2',
                'description' => 'Description 2',
                'image_url' => '',
                'author' => 'Jane Smith',
                'created_at' => '2024-01-02 10:00:00'
            ]
        ];

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->exactly(2))
            ->method('bindValue')
            ->willReturn(true);
        $stmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $stmt->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expectedArticles);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $result = $this->model->getPaginatedArticles(0, 10);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals($expectedArticles, $result);
    }

    /**
     * Test getPaginatedArticles returns empty array on error
     */
    public function testGetPaginatedArticlesReturnsEmptyArrayOnError(): void
    {
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->will($this->throwException(new PDOException('Database error')));

        $result = $this->model->getPaginatedArticles(0, 10);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test countArticles returns correct count
     */
    public function testCountArticlesReturnsCorrectCount(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn(['total' => 15]);

        $this->mockPdo->expects($this->once())
            ->method('query')
            ->willReturn($stmt);

        $result = $this->model->countArticles();

        $this->assertEquals(15, $result);
    }

    /**
     * Test countArticles returns 0 on error
     */
    public function testCountArticlesReturnsZeroOnError(): void
    {
        $this->mockPdo->expects($this->once())
            ->method('query')
            ->will($this->throwException(new PDOException('Database error')));

        $result = $this->model->countArticles();

        $this->assertEquals(0, $result);
    }

    /**
     * Test updateArticle updates article with new image
     */
    public function testUpdateArticleWithNewImage(): void
    {
        $articleId = 1;
        $title = 'Article Mis à Jour';
        $description = 'Description mise à jour';
        $imageUrl = 'https://cloudinary.com/new-image.jpg';
        $author = 'Nouvel Auteur';

        // Mock getArticleById to return existing article
        $existingArticle = [
            'id' => $articleId,
            'title' => 'Ancien Titre',
            'slug' => 'ancien-titre',
            'description' => 'Ancienne description',
            'image_url' => 'https://cloudinary.com/old-image.jpg',
            'author' => 'Ancien Auteur'
        ];

        $getStmt = $this->createMock(PDOStatement::class);
        $getStmt->expects($this->once())
            ->method('execute')
            ->with([':id' => $articleId])
            ->willReturn(true);
        $getStmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($existingArticle);

        // Mock slug existence check (slug doesn't exist)
        $checkStmt = $this->createMock(PDOStatement::class);
        $checkStmt->method('execute')->willReturn(true);
        $checkStmt->method('fetchColumn')->willReturn(0);

        // Mock update statement
        $updateStmt = $this->createMock(PDOStatement::class);
        $updateStmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) use ($title, $description, $imageUrl, $author, $articleId) {
                return $params[':title'] === $title &&
                       $params[':slug'] === 'article-mis-a-jour' &&
                       $params[':description'] === $description &&
                       $params[':image_url'] === $imageUrl &&
                       $params[':author'] === $author &&
                       $params[':id'] === $articleId;
            }))
            ->willReturn(true);

        $this->mockPdo->expects($this->exactly(3))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($getStmt, $checkStmt, $updateStmt);

        $result = $this->model->updateArticle(
            $articleId,
            $title,
            $description,
            $imageUrl,
            $author
        );

        $this->assertTrue($result);
    }

    /**
     * Test updateArticle updates article without changing image
     */
    public function testUpdateArticleWithoutNewImage(): void
    {
        $articleId = 1;
        $title = 'Article Mis à Jour';
        $description = 'Description mise à jour';
        $imageUrl = ''; // Empty means keep existing
        $author = 'Nouvel Auteur';

        // Mock getArticleById to return existing article
        $existingArticle = [
            'id' => $articleId,
            'title' => 'Ancien Titre',
            'slug' => 'ancien-titre',
            'description' => 'Ancienne description',
            'image_url' => 'https://cloudinary.com/old-image.jpg',
            'author' => 'Ancien Auteur'
        ];

        $getStmt = $this->createMock(PDOStatement::class);
        $getStmt->expects($this->once())
            ->method('execute')
            ->with([':id' => $articleId])
            ->willReturn(true);
        $getStmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($existingArticle);

        // Mock slug existence check (slug doesn't exist)
        $checkStmt = $this->createMock(PDOStatement::class);
        $checkStmt->method('execute')->willReturn(true);
        $checkStmt->method('fetchColumn')->willReturn(0);

        // Mock update statement (should not include image_url)
        $updateStmt = $this->createMock(PDOStatement::class);
        $updateStmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) use ($title, $description, $author, $articleId) {
                return $params[':title'] === $title &&
                       $params[':slug'] === 'article-mis-a-jour' &&
                       $params[':description'] === $description &&
                       $params[':author'] === $author &&
                       $params[':id'] === $articleId &&
                       !isset($params[':image_url']); // image_url should not be in params
            }))
            ->willReturn(true);

        $this->mockPdo->expects($this->exactly(3))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($getStmt, $checkStmt, $updateStmt);

        $result = $this->model->updateArticle(
            $articleId,
            $title,
            $description,
            $imageUrl,
            $author
        );

        $this->assertTrue($result);
    }

    /**
     * Test updateArticle keeps same slug when title doesn't change
     */
    public function testUpdateArticleKeepsSlugWhenTitleUnchanged(): void
    {
        $articleId = 1;
        $title = 'Même Titre'; // Same as existing
        $description = 'Nouvelle description';
        $imageUrl = '';
        $author = 'Nouvel Auteur';

        // Mock getArticleById to return existing article
        $existingArticle = [
            'id' => $articleId,
            'title' => 'Même Titre',
            'slug' => 'meme-titre',
            'description' => 'Ancienne description',
            'image_url' => 'https://cloudinary.com/image.jpg',
            'author' => 'Ancien Auteur'
        ];

        $getStmt = $this->createMock(PDOStatement::class);
        $getStmt->expects($this->once())
            ->method('execute')
            ->with([':id' => $articleId])
            ->willReturn(true);
        $getStmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($existingArticle);

        // Mock update statement (slug should remain the same)
        $updateStmt = $this->createMock(PDOStatement::class);
        $updateStmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) {
                return $params[':slug'] === 'meme-titre'; // Same slug
            }))
            ->willReturn(true);

        $this->mockPdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($getStmt, $updateStmt);

        $result = $this->model->updateArticle(
            $articleId,
            $title,
            $description,
            $imageUrl,
            $author
        );

        $this->assertTrue($result);
    }

    /**
     * Test updateArticle returns false when article not found
     */
    public function testUpdateArticleReturnsFalseWhenNotFound(): void
    {
        $articleId = 999;

        $getStmt = $this->createMock(PDOStatement::class);
        $getStmt->expects($this->once())
            ->method('execute')
            ->with([':id' => $articleId])
            ->willReturn(true);
        $getStmt->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn(false); // Article not found

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($getStmt);

        $result = $this->model->updateArticle(
            $articleId,
            'Title',
            'Description',
            '',
            'Author'
        );

        $this->assertFalse($result);
    }

    /**
     * Test updateArticle returns false on exception
     */
    public function testUpdateArticleReturnsFalseOnException(): void
    {
        $articleId = 1;

        $getStmt = $this->createMock(PDOStatement::class);
        $getStmt->method('execute')->willReturn(true);
        $getStmt->method('fetch')->willReturn([
            'id' => $articleId,
            'title' => 'Test',
            'slug' => 'test',
            'description' => 'Test',
            'image_url' => '',
            'author' => 'Test'
        ]);

        // Mock slug existence check (slug doesn't exist)
        $checkStmt = $this->createMock(PDOStatement::class);
        $checkStmt->method('execute')->willReturn(true);
        $checkStmt->method('fetchColumn')->willReturn(0);

        $this->mockPdo->expects($this->exactly(3))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls(
                $getStmt,
                $checkStmt,
                $this->throwException(new PDOException('Database error'))
            );

        $result = $this->model->updateArticle(
            $articleId,
            'New Title',
            'New Description',
            '',
            'New Author'
        );

        $this->assertFalse($result);
    }

    /**
     * Test deleteArticle returns true on successful deletion
     */
    public function testDeleteArticleReturnsTrue(): void
    {
        $articleId = 1;

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([':id' => $articleId])
            ->willReturn(true);

        $this->mockStmt->expects($this->once())
            ->method('rowCount')
            ->willReturn(1); // One row deleted

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with('DELETE FROM ARTICLES WHERE id = :id')
            ->willReturn($this->mockStmt);

        $result = $this->model->deleteArticle($articleId);

        $this->assertTrue($result);
    }

    /**
     * Test deleteArticle returns false when article not found
     */
    public function testDeleteArticleReturnsFalseWhenNotFound(): void
    {
        $articleId = 999;

        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([':id' => $articleId])
            ->willReturn(true);

        $this->mockStmt->expects($this->once())
            ->method('rowCount')
            ->willReturn(0); // No rows deleted

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);

        $result = $this->model->deleteArticle($articleId);

        $this->assertFalse($result);
    }

    /**
     * Test deleteArticle returns false on exception
     */
    public function testDeleteArticleReturnsFalseOnException(): void
    {
        $articleId = 1;

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->will($this->throwException(new PDOException('Database error')));

        $result = $this->model->deleteArticle($articleId);

        $this->assertFalse($result);
    }

    /**
     * Test updateArticle generates unique slug when title changes
     */
    public function testUpdateArticleGeneratesUniqueSlugWhenTitleChanges(): void
    {
        $articleId = 1;
        $title = 'Nouveau Titre';
        $description = 'Description';
        $imageUrl = '';
        $author = 'Author';

        // Mock getArticleById
        $existingArticle = [
            'id' => $articleId,
            'title' => 'Ancien Titre',
            'slug' => 'ancien-titre',
            'description' => 'Description',
            'image_url' => '',
            'author' => 'Author'
        ];

        $getStmt = $this->createMock(PDOStatement::class);
        $getStmt->method('execute')->willReturn(true);
        $getStmt->method('fetch')->willReturn($existingArticle);

        // Mock slug existence check - first check returns 1 (exists), second returns 0 (unique)
        $checkStmt1 = $this->createMock(PDOStatement::class);
        $checkStmt1->method('execute')->willReturn(true);
        $checkStmt1->method('fetchColumn')->willReturn(1); // Slug exists

        $checkStmt2 = $this->createMock(PDOStatement::class);
        $checkStmt2->method('execute')->willReturn(true);
        $checkStmt2->method('fetchColumn')->willReturn(0); // Slug doesn't exist

        // Mock update statement
        $updateStmt = $this->createMock(PDOStatement::class);
        $updateStmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) {
                // Should append -2 since first slug exists
                return $params[':slug'] === 'nouveau-titre-2';
            }))
            ->willReturn(true);

        $this->mockPdo->expects($this->exactly(4))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($getStmt, $checkStmt1, $checkStmt2, $updateStmt);

        $result = $this->model->updateArticle(
            $articleId,
            $title,
            $description,
            $imageUrl,
            $author
        );

        $this->assertTrue($result);
    }

    /**
     * Test getLatestArticles returns articles ordered by creation date (newest first)
     */
    public function testGetLatestArticlesReturnsArticles(): void
    {
        $expectedArticles = [
            [
                'id' => 2,
                'title' => 'Article Récent',
                'slug' => 'article-recent',
                'description' => 'Description récente',
                'image_url' => 'https://cloudinary.com/recent.jpg',
                'author' => 'Auteur Récent',
                'created_at' => '2024-01-15 10:00:00'
            ],
            [
                'id' => 1,
                'title' => 'Article Ancien',
                'slug' => 'article-ancien',
                'description' => 'Description ancienne',
                'image_url' => 'https://cloudinary.com/ancien.jpg',
                'author' => 'Auteur Ancien',
                'created_at' => '2024-01-10 10:00:00'
            ]
        ];

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('bindValue')
            ->with(':limit', 2, PDO::PARAM_INT)
            ->willReturn(true);
        $stmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $stmt->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expectedArticles);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('ORDER BY created_at DESC'))
            ->willReturn($stmt);

        $result = $this->model->getLatestArticles(2);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals($expectedArticles, $result);
    }

    /**
     * Test getLatestArticles returns empty array when no articles exist
     */
    public function testGetLatestArticlesReturnsEmptyArrayWhenNoArticles(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('bindValue')
            ->with(':limit', 2, PDO::PARAM_INT)
            ->willReturn(true);
        $stmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $stmt->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn([]);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $result = $this->model->getLatestArticles(2);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test getLatestArticles returns empty array on PDO exception
     */
    public function testGetLatestArticlesReturnsEmptyArrayOnException(): void
    {
        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->will($this->throwException(new PDOException('Database error')));

        $result = $this->model->getLatestArticles(2);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test getLatestArticles uses default limit of 2
     */
    public function testGetLatestArticlesUsesDefaultLimit(): void
    {
        $expectedArticles = [
            [
                'id' => 1,
                'title' => 'Article 1',
                'slug' => 'article-1',
                'description' => 'Description 1',
                'image_url' => 'https://cloudinary.com/image1.jpg',
                'author' => 'Auteur 1',
                'created_at' => '2024-01-01 10:00:00'
            ]
        ];

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('bindValue')
            ->with(':limit', 2, PDO::PARAM_INT)
            ->willReturn(true);
        $stmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $stmt->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expectedArticles);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $result = $this->model->getLatestArticles();

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
    }

    /**
     * Test getLatestArticles respects custom limit parameter
     */
    public function testGetLatestArticlesRespectsCustomLimit(): void
    {
        $expectedArticles = [
            ['id' => 1, 'title' => 'Article 1', 'slug' => 'article-1', 'description' => 'Desc 1', 'image_url' => '', 'author' => 'Auteur 1', 'created_at' => '2024-01-01 10:00:00'],
            ['id' => 2, 'title' => 'Article 2', 'slug' => 'article-2', 'description' => 'Desc 2', 'image_url' => '', 'author' => 'Auteur 2', 'created_at' => '2024-01-02 10:00:00'],
            ['id' => 3, 'title' => 'Article 3', 'slug' => 'article-3', 'description' => 'Desc 3', 'image_url' => '', 'author' => 'Auteur 3', 'created_at' => '2024-01-03 10:00:00']
        ];

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->expects($this->once())
            ->method('bindValue')
            ->with(':limit', 3, PDO::PARAM_INT)
            ->willReturn(true);
        $stmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $stmt->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expectedArticles);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $result = $this->model->getLatestArticles(3);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
    }

    /**
     * Test getLatestArticles selects only necessary columns
     */
    public function testGetLatestArticlesSelectsOnlyNecessaryColumns(): void
    {
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('bindValue')->willReturn(true);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetchAll')->willReturn([]);

        $this->mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->callback(function ($query) {
                // Verify that the query selects only the necessary columns
                return strpos($query, 'SELECT id, title, slug, description, image_url, author, created_at') !== false &&
                       strpos($query, 'FROM ARTICLES') !== false &&
                       strpos($query, 'ORDER BY created_at DESC') !== false &&
                       strpos($query, 'LIMIT :limit') !== false &&
                       strpos($query, 'SELECT *') === false; // Should not use SELECT *
            }))
            ->willReturn($stmt);

        $this->model->getLatestArticles(2);
    }
}
