<?php
/**
 * Homepage View
 *
 * Displays the main landing page with featured events carousel,
 * latest articles, welcome message, and quick navigation to key sections.
 *
 * @package BdeLive\Views\Public
 * @version 1.0.0
 * @author BdeLive Team
 *
 * @var array<int, array<string, mixed>> $articles
 * @var array<int, array<string, mixed>> $events
 * @var array<string, mixed>|null $user
 * @var array<string, string|null> $flash
 */
$articles = $articles ?? [];

start_page("BDELive - Site Officiel", true, $user ?? null);


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
            Bienvenue, <?= htmlspecialchars($user['first_name'] ?? '') ?> <?= htmlspecialchars($user['last_name'] ?? '')?> (<?= htmlspecialchars($user['user_status'] ?? '') ?>) !
            <a href="index.php?page=logout">Se déconnecter</a>
        </div>
    <?php endif; ?>

    <section class="hero">
        <h1 id="hero-title">BDELive</h1>
        <p>Site officiel du BDE, BUT Informatique Aix-en-Provence</p>
    </section>

    <section class="future-event">
        <h2 class="section-title events-title">Événements à venir</h2>
        
        <?php if (!empty($events)) : ?>
            <article class="carousel" id="carousel-future-event">
                <div class="carousel-block">
                    <button class="carousel-control prev" onclick="moveSlide(-1, 'carousel-future-event')" aria-label="Précédent">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>

                    <div class="carousel-inner">
                        <?php foreach ($events as $index => $event) : ?>
                            <?php
                            // Parse images JSON - handle both array and string format
                            $images = !empty($event['images']) ? json_decode($event['images'], true) : [];
                            $eventImage = null; // No static fallback

                            if (!empty($images) && is_array($images)) {
                                $firstImage = $images[0];
                                // Check if it's an array with 'url' key or a direct string
                                $eventImage = is_array($firstImage) ? ($firstImage['url'] ?? null) : $firstImage;
                            }
                            ?>
                            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                <a href="index.php?page=showEvent&id=<?= htmlspecialchars((string)$event['event_id']) ?>" 
                                   class="carousel-event-link"
                                   aria-label="Voir les détails de <?= htmlspecialchars($event['event_name']) ?>">
                                    <h3 class="event-title"><?= htmlspecialchars($event['event_name']) ?></h3>
                                    <?php if ($eventImage) : ?>
                                        <img src="<?= htmlspecialchars($eventImage) ?>" 
                                             class="carousel-image" 
                                             alt="<?= htmlspecialchars($event['event_name']) ?>"
                                             <?= $index > 0 ? 'loading="lazy"' : '' ?>
                                             decoding="async">
                                    <?php else : ?>
                                        <div class="carousel-no-image">
                                            <span class="event-name-display"><?= htmlspecialchars($event['event_name']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <button class="carousel-control next" onclick="moveSlide(1, 'carousel-future-event')" aria-label="Suivant">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>

                <div class="carousel-dots" role="group" aria-label="Indicateurs du carousel">
                    <?php foreach ($events as $index => $event) : ?>
                        <button class="dot <?= $index === 0 ? 'active' : '' ?>" 
                                type="button"
                                onclick="currentSlide(<?= $index ?>, 'carousel-future-event')"
                                aria-label="Aller à l'événement <?= $index + 1 ?>"
                                aria-current="<?= $index === 0 ? 'true' : 'false' ?>"></button>
                    <?php endforeach; ?>
                </div>
            </article>
        <?php else : ?>
            <div class="no-events-message" style="text-align: center; padding: 2rem;">
                <p>Aucun événement à venir pour le moment. Restez connectés !</p>
            </div>
        <?php endif; ?>
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
                                    alt="<?= htmlspecialchars($article['title']) ?>"
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
            <p>
                Le <strong>BDE Informatique d'Aix</strong> est une association étudiante qui rassemble les passionnés de
                technologies, de programmation et de cybersécurité.
                Nous organisons des événements, des ateliers et des soirées pour créer du lien entre les étudiants
                en informatique et favoriser l'entraide au sein du campus.
            </p>
            <p>
                Nos valeurs : <strong>convivialité, partage et innovation</strong>.
                Que tu sois développeur, gamer ou simplement curieux, le BDE Info Aix est fait pour toi !
            </p>
        </div>
    </section>

    <section class="Equip" aria-labelledby="equip-title">
        <h3 id="equip-title"><strong>Notre équipe</strong></h3>
        <div class="equip-text">
            <p>
                <strong>Notre équipe passionnée</strong> est composée d'étudiants motivés à apprendre qui travaillent
                ensemble pour proposer des événements et projets autour de l'informatique.
            </p>
            <ul>
                <li><a href="index.php?page=bde_members">En savoir plus</a></li>
            </ul>
        </div>
    </section>

    <section class="join" aria-labelledby="join-title">
        <h3 id="join-title"><strong>Rejoins-nous</strong></h3>

        <div class="join-text">
            <p>
                Envie de participer à la vie du campus ? <br>
                <strong>Rejoins le BDE Info Aix !</strong>
            </p>
        </div>
    </section>

    <div class="btn-image-right">
        <a href="index.php" aria-label="Retour à l'accueil">
            <svg class="bde-image" width="60" height="60" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <circle cx="12" cy="12" r="10" fill="currentColor"/>
                <path d="M12 8v8M8 12l4-4 4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
            </svg>
        </a>
    </div>
</main>

<?php end_page(); ?>
