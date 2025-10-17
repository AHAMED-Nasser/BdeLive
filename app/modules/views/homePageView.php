<?php
    $imageFuturEvent = [
        ['src' => './assets/img/event1.png'],
        ['src' => './assets/img/event2.png'],
        ['src' => './assets/img/event3.png']
    ];

    start_page("BDE Inform'Aix - Site Officiel", true);
?>
    <main>

        <?php
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['utilisateur_id'])): ?>
            <div class="alert alert-info">
                Bienvenue, <?= htmlspecialchars($_SESSION['prenom']) ?> <?= htmlspecialchars($_SESSION['nom']) ?> (BUT <?= htmlspecialchars($_SESSION['classe_annee']) ?>) ! 
                <a href="index.php?page=logout">Se déconnecter</a>
            </div>
        <?php endif; ?>
        <section class="hero">
            <h1 id="hero-title">BDE INFORM'AIX</h1>
            <p>Site officiel du BDE, BUT Informatique Aix-en-Provence</p>
        </section>

        <section class="future-event">
            <h2 class="title">Événement à venir</h2>
            <?php useCarousel('E-sport', $imageFuturEvent, 'carousel-future-event') ?>
        </section>



    </main>
<?php end_page() ?>
