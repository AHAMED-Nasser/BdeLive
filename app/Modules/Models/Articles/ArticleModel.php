<?php

declare(strict_types=1);

namespace App\Modules\Models\Articles;

use PDO;
use PDOException;
use App\Modules\Helpers\SlugGenerator;

/**
 * ArticleModel - Modèle unifié pour la gestion des articles
 *
 * Ce modèle centralise toutes les opérations sur les articles (lecture et écriture)
 * conformément au pattern Repository enseigné dans le cours (CM4 Slide 22).
 *
 * Responsabilités :
 * - Lecture : Récupération des articles avec pagination, recherche par slug/ID
 * - Écriture : Création, modification et suppression d'articles
 * - Gestion automatique des slugs uniques
 *
 * @package BdeLive\Models\Articles
 * @author BdeLive - Group 8
 * @version 2.0.0
 */
class ArticleModel
{
    /**
     * Instance de connexion PDO à la base de données
     *
     * @var PDO
     */
    private PDO $pdo;

    /**
     * Constructeur - Injection de dépendance PDO
     *
     * Conformément aux bonnes pratiques (CM4 Slide 22), la connexion PDO
     * est injectée via le constructeur pour faciliter les tests et respecter
     * le principe d'inversion de dépendances.
     *
     * @param PDO $pdo Instance de connexion à la base de données
     * @return void
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // =========================================================================
    // MÉTHODES D'ÉCRITURE (Write)
    // =========================================================================

    /**
     * Insérer un nouvel article dans la base de données
     *
     * Crée un nouvel enregistrement d'article avec toutes les informations fournies.
     * Génère automatiquement un slug unique à partir du titre.
     *
     * @param string $title Titre de l'article
     * @param string $description Description/contenu de l'article
     * @param string $imageUrl URL de l'image Cloudinary
     * @param string $author Nom complet de l'auteur
     * @return bool True si l'insertion réussit, false sinon
     */
    public function insertArticle(
        string $title,
        string $description,
        string $imageUrl,
        string $author
    ): bool {
        try {
            // Génération d'un slug unique à partir du titre
            $slug = $this->generateUniqueSlug($title);

            $query = "INSERT INTO ARTICLES (title, slug, description, image_url, author) 
                      VALUES (:title, :slug, :description, :image_url, :author)";

            $stmt = $this->pdo->prepare($query);

            return $stmt->execute([
                ':title' => $title,
                ':slug' => $slug,
                ':description' => $description,
                ':image_url' => $imageUrl,
                ':author' => $author
            ]);
        } catch (PDOException $e) {
            error_log('ArticleModel::insertArticle - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Mettre à jour un article existant dans la base de données
     *
     * Met à jour les informations d'un article existant identifié par son ID.
     * Si le titre a changé, génère un nouveau slug unique.
     * L'URL de l'image n'est mise à jour que si une nouvelle image est fournie.
     *
     * @param int $articleId ID de l'article à mettre à jour
     * @param string $title Nouveau titre de l'article
     * @param string $description Nouvelle description/contenu
     * @param string $imageUrl Nouvelle URL d'image Cloudinary (chaîne vide pour conserver l'existante)
     * @param string $author Nouveau nom d'auteur
     * @return bool True si la mise à jour réussit, false sinon
     */
    public function updateArticle(
        int $articleId,
        string $title,
        string $description,
        string $imageUrl,
        string $author
    ): bool {
        try {
            // Récupération de l'article actuel pour vérifier si le titre a changé
            $currentArticle = $this->getArticleById($articleId);
            if ($currentArticle === null) {
                error_log('ArticleModel::updateArticle - Article not found: ' . $articleId);
                return false;
            }

            // Génération d'un nouveau slug si le titre a changé
            $slug = $currentArticle['slug'];
            if ($currentArticle['title'] !== $title) {
                $slug = $this->generateUniqueSlugForUpdate($title, $articleId);
            }

            // Construction de la requête selon le traitement de l'image
            if ($imageUrl === 'DELETE') {
                $query = "UPDATE ARTICLES 
                         SET title = :title, slug = :slug, description = :description, 
                             image_url = NULL, author = :author 
                         WHERE id = :id";
                $params = [
                    ':title' => $title,
                    ':slug' => $slug,
                    ':description' => $description,
                    ':author' => $author,
                    ':id' => $articleId
                ];
            } elseif (!empty($imageUrl)) {
                $query = "UPDATE ARTICLES 
                         SET title = :title, slug = :slug, description = :description, 
                             image_url = :image_url, author = :author 
                         WHERE id = :id";
                $params = [
                    ':title' => $title,
                    ':slug' => $slug,
                    ':description' => $description,
                    ':image_url' => $imageUrl,
                    ':author' => $author,
                    ':id' => $articleId
                ];
            } else {
                $query = "UPDATE ARTICLES 
                         SET title = :title, slug = :slug, description = :description, 
                             author = :author 
                         WHERE id = :id";
                $params = [
                    ':title' => $title,
                    ':slug' => $slug,
                    ':description' => $description,
                    ':author' => $author,
                    ':id' => $articleId
                ];
            }

            $stmt = $this->pdo->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log('ArticleModel::updateArticle - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprimer un article de la base de données
     *
     * Supprime définitivement un article identifié par son ID.
     * Retourne true si la suppression a réussi, false sinon.
     *
     * @param int $articleId ID de l'article à supprimer
     * @return bool True si la suppression réussit, false sinon
     */
    public function deleteArticle(int $articleId): bool
    {
        try {
            $query = "DELETE FROM ARTICLES WHERE id = :id";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([':id' => $articleId]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('ArticleModel::deleteArticle - ' . $e->getMessage());
            return false;
        }
    }

    // =========================================================================
    // MÉTHODES DE LECTURE (Read)
    // =========================================================================

    /**
     * Récupérer un article par son identifiant
     *
     * @param int $articleId Identifiant unique de l'article
     * @return array<string, mixed>|null Données de l'article ou null si non trouvé
     */
    public function getArticleById(int $articleId): ?array
    {
        try {
            $query = "SELECT * FROM ARTICLES WHERE id = :id";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([':id' => $articleId]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result !== false ? $result : null;
        } catch (PDOException $e) {
            error_log('ArticleModel::getArticleById - ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupérer un article par son slug
     *
     * @param string $slug Slug unique de l'article
     * @return array<string, mixed>|null Données de l'article ou null si non trouvé
     */
    public function getArticleBySlug(string $slug): ?array
    {
        try {
            $query = "SELECT * FROM ARTICLES WHERE slug = :slug";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([':slug' => $slug]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result !== false ? $result : null;
        } catch (PDOException $e) {
            error_log('ArticleModel::getArticleBySlug - ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupérer des articles paginés triés par date de création (décroissant)
     *
     * @param int $offset Décalage de départ
     * @param int $limit Nombre d'articles à récupérer
     * @return array<int, array<string, mixed>> Tableau d'articles
     */
    public function getPaginatedArticles(int $offset, int $limit): array
    {
        try {
            $query = "SELECT id, title, slug, description, image_url, 
                      author, created_at 
                      FROM ARTICLES 
                      ORDER BY created_at DESC 
                      LIMIT :limit OFFSET :offset";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result ?: [];
        } catch (PDOException $e) {
            error_log('ArticleModel::getPaginatedArticles - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupérer les derniers articles triés par date de création (décroissant)
     *
     * Récupère un nombre spécifié des articles les plus récents de la base de données.
     * Requête optimisée qui ne sélectionne que les colonnes nécessaires pour les performances.
     *
     * @param int $limit Nombre d'articles à récupérer (par défaut : 2)
     * @return array<int, array<string, mixed>> Tableau d'articles, tableau vide si aucun trouvé
     */
    public function getLatestArticles(int $limit = 2): array
    {
        try {
            $query = "SELECT id, title, slug, description, image_url, author, created_at 
                      FROM ARTICLES 
                      ORDER BY created_at DESC 
                      LIMIT :limit";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result ?: [];
        } catch (PDOException $e) {
            error_log('ArticleModel::getLatestArticles - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Compter le nombre total d'articles
     *
     * @return int Nombre total d'articles
     */
    public function countArticles(): int
    {
        try {
            $query = "SELECT COUNT(*) as total FROM ARTICLES";
            $stmt = $this->pdo->query($query);

            if ($stmt === false) {
                return 0;
            }

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int) ($result['total'] ?? 0);
        } catch (PDOException $e) {
            error_log('ArticleModel::countArticles - ' . $e->getMessage());
            return 0;
        }
    }

    // =========================================================================
    // MÉTHODES PRIVÉES - GESTION DES SLUGS
    // =========================================================================

    /**
     * Générer un slug unique à partir d'un titre en utilisant SlugGenerator réutilisable
     *
     * Si un slug existe déjà, ajoute un numéro pour le rendre unique.
     * Exemple : "mon-article", "mon-article-2", "mon-article-3"
     *
     * @param string $title Titre à convertir
     * @return string Un slug unique
     */
    private function generateUniqueSlug(string $title): string
    {
        return SlugGenerator::generateUnique($title, function (string $slug): bool {
            return $this->slugExists($slug);
        });
    }

    /**
     * Vérifier si un slug existe déjà dans la base de données
     *
     * @param string $slug Slug à vérifier
     * @return bool True si le slug existe, false sinon
     */
    private function slugExists(string $slug): bool
    {
        try {
            $query = "SELECT COUNT(*) FROM ARTICLES WHERE slug = :slug";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([':slug' => $slug]);

            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('ArticleModel::slugExists - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Générer un slug unique pour une mise à jour d'article
     *
     * Similaire à generateUniqueSlug mais exclut l'article actuel de la vérification d'unicité.
     * Permet de mettre à jour un article sans changer son slug si le titre n'a pas changé,
     * ou de générer un nouveau slug unique si le titre a changé.
     *
     * @param string $title Titre à convertir
     * @param int $excludeId ID de l'article à exclure de la vérification d'unicité
     * @return string Un slug unique
     */
    private function generateUniqueSlugForUpdate(string $title, int $excludeId): string
    {
        return SlugGenerator::generateUnique($title, function (string $slug) use ($excludeId): bool {
            return $this->slugExistsExcludingId($slug, $excludeId);
        });
    }

    /**
     * Vérifier si un slug existe déjà dans la base de données, en excluant un ID d'article spécifique
     *
     * @param string $slug Slug à vérifier
     * @param int $excludeId ID de l'article à exclure de la vérification
     * @return bool True si le slug existe (en excluant l'ID spécifié), false sinon
     */
    private function slugExistsExcludingId(string $slug, int $excludeId): bool
    {
        try {
            $query = "SELECT COUNT(*) FROM ARTICLES WHERE slug = :slug AND id != :id";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([':slug' => $slug, ':id' => $excludeId]);

            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('ArticleModel::slugExistsExcludingId - ' . $e->getMessage());
            return false;
        }
    }
}
