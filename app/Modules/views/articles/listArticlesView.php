<?php
/**
 * Articles List View
 *
 * Displays a paginated list of all articles with filtering and sorting options.
 * Includes admin controls for editing and deleting articles.
 *
 * @package BdeLive\Views\Articles
 * @version 1.0.0
 * @author BdeLive Team
 *
 * @var array<int, \App\Modules\Entities\Article> $articles Array of Article entities
 * @var \App\Modules\Helpers\Pagination $pagination
 * @var array<string, mixed>|null $user
 * @var array<string, string|null> $flash
 */
start_page("Nos articles", true, $user ?? null);
?>

<section class="articles-section">
    <div class="container">
        <div class="page-header-with-action">
            <h1 class="title">Nos articles</h1>
            <?php
            $auth = \App\Core\Application::getInstance()->auth();
            $isAdmin = $auth->isAdmin();
            if ($isAdmin) : ?>
                <a href="index.php?page=createArticle" class="create-action-btn">
                    <i class="fas fa-edit"></i>
                    <span>Créer un article</span>
                </a>
            <?php endif; ?>
        </div>

        <?php if (!empty($flash['success'])) : ?>
            <article style="color: #1d7630; text-align: center; margin-bottom: 20px;">
                <?= htmlspecialchars($flash['success']) ?>
            </article>
        <?php endif ?>

        <?php if (!empty($flash['error'])) : ?>
            <article style="color: #922222; text-align: center; margin-bottom: 20px;">
                <?= htmlspecialchars($flash['error']) ?>
            </article>
        <?php endif ?>

        <?php if (empty($articles)) : ?>
            <p class="no-articles">Aucun article disponible pour le moment.</p>
        <?php else : ?>
            <div class="articles-grid">
                <?php foreach ($articles as $article) : ?>
                    <?php
                    // Use entity method for short description
                    $preview = $article->getShortDescription(150);
                    $fullDescription = strip_tags($article->getDescription());
                    $isLong = mb_strlen($fullDescription) > 150;
                    ?>
                    <article class="article-card">
                        <?php if ($article->hasImage()) : ?>
                            <img
                                src="<?= htmlspecialchars((string) ($article->getImageUrl() ?? '')) ?>"
                                alt="<?= htmlspecialchars($article->getTitle()) ?>"
                                class="article-image"
                                loading="lazy"
                                decoding="async">
                        <?php endif; ?>
                        <!-- PAS d'image par defaut si vide ! -->

                        <div class="article-content">
                            <h2 class="article-title">
                                <?= htmlspecialchars($article->getTitle()) ?>
                            </h2>
                            <p class="article-meta">
                                Par <?= htmlspecialchars($article->getAuthor()) ?>
                                le <?= htmlspecialchars($article->getFormattedDate()) ?>
                            </p>
                            <p class="article-description">
                                <?= htmlspecialchars($preview) ?>
                            </p>
                            
                            <div class="article-actions">
                                <?php if ($isLong) : ?>
                                    <a href="index.php?page=articles&slug=<?= htmlspecialchars(urlencode($article->getSlug())) ?>" 
                                       class="btn-view">
                                        Voir l'article
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (!empty($user) && isset($user['is_admin']) && $user['is_admin']) : ?>
                                    <a href="index.php?page=updateArticle&slug=<?= htmlspecialchars(urlencode($article->getSlug())) ?>" 
                                       class="btn-edit">
                                        Modifier
                                    </a>
                                    <form method="POST" 
                                          action="index.php?page=deleteArticle&action=deleteArticle&slug=<?= htmlspecialchars(urlencode($article->getSlug())) ?>" 
                                          class="delete-article-form">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="slug" value="<?= htmlspecialchars($article->getSlug()) ?>">
                                        <button type="button" class="btn-delete"
                                                data-confirm-title="Supprimer l'article"
                                                data-confirm-msg="Êtes-vous sûr de vouloir supprimer cet article ? Cette action est irréversible.">
                                            Supprimer
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($pagination->getTotalPages() > 1) : ?>
                <nav class="pagination-container" aria-label="Navigation des articles">
                    <ul class="pagination">
                        <?php if ($pagination->getCurrentPage() > 1) : ?>
                            <li>
                                <a href="<?= $pagination->getLink($pagination->getCurrentPage() - 1) ?>&src=prev">
                                    Précédent
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $pagination->getTotalPages(); $i++) : ?>
                            <li>
                                <?php if ($i === $pagination->getCurrentPage()) : ?>
                                    <span class="page-number active" aria-current="page" aria-label="Page <?= $i ?>, page actuelle">
                                        <?= $i ?>
                                    </span>
                                <?php else : ?>
                                    <a href="<?= $pagination->getLink($i) ?>" class="page-number" aria-label="Aller à la page <?= $i ?>">
                                        <?= $i ?>
                                    </a>
                                <?php endif; ?>
                            </li>
                        <?php endfor; ?>

                        <?php if ($pagination->getCurrentPage() < $pagination->getTotalPages()) : ?>
                            <li>
                                <a href="<?= $pagination->getLink($pagination->getCurrentPage() + 1) ?>&src=next">
                                    Suivant
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php end_page(); ?>
