<?php

declare(strict_types=1);

namespace Tests\Unit\Helpers;

use PHPUnit\Framework\TestCase;
use App\Modules\Helpers\SlugGenerator;

/**
 * Unit tests for SlugGenerator
 */
class SlugGeneratorTest extends TestCase
{
    /**
     * Test basic slug generation
     */
    public function testGenerateBasicSlug(): void
    {
        $result = SlugGenerator::generate('Mon Premier Article');
        $this->assertEquals('mon-premier-article', $result);
    }

    /**
     * Test slug generation with accents
     */
    public function testGenerateSlugWithAccents(): void
    {
        $result = SlugGenerator::generate('Article 2024');
        $this->assertEquals('article-2024', $result);
    }

    /**
     * Test slug generation with special characters
     */
    public function testGenerateSlugWithSpecialCharacters(): void
    {
        $result = SlugGenerator::generate('Article! Spécial? #2024');
        $this->assertEquals('article-special-2024', $result);
    }

    /**
     * Test slug generation with multiple spaces
     */
    public function testGenerateSlugWithMultipleSpaces(): void
    {
        $result = SlugGenerator::generate('Article   avec    espaces');
        $this->assertEquals('article-avec-espaces', $result);
    }

    /**
     * Test slug generation with leading/trailing spaces
     */
    public function testGenerateSlugWithLeadingTrailingSpaces(): void
    {
        $result = SlugGenerator::generate('  Article Test  ');
        $this->assertEquals('article-test', $result);
    }

    /**
     * Test slug generation with uppercase
     */
    public function testGenerateSlugWithUppercase(): void
    {
        $result = SlugGenerator::generate('ARTICLE EN MAJUSCULES');
        $this->assertEquals('article-en-majuscules', $result);
    }

    /**
     * Test slug generation with mixed case
     */
    public function testGenerateSlugWithMixedCase(): void
    {
        $result = SlugGenerator::generate('ArTiClE MiXeD CaSe');
        $this->assertEquals('article-mixed-case', $result);
    }

    /**
     * Test slug generation with numbers
     */
    public function testGenerateSlugWithNumbers(): void
    {
        $result = SlugGenerator::generate('Article 2024 Version 2');
        $this->assertEquals('article-2024-version-2', $result);
    }

    /**
     * Test unique slug generation when slug doesn't exist
     */
    public function testGenerateUniqueSlugWhenNotExists(): void
    {
        $existsCallback = function (string $slug): bool {
            return false; // Slug never exists
        };

        $result = SlugGenerator::generateUnique('Mon Article', $existsCallback);
        $this->assertEquals('mon-article', $result);
    }

    /**
     * Test unique slug generation when slug exists once
     */
    public function testGenerateUniqueSlugWhenExistsOnce(): void
    {
        $counter = 0;
        $existsCallback = function (string $slug) use (&$counter): bool {
            $counter++;
            return $counter === 1; // First slug exists, second doesn't
        };

        $result = SlugGenerator::generateUnique('Mon Article', $existsCallback);
        $this->assertEquals('mon-article-2', $result);
    }

    /**
     * Test unique slug generation when slug exists multiple times
     */
    public function testGenerateUniqueSlugWhenExistsMultipleTimes(): void
    {
        $counter = 0;
        $existsCallback = function (string $slug) use (&$counter): bool {
            $counter++;
            return $counter <= 3; // First 3 slugs exist, 4th doesn't
        };

        $result = SlugGenerator::generateUnique('Mon Article', $existsCallback);
        $this->assertEquals('mon-article-4', $result);
    }

    /**
     * Test isValid with valid slug
     */
    public function testIsValidWithValidSlug(): void
    {
        $this->assertTrue(SlugGenerator::isValid('mon-article'));
        $this->assertTrue(SlugGenerator::isValid('article-2024'));
        $this->assertTrue(SlugGenerator::isValid('test'));
    }

    /**
     * Test isValid with invalid slug (uppercase)
     */
    public function testIsValidWithUppercase(): void
    {
        $this->assertFalse(SlugGenerator::isValid('Mon-Article'));
    }

    /**
     * Test isValid with invalid slug (special characters)
     */
    public function testIsValidWithSpecialCharacters(): void
    {
        $this->assertFalse(SlugGenerator::isValid('mon-article!'));
        $this->assertFalse(SlugGenerator::isValid('article@test'));
    }

    /**
     * Test isValid with invalid slug (leading hyphen)
     */
    public function testIsValidWithLeadingHyphen(): void
    {
        $this->assertFalse(SlugGenerator::isValid('-mon-article'));
    }

    /**
     * Test isValid with invalid slug (trailing hyphen)
     */
    public function testIsValidWithTrailingHyphen(): void
    {
        $this->assertFalse(SlugGenerator::isValid('mon-article-'));
    }

    /**
     * Test isValid with invalid slug (consecutive hyphens)
     */
    public function testIsValidWithConsecutiveHyphens(): void
    {
        $this->assertFalse(SlugGenerator::isValid('mon--article'));
    }

    /**
     * Test slug generation with empty string
     */
    public function testGenerateSlugWithEmptyString(): void
    {
        $result = SlugGenerator::generate('');
        $this->assertEquals('', $result);
    }

    /**
     * Test slug generation with only special characters
     */
    public function testGenerateSlugWithOnlySpecialCharacters(): void
    {
        $result = SlugGenerator::generate('!@#$%^&*()');
        $this->assertEquals('', $result);
    }

    /**
     * Test slug generation with French accents
     */
    public function testGenerateSlugWithFrenchAccents(): void
    {
        $result = SlugGenerator::generate('Café à Paris près de l\'église');
        $this->assertEquals('cafe-a-paris-pres-de-l-eglise', $result);
    }
}
