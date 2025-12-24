<?php
/**
 * @var array<string, mixed>|null $user
 * @var array<string, string|null> $flash
 * @var array<string, mixed> $article
 */
start_page(htmlspecialchars($article['title'] ?? 'Article'), true, $user ?? null);
?>

<section class="article-section">
    <div class="container">
        <a href="index.php?page=articles" class="back-link">← Retour aux articles</a>

        <?php if (!empty($flash['error'])) : ?>
            <article style="color: #922222; text-align: center; margin-bottom: 20px;">
                <?= htmlspecialchars($flash['error']) ?>
            </article>
        <?php endif ?>

        <article class="article-full">
            <?php if (!empty($article['image_url'])) : ?>
                <div class="article-full-image">
                    <img
                        src="<?= htmlspecialchars($article['image_url']) ?>"
                        alt="<?= htmlspecialchars($article['title']) ?>">
                </div>
            <?php endif; ?>

            <div class="article-full-content">
                <h1 class="article-full-title">
                    <?= htmlspecialchars($article['title']) ?>
                </h1>

                <p class="article-full-meta">
                    Par <?= htmlspecialchars($article['author']) ?>
                    le <?= date('d/m/Y', strtotime($article['created_at'])) ?>
                </p>

                <div class="article-full-description">
                    <?= nl2br(htmlspecialchars($article['description'])) ?>
                </div>

                <?php if (!empty($user) && isset($user['is_admin']) && $user['is_admin']) : ?>
                    <div class="article-full-actions">
                        <a href="index.php?page=updateArticle&slug=<?= htmlspecialchars(urlencode((string)($article['slug'] ?? ''))) ?>" 
                           class="btn-edit">
                            Modifier
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </article>
    </div>
</section>

<?php end_page(); ?>
