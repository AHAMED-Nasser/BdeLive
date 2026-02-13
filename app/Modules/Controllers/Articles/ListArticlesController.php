<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Articles;

use App\Modules\Controllers\BaseController;
use App\Modules\Repositories\ArticleRepository;
use App\Modules\Helpers\Pagination;
use App\Core\Database;

/**
 * ListArticlesController - Display paginated list of articles
 *
 * Public page accessible to all visitors (authenticated or not).
 * Shows articles in a responsive grid with pagination.
 * Admin users (BDE) see edit/delete buttons on each article.
 *
 * Refactored to use Data Mapper pattern with Article entities.
 *
 * @package App\Modules\Controllers\Articles
 * @version 2.0.0 - Data Mapper refactoring
 * @author BdeLive Team
 */
class ListArticlesController extends BaseController
{
    /**
     * Constructor - Display articles list with pagination
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $articlesPerPage = 9; // 9 articles par page (grille 3x3)
        $currentPage = max(1, (int)$this->request->get('p', 1));

        $repository = new ArticleRepository(Database::getInstance()->getConnection());
        $totalArticles = $repository->count();

        $pagination = new Pagination($totalArticles, $articlesPerPage, $currentPage);
        $articles = $repository->findPaginated(
            $pagination->getOffset(),
            $articlesPerPage
        );

        $this->render('articles/listArticlesView', [
            'articles' => $articles,
            'pagination' => $pagination
        ]);
    }
}
