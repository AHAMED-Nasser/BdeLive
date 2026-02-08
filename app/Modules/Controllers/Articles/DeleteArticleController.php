<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Articles;

use App\Modules\Controllers\AdminController;
use App\Modules\Repositories\Interfaces\ArticleRepositoryInterface;
use App\Modules\Repositories\ArticleRepository;
use App\Core\Database;

/**
 * DeleteArticleController - Article Deletion for Administrators
 *
 * Handles the deletion of existing articles.
 * Only accessible to users with BDE (admin) status.
 *
 * Refactored to use Data Mapper pattern with ArticleRepositoryInterface.
 *
 * Features:
 * - CSRF token validation
 * - Article deletion by slug or ID
 * - Success/error feedback with flash messages
 * - Redirects to articles list after deletion
 *
 * @package App\Modules\Controllers\Articles
 * @version 2.0.0 - Data Mapper refactoring
 * @author BDELIVE - Group 8
 *
 * @see AdminController For admin authentication requirements
 * @see ArticleRepository For database operations
 */
class DeleteArticleController extends AdminController
{
    /**
     * Constructor - Handle article deletion
     *
     * POST request with action=deleteArticle: Processes the deletion
     * Requires 'slug' or 'id' parameter to identify the article to delete.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $action = $this->request->get('action', '');
        if ($this->request->isPost() && $action === 'deleteArticle') {
            $this->deleteArticle();
        } else {
            // If accessed via GET, redirect to articles list
            $this->setError('Méthode non autorisée.');
            $this->redirect('index.php?page=articles');
        }
    }

    /**
     * Delete an existing article
     *
     * Validates CSRF token, retrieves the article, and deletes it from database.
     * Redirects to articles list with success or error message.
     *
     * @return void Redirects to articles list with flash message
     */
    private function deleteArticle(): void
    {
        // ====================================================================
        // Code CSRF to be corrected
        // ====================================================================
        // CSRF validation temporarily disabled
        // Problem identified: CSRF token not retrieved correctly with multipart/form-data
        // when uploading files. Permanent solution to be implemented in S4
        // ====================================================================

        // Temporary flag to disable CSRF validation

        $skipCsrfValidation = false; // To be set to false after the problem has been corrected.

        // Validate CSRF token
        /** @phpstan-ignore-next-line */
        if (!$skipCsrfValidation) {
            $csrfToken = $this->request->post('csrf_token', '');

            if (!$this->csrf->validateToken((string) $csrfToken)) {
                $this->setError('Token de sécurité invalide. Veuillez réessayer.');
                $this->redirect('index.php?page=createArticle');
            }
        }

        // Get article from request
        $article = $this->getArticleFromRequest();

        if ($article === null) {
            $this->setError('Article introuvable.');
            $this->redirect('index.php?page=articles');
        }

        $articleId = $article->getId();
        if ($articleId === null) {
            $this->setError('Article introuvable.');
            $this->redirect('index.php?page=articles');
        }

        // Delete article via repository
        /** @var ArticleRepositoryInterface $repository */
        $repository = new ArticleRepository(Database::getInstance()->getConnection());
        $result = $repository->delete($articleId);

        if ($result) {
            $this->setSuccess('Article supprimé avec succès.');
            $this->redirect('index.php?page=articles');
        } else {
            $this->setError('Une erreur est survenue lors de la suppression de l\'article.');
            $this->redirect('index.php?page=articles');
        }
    }

    /**
     * Get article from request using slug or ID
     *
     * Retrieves the article slug or ID from GET or POST parameters and fetches the article.
     * Supports both 'slug' and 'id' parameters.
     *
     * @return \App\Modules\Entities\Article|null Article entity if found, null otherwise
     */
    private function getArticleFromRequest(): ?\App\Modules\Entities\Article
    {
        /** @var ArticleRepositoryInterface $repository */
        $repository = new ArticleRepository(Database::getInstance()->getConnection());

        // Try to get slug first (preferred method)
        $slug = $this->request->get('slug', '') ?: $this->request->post('slug', '');
        if (!empty($slug)) {
            $article = $repository->findBySlug((string) $slug);
            if ($article !== null) {
                return $article;
            }
        }

        // Fallback to ID
        $id = $this->request->get('id', '') ?: $this->request->post('id', '');
        if (!empty($id)) {
            $articleId = filter_var($id, FILTER_VALIDATE_INT);
            if ($articleId !== false && $articleId > 0) {
                $article = $repository->findById($articleId);
                if ($article !== null) {
                    return $article;
                }
            }
        }

        return null;
    }
}
