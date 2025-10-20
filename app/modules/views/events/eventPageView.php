<?php
    start_page('Evénements');

    $imageEventEsport = [
        ['src' => './assets/img/carousel/events/event2.jpg'],
        ['src' => './assets/img/carousel/events/event1.svg'],
        ['src' => './assets/img/carousel/events/event1.svg']
    ];

    $imageEventSoiree = [
        ['src' => './assets/img/carousel/events/event1.svg']
    ];
?>

<section class="events">
    <h1 class="title">Evénements</h1>
    <?php useCarousel('E-sport', $imageEventEsport, 'carousel-esport-event') ?>
    <?php useCarousel('Soirées', $imageEventSoiree, 'carousel-soiree-event') ?>
</section>


<?php end_page();
