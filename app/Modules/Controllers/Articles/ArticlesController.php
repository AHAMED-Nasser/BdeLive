<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Articles;

use App\Modules\Controllers\BaseController;
use App\Modules\Models\Articles\ArticleModel;
use App\Modules\Helpers\Pagination;
use App\Core\Database;

/**
 * ArticlesController - Display articles list or single article
 *
 * Handles both:
 * - List: index.php?page=articles (displays paginated list)
 * - Detail: index.php?page=articles&slug=... (displays single article)
 *
 * @package App\Modules\Controllers\Articles
 * @version 1.0.0
 * @author BDELIVE - Group 8
 */
class ArticlesController extends BaseController
{
    /**
     * Constructor - Display articles list or single article
     *
     * Priority handling:
     * 1. If 'slug' parameter is present, displays single article (SEO-friendly)
     * 2. If only 'id' parameter is present, redirects 301 to slug URL (backward compatibility)
     * 3. Otherwise, displays paginated list
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $slug = $this->request->get('slug', '');
        $articleId = (int) $this->request->get('id', 0);

        // Priority 1: Slug (SEO-friendly URL)
        if (!empty($slug)) {
            $this->displaySingleArticle((string) $slug);
            return;
        }

        // Priority 2: ID (legacy, redirect to slug for SEO)
        if ($articleId > 0) {
            $articleModel = new ArticleModel(Database::getInstance()->getConnection());
            $article = $articleModel->getArticleById($articleId);

            if ($article && isset($article['slug'])) {
                // 301 Permanent Redirect to slug URL for SEO
                $slugUrl = 'index.php?page=articles&slug=' . urlencode($article['slug']);
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
        $articleModel = new ArticleModel(Database::getInstance()->getConnection());
        $article = $articleModel->getArticleBySlug($slug);

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

        $articleModel = new ArticleModel(Database::getInstance()->getConnection());
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
