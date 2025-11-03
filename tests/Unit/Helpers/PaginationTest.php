<?php

declare(strict_types=1);

namespace App\Tests\Unit\Helpers;

use PHPUnit\Framework\TestCase;
use App\Modules\Helpers\Pagination;

/**
 * Test suite for Pagination helper class
 *
 * @package App\Tests\Unit\Helpers
 */
class PaginationTest extends TestCase
{
    /**
     * Test pagination calculation with default values
     *
     * @return void
     */
    public function testPaginationWithDefaultValues(): void
    {
        $pagination = new Pagination(100, 10, 1);

        $this->assertEquals(10, $pagination->getTotalPages());
        $this->assertEquals(1, $pagination->getCurrentPage());
        $this->assertEquals(0, $pagination->getOffset());
        $this->assertEquals(10, $pagination->getLimit());
        $this->assertEquals(100, $pagination->getTotalItems());
    }

    /**
     * Test pagination calculation with custom values
     *
     * @return void
     */
    public function testPaginationWithCustomValues(): void
    {
        $pagination = new Pagination(45, 10, 3);

        $this->assertEquals(5, $pagination->getTotalPages());
        $this->assertEquals(3, $pagination->getCurrentPage());
        $this->assertEquals(20, $pagination->getOffset());
        $this->assertEquals(10, $pagination->getLimit());
    }

    /**
     * Test pagination with zero items
     *
     * @return void
     */
    public function testPaginationWithZeroItems(): void
    {
        $pagination = new Pagination(0, 10, 1);

        $this->assertEquals(1, $pagination->getTotalPages());
        $this->assertEquals(1, $pagination->getCurrentPage());
        $this->assertEquals(0, $pagination->getOffset());
    }

    /**
     * Test pagination with page exceeding total pages
     *
     * @return void
     */
    public function testPaginationWithPageExceedingTotal(): void
    {
        $pagination = new Pagination(30, 10, 10);

        // Should be clamped to max page (3)
        $this->assertEquals(3, $pagination->getTotalPages());
        $this->assertEquals(3, $pagination->getCurrentPage());
    }

    /**
     * Test pagination with negative page number
     *
     * @return void
     */
    public function testPaginationWithNegativePage(): void
    {
        $pagination = new Pagination(30, 10, -1);

        // Should be clamped to 1
        $this->assertEquals(1, $pagination->getCurrentPage());
    }

    /**
     * Test hasNext method
     *
     * @return void
     */
    public function testHasNext(): void
    {
        $pagination1 = new Pagination(30, 10, 1);
        $this->assertTrue($pagination1->hasNext());

        $pagination2 = new Pagination(30, 10, 3);
        $this->assertFalse($pagination2->hasNext());
    }

    /**
     * Test hasPrevious method
     *
     * @return void
     */
    public function testHasPrevious(): void
    {
        $pagination1 = new Pagination(30, 10, 1);
        $this->assertFalse($pagination1->hasPrevious());

        $pagination2 = new Pagination(30, 10, 2);
        $this->assertTrue($pagination2->hasPrevious());
    }

    /**
     * Test getFirstPage and getLastPage methods
     *
     * @return void
     */
    public function testGetFirstAndLastPage(): void
    {
        $pagination = new Pagination(30, 10, 2);

        $this->assertEquals(1, $pagination->getFirstPage());
        $this->assertEquals(3, $pagination->getLastPage());
    }

    /**
     * Test getLink method
     *
     * @return void
     */
    public function testGetLink(): void
    {
        $_GET = ['page' => 'test', 'p' => '1'];
        $pagination = new Pagination(30, 10, 1);
        $link = $pagination->getLink(2);

        $this->assertStringContainsString('p=2', $link);
        $this->assertStringContainsString('page=test', $link);
    }
}

