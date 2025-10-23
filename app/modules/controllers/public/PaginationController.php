<?php

declare(strict_types=1);

/**
 * Pagination Controller - PHP 8 optimized
 *
 * @package BdeLive\Controllers
 * @version 2.0.0
 */
class PaginationController
{
    private const ITEMS_PER_PAGE = 4;

    public function __construct()
    {
        $model = new PaginationModel();
        $currentPage = max(1, (int)($_GET['p'] ?? 1));
        $offset = ($currentPage - 1) * self::ITEMS_PER_PAGE;

        $paginationData = [
            'items' => $model->getPaginatedData($offset, self::ITEMS_PER_PAGE),
            'currentPage' => $currentPage,
            'totalPages' => (int) ceil($model->getTotalItems() / self::ITEMS_PER_PAGE),
            'totalItems' => $model->getTotalItems(),
        ];

        // Make variables available to the view
        $current = $currentPage;
        
        // Extract pagination data for the view
        extract($paginationData);

        require __DIR__ . '/../../views/public/paginationView.php';
    }
}
