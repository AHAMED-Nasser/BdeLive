<?php
/**
 * @var array<int, array<string, mixed>> $articles
 * @var array<string, mixed>|null $user
 * @var array<string, string|null> $flash
 */
$articles = $articles ?? [];
$imageFuturEvent = [
        ['src' => './assets/img/event1.png'],
        ['src' => './assets/img/event2.png'],
        ['src' => './assets/img/event3.png'],
];

start_page("BDE Inform'Aix - Site Officiel", true, $user ?? null);


if (session_status() === PHP_SESSION_NONE) {
}


if (!empty($flash['success'])) {
    echo '<div class="alert alert-success">' . htmlspecialchars($flash['success']);
    if (!empty($flash['show_register_link'])) {
        echo '<br><a href="index.php?page=register" style="margin-top: 10px; display: inline-block;">Cliquez ici pour créer un nouveau compte</a>';
    }
    echo '</div>';
}

if (!empty($flash['error'])) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($flash['error']) . '</div>';
}

// Gestion spéciale pour la suppression de session après affichage de la home
if (isset($user) && !empty($user['delete_session_after_home'])) {
    session_unset();
    session_destroy();
}
?>

<main>
    <?php if (isset($user) && $user !== null) : ?>
        <div class="alert alert-info">
            Bienvenue, <?= htmlspecialchars($user['first_name'] ?? '') ?> <?= htmlspecialchars($user['last_name'] ?? '') ?> (BUT <?= htmlspecialchars($user['user_status'] ?? '') ?>) !
            <a href="index.php?page=logout">Se déconnecter</a>
        </div>
    <?php endif; ?>

    <section class="hero">
        <h1 id="hero-title">BDE INFORM'AIX</h1>
        <p>Site officiel du BDE, BUT Informatique Aix-en-Provence</p>
    </section>

    <section class="future-event">
        <h2 class="title">Événement à venir</h2>
        <?php useCarousel('Soirée', $imageFuturEvent, 'carousel-future-event') ?>
    </section>

    <?php if (!empty($articles)) : ?>
        <section class="latest-articles" aria-labelledby="articles-title">
            <h2 id="articles-title" class="title">Dernières actualités</h2>
            <div class="articles-grid-home">
                <?php foreach ($articles as $index => $article) : ?>
                    <?php
                    // Prepare article data
                    $fullDescription = strip_tags($article['description'] ?? '');
                    $descriptionLength = mb_strlen($fullDescription);
                    $previewLength = 150;
                    $isLong = $descriptionLength > $previewLength;
                    $preview = mb_substr($fullDescription, 0, $previewLength);

                    // Format date using DateTime (POO)
                    $date = new DateTime($article['created_at'] ?? 'now');
                    $formattedDate = $date->format('d/m/Y');
                    ?>
                    <article class="article-card-home">
                        <?php if ($index === 0) : ?>
                            <span class="article-badge-new">Nouveau</span>
                        <?php endif; ?>
                        
                        <?php if (!empty($article['image_url'])) : ?>
                            <div class="article-image-container">
                                <img
                                    src="<?= htmlspecialchars($article['image_url']) ?>"
                                    alt="Illustration de l'article : <?= htmlspecialchars($article['title']) ?>"
                                    class="article-image-home"
                                    loading="lazy">
                            </div>
                        <?php endif; ?>

                        <div class="article-content-home">
                            <h3 class="article-title-home">
                                <?= htmlspecialchars($article['title']) ?>
                            </h3>
                            <p class="article-meta-home">
                                Par <?= htmlspecialchars($article['author']) ?>
                                le <?= htmlspecialchars($formattedDate) ?>
                            </p>
                            <p class="article-description-home">
                                <?= htmlspecialchars($preview) ?>
                                <?php if ($isLong) : ?>
                                    ...
                                <?php endif; ?>
                            </p>
                            
                            <div class="article-actions-home">
                                <a href="index.php?page=articles&slug=<?= htmlspecialchars(urlencode((string)($article['slug'] ?? ''))) ?>" 
                                   class="btn-read-more">
                                    Lire la suite
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <section class="BDE" aria-labelledby="bde-title">
        <h3 id="bde-title"><strong>Qui sommes-nous ?</strong></h3>
        <div class="bde-text">
            <h3>
                Le <strong>BDE Informatique d’Aix</strong> est une association étudiante qui rassemble les passionnés de
                technologies, de programmation et de cybersécurité.
                Nous organisons des événements, des ateliers et des soirées pour créer du lien entre les étudiants
                en informatique et favoriser l’entraide au sein du campus.
                <br><br>
                Nos valeurs : <strong>convivialité, partage et innovation</strong>.
                Que tu sois développeur, gamer ou simplement curieux, le BDE Info Aix est fait pour toi !
            </h3>
        </div>
    </section>

    <section class="Equip" aria-labelledby="equip-title">
        <h3 id="equip-title"><strong>Notre équipe</strong></h3>
        <div class="equip-text">
            <h3>
                <strong>Notre équipe passionnée</strong> est composée d'étudiants motivés à apprendre qui travaillent
                ensemble pour proposer des événements et projets autour de l’informatique.
            </h3>
            <ul>
                <li><a href="index.php?page=team">En savoir plus</a></li>
            </ul>
        </div>
    </section>

    <section class="join" aria-labelledby="join-title">
        <h3 id="join-title"><strong>Rejoins-nous</strong></h3>

        <div class="join-text">
            <h2>
                Envie de participer à la vie du campus ? <br>
                <strong>Rejoins le BDE Info Aix !</strong>
            </h2>

            <div class="join-buttons">
                <a href="https://discord.com/invite/4dXHpN6JCK" target="_blank" class="btn-discord">
                    <img src="./assets/img/discord.png" alt="Rejoindre le Discord" class="discord-img">
                </a>
            </div>
        </div>
    </section>

    <div class="btn-image-right">
        <a href="index.php">
            <img src="./assets/img/fleche-accueil.png" alt="Aller à l'accueil" class="bde-image">
        </a>
    </div>
</main>

<?php end_page(); ?>
