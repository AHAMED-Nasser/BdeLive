<?php

declare(strict_types=1);

namespace App\Modules\Helpers;

/**
 * Pagination Helper - Classe réutilisable pour la pagination
 * @package BdeLive\Helpers
 * @version 1.0.0
 */
class Pagination
{
    private int $totalItems;
    private int $itemsPerPage;
    private int $currentPage;
    private int $totalPages;

    public function __construct(int $totalItems, int $itemsPerPage = 10, ?int $currentPage = null)
    {
        $this->totalItems = $totalItems;
        $this->itemsPerPage = $itemsPerPage;
        $this->totalPages = max(1, (int) ceil($this->totalItems / $this->itemsPerPage));

        if ($currentPage === null) {
            $currentPage = (int) ($_GET['p'] ?? 1);
        }
        $this->currentPage = max(1, min($currentPage, $this->totalPages));
    }

    public function getOffset(): int
    {
        return ($this->currentPage - 1) * $this->itemsPerPage;
    }

    public function getLimit(): int
    {
        return $this->itemsPerPage;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    public function getTotalItems(): int
    {
        return $this->totalItems;
    }

    public function hasNext(): bool
    {
        return $this->currentPage < $this->totalPages;
    }

    public function hasPrevious(): bool
    {
        return $this->currentPage > 1;
    }

    public function getFirstPage(): int
    {
        return 1;
    }

    public function getLastPage(): int
    {
        return $this->totalPages;
    }

    public function getLink(int $pageNumber): string
    {
        $params = $_GET;
        $params['p'] = $pageNumber;
        return 'index.php?' . http_build_query($params);
    }
}

\class_alias(__NAMESPACE__ . '\\Pagination', 'Pagination');
