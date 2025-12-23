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
        $authorFirstname = 'Jean';
        $authorLastname = 'Dupont';

        // Mock slug existence check (slug doesn't exist)
        $checkStmt = $this->createMock(PDOStatement::class);
        $checkStmt->method('execute')->willReturn(true);
        $checkStmt->method('fetchColumn')->willReturn(0);

        $insertStmt = $this->createMock(PDOStatement::class);
        $insertStmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) use ($title, $description, $imageUrl, $authorFirstname, $authorLastname) {
                return $params[':title'] === $title &&
                       $params[':slug'] === 'mon-premier-article' &&
                       $params[':description'] === $description &&
                       $params[':image_url'] === $imageUrl &&
                       $params[':author_firstname'] === $authorFirstname &&
                       $params[':author_lastname'] === $authorLastname;
            }))
            ->willReturn(true);

        $this->mockPdo->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($checkStmt, $insertStmt);

        $result = $this->model->insertArticle(
            $title,
            $description,
            $imageUrl,
            $authorFirstname,
            $authorLastname
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
        $authorFirstname = 'Marie';
        $authorLastname = 'Martin';

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
            $authorFirstname,
            $authorLastname
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
        $authorFirstname = 'Paul';
        $authorLastname = 'Durand';

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
            $authorFirstname,
            $authorLastname
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
        $authorFirstname = 'Sophie';
        $authorLastname = 'Bernard';

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
            $authorFirstname,
            $authorLastname
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
        $authorFirstname = 'Test';
        $authorLastname = 'User';

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
            $authorFirstname,
            $authorLastname
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
            'author_firstname' => 'Jean',
            'author_lastname' => 'Dupont',
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
            'author_firstname' => 'Jean',
            'author_lastname' => 'Dupont'
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
        $authorFirstname = 'Test';
        $authorLastname = 'User';

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
            $authorFirstname,
            $authorLastname
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
                'author_firstname' => 'John',
                'author_lastname' => 'Doe',
                'created_at' => '2024-01-01 10:00:00'
            ],
            [
                'id' => 2,
                'title' => 'Article 2',
                'slug' => 'article-2',
                'description' => 'Description 2',
                'image_url' => '',
                'author_firstname' => 'Jane',
                'author_lastname' => 'Smith',
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

}

