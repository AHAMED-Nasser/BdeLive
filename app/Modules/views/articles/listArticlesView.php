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
                                <?= htmlspecialchars(
                                    mb_substr(strip_tags($article['description']), 0, 150)
                                ) ?>...
                            </p>
                            <?php if (!empty($user) && isset($user['is_admin']) && $user['is_admin']) : ?>
                                <div class="article-actions">
                                    <a href="index.php?page=updateArticle&slug=<?= htmlspecialchars(urlencode((string)($article['slug'] ?? ''))) ?>" 
                                       class="btn-edit">
                                        Modifier
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($pagination->getTotalPages() > 1) : ?>
                <div class="pagination">
                    <?php if ($pagination->getCurrentPage() > 1) : ?>
                        <a href="<?= $pagination->getLink($pagination->getCurrentPage() - 1) ?>">
                            Précédent
                        </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $pagination->getTotalPages(); $i++) : ?>
                        <a href="<?= $pagination->getLink($i) ?>"
                           class="<?= $i === $pagination->getCurrentPage() ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($pagination->getCurrentPage() < $pagination->getTotalPages()) : ?>
                        <a href="<?= $pagination->getLink($pagination->getCurrentPage() + 1) ?>">
                            Suivant
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php end_page(); ?>
