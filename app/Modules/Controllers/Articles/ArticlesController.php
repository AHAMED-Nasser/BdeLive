<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Articles;

use App\Modules\Controllers\BaseController;
use App\Modules\Helpers\Pagination;
use App\Modules\Repositories\ArticleRepository;

/**
 * ArticlesController - Display articles list or single article
 *
 * Handles both:
 * - List: index.php?page=articles (displays paginated list)
 * - Detail: index.php?page=articles&slug=... (displays single article)
 *
 * Refactored to use Data Mapper + constructor injection (ArticleRepository).
 *
 * @package App\Modules\Controllers\Articles
 * @version 2.0.0 - Data Mapper + DI
 * @author BDELIVE - Group 8
 */
class ArticlesController extends BaseController
{
    private ArticleRepository $repository;

    public function __construct(ArticleRepository $repository)
    {
        parent::__construct();
        $this->repository = $repository;

        $slug = $this->request->get('slug', '');
        $articleId = (int) $this->request->get('id', 0);

        // Priority 1: Slug (SEO-friendly URL)
        if (!empty($slug)) {
            $this->displaySingleArticle((string) $slug);
            return;
        }

        // Priority 2: ID (legacy, redirect to slug for SEO)
        if ($articleId > 0) {
            $article = $this->repository->findById($articleId);

            if ($article) {
                // 301 Permanent Redirect to slug URL for SEO
                $slugUrl = 'index.php?page=articles&slug=' . urlencode($article->getSlug());
                header('Location: ' . $slugUrl, true, 301);
                exit;
            }

            $this->setError('Article introuvable.');
            $this->redirect('index.php?page=articles');
        }

        // Otherwise, display articles list
        $this->displayArticlesList();
    }

    /**
     * Display a single article by slug
     *
     * @param string $slug Article slug
     * @return void
     */
    private function displaySingleArticle(string $slug): void
    {
        $article = $this->repository->findBySlug($slug);

        if ($article === null) {
            $this->setError('Article introuvable.');
            $this->redirect('index.php?page=articles');
        }

        $this->render('articles/articleView', [
            'article' => $article
        ]);
    }

    /**
     * Display paginated articles list
     *
     * @return void
     */
    private function displayArticlesList(): void
    {
        $articlesPerPage = 9; // 9 articles par page (grille 3x3)
        $currentPage = max(1, (int) $this->request->get('p', 1));

        $totalArticles = $this->repository->count();
        $pagination = new Pagination($totalArticles, $articlesPerPage, $currentPage);
        $articles = $this->repository->findPaginated(
            $pagination->getOffset(),
            $articlesPerPage
        );

        $this->render('articles/listArticlesView', [
            'articles' => $articles,
            'pagination' => $pagination
        ]);
    }
}
