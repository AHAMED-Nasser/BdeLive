<?php
/**
 * @var \App\Core\Security\CsrfProtection $csrf
 * @var array<string, mixed>|null $user
 * @var array<string, string|null> $flash
 */
start_page("Créer un article", true, $user ?? null) ?>

<section class="createEvent">
    <div class="forgot-container">
        <h1 class="title">Création d'un article</h1>

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
            action="index.php?page=createArticle&action=submitArticle"
            method="POST"
            enctype="multipart/form-data">
            <?= $csrf->getTokenField() ?>
            
            <label for="article-title">Titre de l'article</label>
            <input id="article-title" type="text" name="article-title" placeholder="Titre de l'article" required>

            <label for="article-description">Description de l'article</label>
            <textarea
                id="article-description"
                placeholder="Contenu de l'article..."
                name="article-description"
                rows="10"
                required></textarea>

            <label for="author-firstname">Prénom de l'auteur</label>
            <input id="author-firstname" type="text" name="author-firstname" placeholder="Prénom de l'auteur" required>

            <label for="author-lastname">Nom de l'auteur</label>
            <input id="author-lastname" type="text" name="author-lastname" placeholder="Nom de l'auteur" required>

            <div class="insert-image">
                <label>Image de l'article (optionnel)</label>
                <label for="article-image" id="drop-area">
                    <input id="article-image" name="article-image" type="file" accept="image/*" hidden>
                    <div id="image-view">
                        <p id="image-view-text">Glissez déposez ici <br> pour ajouter une image</p>
                    </div>
                </label>
                <!-- image recap -->
                <div id="image-recap"></div>
            </div>

            <button type="submit">Créer l'article</button>

        </form>

    </div>
</section>

<?php end_page() ?>
