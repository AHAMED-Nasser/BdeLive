<?php
/**
 * Homepage View
 *
 * Displays the main landing page with featured events carousel,
 * latest articles, welcome message, and quick navigation to key sections.
 *
 * @package BdeLive\Views\Public
 * @version 1.0.0
 * @author BdeLive - Group 8
 *
 * @var array<int, \App\Modules\Entities\Article> $articles Array of Article entities
 * @var array<int, \App\Modules\Entities\Event> $events Array of Event entities
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
            Bienvenue, <?= htmlspecialchars($user['first_name'] ?? '') ?>     <?= htmlspecialchars($user['last_name'] ?? '') ?>
            (<?= htmlspecialchars($user['user_status'] ?? '') ?>) !
            <a href="index.php?page=logout">Se déconnecter</a>
        </div>
    <?php endif; ?>

    <section class="hero">
        <div class="hero-content">
            <h1 id="hero-title">BDELive</h1>
            <p>Site officiel du BDE, BUT Informatique Aix-en-Provence</p>
        </div>

        <div class="opening-hour">
            <?php
            $day = (int)date('N');
            $hour = date('H:i');

            $openDay = [1, 2, 3, 4, 5];

            $isOpenDay = in_array($day, $openDay, true);
            $isOpenHour = ($hour >= '10:05' && $hour <= '10:25') || ($hour >= '12:15' && $hour <= '13:30') || ($hour >= '15:20' && $hour <= '15:40');

            if ($isOpenDay && $isOpenHour) : ?>
            <span class="bde-opening" id="open">BDE Ouvert</span>
            <?php else : ?>
            <span class="bde-opening" id="close">BDE Fermé</span>
            <?php endif ?>
        </div>
        
        <!-- Horaires d'ouverture -->
        <div class="hero-hours">
            <div class="hero-hours-title">Horaires d'ouverture</div>
            <div class="hero-hours-content">
                <div class="hero-hours-section">
                    <strong>Du lundi au vendredi :</strong>
                    <div class="hero-hours-times">
                        <span>10h05 - 10h25</span>
                        <span>12h15 - 13h30</span>
                        <span>15h20 - 15h40</span>
                    </div>
                </div>
                <div class="hero-hours-section">
                    <strong>Le samedi et dimanche :</strong>
                    <div class="hero-hours-times">
                        <span class="closed">Fermé</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="future-event">
        <h2 class="section-title events-title">Événements à venir</h2>

        <?php if (!empty($events)) : ?>
            <article class="carousel" id="carousel-future-event">
                <div class="carousel-block">
                    <button class="carousel-control prev" onclick="moveSlide(-1, 'carousel-future-event')"
                        aria-label="Précédent">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </button>

                    <div class="carousel-inner">
                        <?php foreach ($events as $index => $event) : ?>
                            <?php
                            // Get images array from entity
                            $images = $event->getImagesArray();
                            $eventImage = null; // No static fallback

                            if (!empty($images)) {
                                $firstImage = $images[0];
                                // Check if it's an array with 'url' key or a direct string
                                $eventImage = is_array($firstImage) ? ($firstImage['url'] ?? null) : $firstImage;
                            }
                            ?>
                            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                <?php if ($event->isPast()) : ?>
                                    <span class="event-badge--past" aria-hidden="true">Passé</span>
                                <?php else : ?>
                                    <span class="event-badge--upcoming" aria-hidden="true">À venir</span>
                                <?php endif; ?>
                                <?php
                                $homeEventHref = $event->getSlug() !== ''
                                    ? 'index.php?page=showEvent&slug=' . urlencode($event->getSlug())
                                    : 'index.php?page=showEvent&id=' . (int) $event->getId();
                                $eventTypeLabel = $event->isGroupEvent()
                                    ? 'Groupe (' . $event->getTeamSize() . 'x)'
                                    : 'Solo';
                                ?>
                                <a href="<?= htmlspecialchars($homeEventHref) ?>"
                                    class="carousel-event-link"
                                    aria-label="Voir les détails de <?= htmlspecialchars($event->getName()) ?><?= $event->isPast() ? ' (événement passé)' : '' ?>">
                                    <div class="carousel-event-overlay">
                                        <h3 class="event-title"><?= htmlspecialchars($event->getName()) ?></h3>
                                        <div class="event-meta-brief">
                                            <span class="event-meta-date"><?= htmlspecialchars($event->getFormattedDate()) ?> · <?= htmlspecialchars($event->getFormattedTime()) ?></span>
                                            <?php if ($event->getLocation() !== '') : ?>
                                                <span class="event-meta-location"><?= htmlspecialchars($event->getLocation()) ?></span>
                                            <?php endif; ?>
                                            <span class="event-meta-type"><?= htmlspecialchars($eventTypeLabel) ?></span>
                                            <span class="event-meta-registration"><?= $event->isPast() ? 'Inscriptions fermées' : 'Inscriptions ouvertes' ?></span>
                                        </div>
                                    </div>
                                    <?php if ($eventImage) : ?>
                                        <img src="<?= htmlspecialchars($eventImage) ?>" class="carousel-image"
                                            alt="<?= htmlspecialchars($event->getName()) ?>" <?= $index > 0 ? 'loading="lazy"' : '' ?> decoding="async">
                                    <?php else : ?>
                                        <div class="carousel-no-image">
                                            <span class="event-name-display"><?= htmlspecialchars($event->getName()) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <button class="carousel-control next" onclick="moveSlide(1, 'carousel-future-event')"
                        aria-label="Suivant">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </button>
                </div>

                <div class="carousel-dots" role="group" aria-label="Indicateurs du carousel">
                    <?php foreach ($events as $index => $event) : ?>
                        <button class="dot <?= $index === 0 ? 'active' : '' ?>" type="button"
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
                    $preview = \App\Core\Markdown\MarkdownRenderer::toPlainPreview($article->getDescription(), 150);
                    $fullDescription = $article->getDescription() ?? '';
                    $isLong = mb_strlen($fullDescription) > 150;
                    ?>
                    <article class="article-card-home">
                        <?php if ($index === 0) : ?>
                            <span class="article-badge-new">Nouveau</span>
                        <?php endif; ?>

                        <?php if ($article->hasImage()) : ?>
                            <div class="article-image-container">
                                <img src="<?= htmlspecialchars((string) ($article->getImageUrl() ?? '')) ?>"
                                    alt="<?= htmlspecialchars($article->getTitle()) ?>" class="article-image-home" loading="lazy">
                            </div>
                        <?php endif; ?>

                        <div class="article-content-home">
                            <h3 class="article-title-home">
                                <?= htmlspecialchars($article->getTitle()) ?>
                            </h3>
                            <p class="article-meta-home">
                                Par <?= htmlspecialchars($article->getAuthor()) ?>
                                le <?= htmlspecialchars($article->getFormattedDate()) ?>
                            </p>
                            <p class="article-description-home">
                                <?= htmlspecialchars($preview) ?>
                            </p>

                            <div class="article-actions-home">
                                <a href="index.php?page=articles&slug=<?= htmlspecialchars(urlencode($article->getSlug())) ?>"
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
            <?php if (isset($user) && $user !== null) : ?>
            <ul>
                <li><a href="index.php?page=bde_members">En savoir plus</a></li>
            </ul>
            <?php endif; ?>
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
</main>

<?php end_page(); ?>
