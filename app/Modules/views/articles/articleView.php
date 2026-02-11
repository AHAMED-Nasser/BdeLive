<?php
/**
 * @var array<string, mixed>|null $user
 * @var array<string, string|null> $flash
 * @var \App\Modules\Entities\Article $article Article entity
 */
start_page("BDELive - Site officiel", true, $user ?? null);
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
            <?php if ($article->hasImage()) : ?>
                <div class="article-full-image">
                    <img
                        src="<?= htmlspecialchars((string) ($article->getImageUrl() ?? '')) ?>"
                        alt="<?= htmlspecialchars($article->getTitle()) ?>"
                        loading="lazy"
                        decoding="async">
                </div>
            <?php endif; ?>

            <div class="article-full-content">
                <h1 class="article-full-title">
                    <?= htmlspecialchars($article->getTitle()) ?>
                </h1>

                <p class="article-full-meta">
                    Par <?= htmlspecialchars($article->getAuthor()) ?>
                    le <?= htmlspecialchars($article->getFormattedDate()) ?>
                </p>

                <div class="article-full-description">
                    <?= nl2br(htmlspecialchars($article->getDescription())) ?>
                </div>

                <?php if (!empty($user) && isset($user['is_admin']) && $user['is_admin']) : ?>
                    <div class="article-full-actions">
                        <a href="index.php?page=updateArticle&slug=<?= htmlspecialchars(urlencode($article->getSlug())) ?>" 
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
