<?php
/**
 * @var \App\Core\Security\CsrfProtection $csrf
 * @var array<string, mixed>|null $user
 * @var array<string, string|null> $flash
 * @var array<string, mixed> $article
 */
start_page("Modifier un article", true, $user ?? null) ?>

<section class="createEvent">
    <div class="forgot-container">
        <h1 class="title">Modification d'un article</h1>

        <?php if (!empty($flash['success'])) : ?>
            <article style="color: #1d7630">
                <?= htmlspecialchars($flash['success']) ?>
            </article>
        <?php endif ?>

        <?php if (!empty($flash['error'])) : ?>
            <article style="color: #922222">
                <?= htmlspecialchars($flash['error']) ?>
            </article>
        <?php endif ?>

        <form
            id="form"
            action="index.php?page=updateArticle&slug=<?= htmlspecialchars(urlencode((string)($article['slug'] ?? ''))) ?>&action=submitUpdate"
            method="POST"
            enctype="multipart/form-data">
            <?= $csrf->getTokenField() ?>
            <input type="hidden" name="article-id" value="<?= htmlspecialchars((string)($article['id'] ?? '')) ?>">
            
            <label for="article-title">Titre de l'article</label>
            <input 
                id="article-title" 
                type="text" 
                name="article-title" 
                placeholder="Titre de l'article" 
                value="<?= htmlspecialchars($article['title'] ?? '') ?>"
                required>

            <label for="article-description">Description de l'article</label>
            <textarea
                id="article-description"
                placeholder="Contenu de l'article..."
                name="article-description"
                rows="10"
                required><?= htmlspecialchars($article['description'] ?? '') ?></textarea>

            <label for="author">Auteur de l'article</label>
            <input 
                id="author" 
                type="text" 
                name="author" 
                placeholder="Nom complet de l'auteur" 
                value="<?= htmlspecialchars($article['author'] ?? '') ?>"
                required>

            <div class="insert-image">
                <label>Image de l'article (optionnel - laisser vide pour conserver l'image actuelle)</label>
                <?php if (!empty($article['image_url'])) : ?>
                    <div style="margin-bottom: 15px;">
                        <p style="margin-bottom: 10px; font-weight: bold;">Image actuelle :</p>
                        <img 
                            src="<?= htmlspecialchars($article['image_url']) ?>" 
                            alt="Image actuelle" 
                            style="max-width: 300px; max-height: 200px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                <?php endif; ?>
                <label for="article-image" id="drop-area">
                    <input id="article-image" name="article-image" type="file" accept="image/*" hidden>
                    <div id="image-view">
                        <p id="image-view-text">Glissez déposez ici <br> pour changer l'image</p>
                    </div>
                </label>
                <!-- image recap -->
                <div id="image-recap"></div>
            </div>

            <button type="submit">Modifier l'article</button>

        </form>

    </div>
</section>

<?php end_page() ?>
