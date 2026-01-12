<?php

declare(strict_types=1);

namespace App\Modules\Helpers;

use App\Core\Application;

/**
 * Pagination Helper - Reusable pagination class
 *
 * Provides pagination calculations and utilities for displaying paginated content.
 * Handles offset/limit calculations, page navigation, and URL generation.
 *
 * @package BdeLive\Helpers
 * @version 1.0.0
 */
class Pagination
{
    /**
     * Total number of items to paginate
     *
     * @var int
     */
    private int $totalItems;

    /**
     * Number of items per page
     *
     * @var int
     */
    private int $itemsPerPage;

    /**
     * Current page number
     *
     * @var int
     */
    private int $currentPage;

    /**
     * Total number of pages
     *
     * @var int
     */
    private int $totalPages;

    /**
     * Constructor - Initialize pagination
     *
     * Calculates total pages and validates current page number.
     * If current page is not provided, it reads from $_GET['p'] or defaults to 1.
     *
     * @param int $totalItems Total number of items to paginate
     * @param int $itemsPerPage Number of items per page (default: 10)
     * @param int|null $currentPage Current page number (null to read from $_GET['p'])
     * @return void
     */
    public function __construct(int $totalItems, int $itemsPerPage = 10, ?int $currentPage = null)
    {
        $this->totalItems = $totalItems;
        $this->itemsPerPage = $itemsPerPage;
        $this->totalPages = max(1, (int) ceil($this->totalItems / $this->itemsPerPage));

        if ($currentPage === null) {
            $currentPage = (int) (Application::getInstance()->request()->get('p', 1));
        }
        $this->currentPage = max(1, min($currentPage, $this->totalPages));
    }

    /**
     * Get the offset for database queries
     *
     * Calculates the OFFSET value for SQL LIMIT queries.
     *
     * @return int The offset value (number of items to skip)
     */
    public function getOffset(): int
    {
        return ($this->currentPage - 1) * $this->itemsPerPage;
    }

    /**
     * Get the limit for database queries
     *
     * Returns the number of items per page (LIMIT value for SQL).
     *
     * @return int The limit value (number of items per page)
     */
    public function getLimit(): int
    {
        return $this->itemsPerPage;
    }

    /**
     * Get the current page number
     *
     * @return int Current page number (1-based)
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * Get the total number of pages
     *
     * @return int Total number of pages
     */
    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    /**
     * Get the total number of items
     *
     * @return int Total number of items
     */
    public function getTotalItems(): int
    {
        return $this->totalItems;
    }

    /**
     * Check if there is a next page
     *
     * @return bool True if there is a next page, false otherwise
     */
    public function hasNext(): bool
    {
        return $this->currentPage < $this->totalPages;
    }

    /**
     * Check if there is a previous page
     *
     * @return bool True if there is a previous page, false otherwise
     */
    public function hasPrevious(): bool
    {
        return $this->currentPage > 1;
    }

    /**
     * Get the first page number
     *
     * @return int Always returns 1
     */
    public function getFirstPage(): int
    {
        return 1;
    }

    /**
     * Get the last page number
     *
     * @return int Last page number (total pages)
     */
    public function getLastPage(): int
    {
        return $this->totalPages;
    }

    /**
     * Generate a URL link for a specific page number
     *
     * Preserves existing query parameters and adds/updates the 'p' parameter.
     *
     * @param int $pageNumber The page number to generate a link for
     * @return string URL with query parameters including the page number
     */
    public function getLink(int $pageNumber): string
    {
        $params = Application::getInstance()->request()->getQuery();
        $params['p'] = $pageNumber;
        return 'index.php?' . http_build_query($params);
    }
}
