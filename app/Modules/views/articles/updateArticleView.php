<?php
/**
 * Update Article View
 *
 * Displays the form for editing an existing article.
 * Pre-populates fields with current article data and handles image updates.
 *
 * @package BdeLive\Views\Articles
 * @version 1.0.0
 * @author BdeLive Team
 *
 * @var \App\Core\Security\CsrfProtection $csrf
 * @var array<string, mixed>|null $user
 * @var array<string, string|null> $flash
 * @var \App\Modules\Entities\Article $article Article entity
 */
start_page("Modifier un article", true, $user ?? null) ?>

<section class="createEvent">
    <div class="form-container-wide">
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
            action="index.php?page=updateArticle&slug=<?= htmlspecialchars(urlencode($article->getSlug())) ?>&action=submitUpdate"
            method="POST"
            enctype="multipart/form-data">
            <?= $csrf->getTokenField() ?>
            <input type="hidden" name="article-id" value="<?= htmlspecialchars((string) $article->getId()) ?>">

            <label for="article-title">Titre de l'article</label>
            <input
                id="article-title"
                type="text"
                name="article-title"
                placeholder="Titre de l'article"
                value="<?= htmlspecialchars($article->getTitle()) ?>"
                required>

            <label for="markdown-editor">Description de l'article</label>
            <textarea
                id="markdown-editor"
                data-autosave-id="article_description"
                placeholder="Contenu de l'article en Markdown..."
                name="article-description"
                rows="20"
                required><?= htmlspecialchars($article->getDescription()) ?></textarea>

            <label for="author">Auteur de l'article</label>
            <input
                id="author"
                type="text"
                name="author"
                placeholder="Nom complet de l'auteur"
                value="<?= htmlspecialchars($article->getAuthor()) ?>"
                required>

            <div class="insert-image">
                <?php if ($article->hasImage()) : ?>
                    <div class="image-management" style="margin-top: 20px; margin-bottom: 20px;">
                        <p class="form-label">Images actuelles (cocher pour supprimer) :</p>
                        <div class="current-images" style="display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 15px;">
                            <div class="img-item" style="text-align: center; width: 120px;">
                                <img
                                    src="<?= htmlspecialchars((string) ($article->getImageUrl() ?? '')) ?>"
                                    alt="Image actuelle"
                                    loading="lazy"
                                    decoding="async"
                                    style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;">
                                <label style="font-size: 0.8em; color: #dc3545; cursor: pointer; display: block; margin-top: 5px;">
                                    <input type="checkbox" name="delete-image" value="1"> Supprimer
                                </label>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="form-label"><?= $article->hasImage() ? 'Ajouter une nouvelle image :' : 'Image de l\'article (optionnel)' ?></div>
<div id="drop-area">
                <input id="article-image" name="article-image" type="file" accept="image/*" hidden>
                    <div id="image-view">
                        <p id="image-view-text">Glissez déposez ici <br> pour changer l'image</p>
                    </div>
                </div>
                <!-- image recap -->
                <div id="image-recap"></div>
            </div>

            <button type="submit" data-loading-text="Modification en cours...">Modifier l'article</button>

        </form>

    </div>
</section>

<script>
// Gestion visuelle de la suppression d'image avec checkbox
document.addEventListener('DOMContentLoaded', function() {
    const deleteCheckbox = document.querySelector('input[name="delete-image"]');
    const articleImageInput = document.getElementById('article-image');

    if (deleteCheckbox) {
        const imgItem = deleteCheckbox.closest('.img-item');
        const img = imgItem ? imgItem.querySelector('img') : null;

        deleteCheckbox.addEventListener('change', function() {
            if (this.checked && img) {
                img.style.opacity = '0.5';
                img.style.filter = 'grayscale(100%)';
            } else if (img) {
                img.style.opacity = '1';
                img.style.filter = 'none';
            }
        });

        // Si une nouvelle image est sélectionnée, décocher la suppression
        if (articleImageInput) {
            articleImageInput.addEventListener('change', function() {
                if (this.files && this.files.length > 0 && deleteCheckbox.checked) {
                    deleteCheckbox.checked = false;
                    if (img) {
                        img.style.opacity = '1';
                        img.style.filter = 'none';
                    }
                }
            });
        }
    }
});
</script>

<?php end_page() ?>
