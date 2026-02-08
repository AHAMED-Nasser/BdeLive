<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Articles;

use App\Modules\Controllers\AdminController;
use App\Modules\Repositories\Interfaces\ArticleRepositoryInterface;
use App\Modules\Repositories\ArticleRepository;
use App\Services\CloudinaryService;
use App\Core\Database;

/**
 * UpdateArticleController - Article Update for Administrators
 *
 * Handles the update of existing articles with image upload to Cloudinary.
 * Only accessible to users with BDE (admin) status.
 *
 * Refactored to use Data Mapper pattern with Article entities and ArticleRepositoryInterface.
 *
 * Features:
 * - Article form display with pre-filled data
 * - Form validation (CSRF, required fields)
 * - Optional image upload to Cloudinary (keeps existing if not provided)
 * - Article data update via repository
 * - Success/error feedback with flash messages
 *
 * @package App\Modules\Controllers\Articles
 * @version 2.0.0 - Data Mapper refactoring
 * @author BdeLive Team
 *
 * @see AdminController For admin authentication requirements
 * @see ArticleRepository For database operations
 * @see CloudinaryService For image upload handling
 */
class UpdateArticleController extends AdminController
{
    /**
     * Constructor - Handle article update form display and submission
     *
     * GET request: Displays the article update form with pre-filled data
     * POST request with action=submitUpdate: Processes the form submission
     *
     * Requires 'slug' query parameter to identify the article to update.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $action = $this->request->get('action', '');
        if ($this->request->isPost() && $action === 'submitUpdate') {
            $this->updateArticle();
        } else {
            $this->displayUpdateForm();
        }
    }

    /**
     * Display the article update form with pre-filled data
     *
     * Retrieves the article by slug and renders the update form.
     * Redirects to home if article not found or slug is invalid.
     *
     * @return void
     */
    private function displayUpdateForm(): void
    {
        $article = $this->getArticleFromRequest();

        if ($article === null) {
            $this->setError('Article introuvable.');
            $this->redirect('index.php?page=home');
        }

        $this->render('articles/updateArticleView', [
            'article' => $article
        ]);
    }

    /**
     * Update an existing article
     *
     * Validates form data, optionally uploads new image to Cloudinary,
     * and updates article in database. Redirects back to form on validation
     * error or to home on success.
     *
     * Required form fields:
     * - article-title: Article title
     * - article-description: Article content/description
     * - author: Author's full name
     * - article-image: Image file (optional, only updates if provided)
     *
     * @return void Redirects to appropriate page with flash message
     */
    private function updateArticle(): void
    {
        $article = $this->getArticleFromRequest();

        if ($article === null) {
            $this->setError('Article introuvable.');
            $this->redirect('index.php?page=home');
        }

        $articleId = $article->getId();
        $slug = $article->getSlug();


        $skipCsrfValidation = false;

        // Validate CSRF token
        /** @phpstan-ignore-next-line */
        if (!$skipCsrfValidation) {
            $csrfToken = $this->request->post('csrf_token', '');

            if (!$this->csrf->validateToken((string) $csrfToken)) {
                $this->setError('Token de sécurité invalide. Veuillez réessayer.');
                $this->redirect('index.php?page=updateArticle&slug=' . urlencode($slug));
            }
        }

        // Get form data using Request object (not superglobals)
        $title = (string) $this->request->post('article-title', '');
        $description = (string) $this->request->post('article-description', '');
        $author = (string) $this->request->post('author', '');

        // Validate required fields
        if (empty($title) || empty($description) || empty($author)) {
            $this->setError('Tous les champs sont obligatoires.');
            $this->redirect('index.php?page=updateArticle&slug=' . urlencode($slug));
        }

        // Gestion de la suppression d'image (checkbox)
        $deleteImage = $this->request->post('delete-image', '0') === '1';

        // Upload image to Cloudinary (optional - only if new image provided)
        $imageUrl = '';
        $file = $this->request->file('article-image');

        // Si suppression demandée, on met une valeur spéciale
        if ($deleteImage) {
            $imageUrl = 'DELETE'; // Valeur spéciale pour indiquer la suppression
        } elseif ($file !== null && !empty($file['name']) && !empty($file['tmp_name'])) {
            // Upload seulement si une image est fournie
            try {
                $cloudinary = new CloudinaryService();
                /** @var array{name: string, type: string, tmp_name: string, error: int, size: int} $file */
                $uploadedImage = $cloudinary->uploadImage($file, 'articles');

                if ($uploadedImage === null) {
                    error_log('UpdateArticleController::updateArticle - Image upload failed');
                    $this->setError('Erreur lors de l\'upload de l\'image. Veuillez réessayer.');
                    $this->redirect('index.php?page=updateArticle&slug=' . urlencode($slug));
                }

                $imageUrl = $uploadedImage['url'];
            } catch (\Exception $e) {
                error_log('UpdateArticleController::updateArticle - Cloudinary error: ' . $e->getMessage());
                $this->setError('Erreur lors de l\'upload de l\'image. Veuillez réessayer.');
                $this->redirect('index.php?page=updateArticle&slug=' . urlencode($slug));
            }
        }

        // Update article entity with new values using setters
        $article->setTitle($title);
        $article->setDescription($description);
        $article->setAuthor($author);

        // Handle image URL update
        if ($deleteImage) {
            $article->setImageUrl(null);
        } elseif (!empty($imageUrl)) {
            $article->setImageUrl($imageUrl);
        }
        // If imageUrl is empty and deleteImage is false, keep existing image

        // Save via repository (will detect update because entity has ID)
        /** @var ArticleRepositoryInterface $repository */
        $repository = new ArticleRepository(Database::getInstance()->getConnection());
        $result = $repository->save($article);

        if ($result) {
            $this->setSuccess('Article modifié avec succès.');
            $this->redirect('index.php?page=home');
        } else {
            $this->setError('Une erreur est survenue lors de la modification de l\'article.');
            $this->redirect('index.php?page=updateArticle&slug=' . urlencode($slug));
        }
    }

    /**
     * Get article from request using slug
     *
     * Retrieves the article slug from GET or POST parameters and fetches the article.
     * Supports both 'slug' and 'id' parameters for backward compatibility.
     *
     * @return \App\Modules\Entities\Article|null Article entity if found, null otherwise
     */
    private function getArticleFromRequest(): ?\App\Modules\Entities\Article
    {
        /** @var ArticleRepositoryInterface $repository */
        $repository = new ArticleRepository(Database::getInstance()->getConnection());

        // Try to get slug first (preferred method)
        $slug = $this->request->get('slug', '');
        if (!empty($slug)) {
            $article = $repository->findBySlug((string) $slug);
            if ($article !== null) {
                return $article;
            }
        }

        // Fallback to ID for backward compatibility
        $id = $this->request->get('id', '') ?: $this->request->post('article-id', '');
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
