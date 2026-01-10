<?php
/**
 * @var array<int, array<string, mixed>> $articles
 * @var \App\Modules\Helpers\Pagination $pagination
 * @var array<string, mixed>|null $user
 * @var array<string, string|null> $flash
 */
start_page("Nos articles", true, $user ?? null);
?>

<section class="articles-section">
    <div class="container">
        <h1 class="title">Nos articles</h1>

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
                    $fullDescription = strip_tags($article['description'] ?? '');
                    $descriptionLength = mb_strlen($fullDescription);
                    $previewLength = 150;
                    $isLong = $descriptionLength > $previewLength;
                    $preview = mb_substr($fullDescription, 0, $previewLength);
                    ?>
                    <article class="article-card">
                        <?php if (!empty($article['image_url'])) : ?>
                            <img
                                src="<?= htmlspecialchars($article['image_url']) ?>"
                                alt="<?= htmlspecialchars($article['title']) ?>"
                                class="article-image">
                        <?php endif; ?>
                        <!-- PAS d'image par defaut si vide ! -->

                        <div class="article-content">
                            <h2 class="article-title">
                                <?= htmlspecialchars($article['title']) ?>
                            </h2>
                            <p class="article-meta">
                                Par <?= htmlspecialchars($article['author']) ?>
                                le <?= date('d/m/Y', strtotime($article['created_at'])) ?>
                            </p>
                            <p class="article-description">
                                <?= htmlspecialchars($preview) ?>
                                <?php if ($isLong) : ?>
                                    ...
                                <?php endif; ?>
                            </p>
                            
                            <div class="article-actions">
                                <?php if ($isLong) : ?>
                                    <a href="index.php?page=articles&slug=<?= htmlspecialchars(urlencode((string)($article['slug'] ?? ''))) ?>" 
                                       class="btn-view">
                                        Voir l'article
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (!empty($user) && isset($user['is_admin']) && $user['is_admin']) : ?>
                                    <a href="index.php?page=updateArticle&slug=<?= htmlspecialchars(urlencode((string)($article['slug'] ?? ''))) ?>" 
                                       class="btn-edit">
                                        Modifier
                                    </a>
                                    <form method="POST" 
                                          action="index.php?page=deleteArticle&action=deleteArticle&slug=<?= htmlspecialchars(urlencode((string)($article['slug'] ?? ''))) ?>" 
                                          class="delete-article-form"
                                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet article ? Cette action est irréversible.');">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="slug" value="<?= htmlspecialchars((string)($article['slug'] ?? '')) ?>">
                                        <button type="submit" class="btn-delete">
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
