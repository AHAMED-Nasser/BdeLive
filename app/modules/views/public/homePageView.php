<?php
$imageFuturEvent = [
        ['src' => './assets/img/event1.png'],
        ['src' => './assets/img/event2.png'],
        ['src' => './assets/img/event3.png'],
];

start_page("BDE Inform'Aix - Site Officiel", true);


if (session_status() === PHP_SESSION_NONE) {
}


if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
}

if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
}


unset($_SESSION['success'], $_SESSION['error']);


if (!empty($_SESSION['delete_session_after_home'])) {
    unset($_SESSION['delete_session_after_home']);
    session_unset();
    session_destroy();
}
?>

<main>
    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="alert alert-info">
            Bienvenue, <?= htmlspecialchars($_SESSION['first_name']) ?> <?= htmlspecialchars($_SESSION['last_name']) ?> (BUT <?= htmlspecialchars($_SESSION['user_status']) ?>) !
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
