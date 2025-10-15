<?php
    function useCarousel($carouselLabel, $imageMap) {
?>

<h2 class="event-title"><?= $carouselLabel ?></h2>
<section class="carousel" id="<?= $carouselId ?? 'carousel' ?>">
    <button class="carousel-control prev" onclick="moveSlide(-1, '<?= $carouselId ?? 'carousel' ?>')" ><img src="./assets/img/carousel/arrow.png" width="50px"></button>

    <div class="carousel-inner">
        <?php foreach ($imageMap as $index => $image): ?>
        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
            <img src="<?= htmlspecialchars($image['src']) ?>" class="carousel-image" width="800px">
        </div>
        <?php endforeach ?>
    </div>

    <button class="carousel-control next" onclick="moveSlide(1, '<?= $carouselId ?? 'carousel'?>')"><img src="./assets/img/carousel/arrow.png" width="50px"></button>

    <div class="carousel-dots">
        <?php foreach ($imageMap as $image): ?>
            <span class="dot <?= $index === 0 ? 'active' : ''?>" onclick="currentSlide(<?= $index ?>, <?= $carouselId ?? 'carousel'?>)"></span>
        <?php endforeach ?>
    </div>
</section>

<?php } ?>
