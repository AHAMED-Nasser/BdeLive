<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Articles;

use App\Modules\Controllers\BaseController;
use App\Modules\Models\Admin\ArticleModel;
use App\Modules\Helpers\Pagination;

/**
 * ListArticlesController - Display paginated list of articles
 *
 * Public page accessible to all visitors (authenticated or not).
 * Shows articles in a responsive grid with pagination.
 * Admin users (BDE) see edit/delete buttons on each article.
 *
 * @package App\Modules\Controllers\Articles
 * @version 1.0.0
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

        $articleModel = new ArticleModel();
        $totalArticles = $articleModel->countArticles();

        $pagination = new Pagination($totalArticles, $articlesPerPage, $currentPage);
        $articles = $articleModel->getPaginatedArticles(
            $pagination->getOffset(),
            $articlesPerPage
        );

        $this->render('articles/listArticlesView', [
            'articles' => $articles,
            'pagination' => $pagination
        ]);
    }
}
