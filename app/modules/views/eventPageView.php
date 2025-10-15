<?php
    start_page('Evénements');

    $imageEventEsport = [
        ['src' => './assets/img/carousel/events/event2.jpg'],
        ['src' => './assets/img/carousel/events/event1.jpg'],
        ['src' => './assets/img/carousel/events/event1.svg']
    ];
    $carouselId = 'carousel-event';
?>

<div class="forgot-container">
    <h1>Evénements</h1>
    <?php useCarousel('E-sport', $imageEventEsport) ?>
</div>

<?php end_page() ?>

