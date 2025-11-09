<?php
/**
 * Affiche un carousel d’images.
 *
 * @param string $carouselLabel
 * @param array<int, array{src: string, alt?: string}> $imageMap
 * @param string $carouselId
 */
function useCarousel($carouselLabel, $imageMap, $carouselId): void
{
    ?>

<h2 class="event-title"><?= $carouselLabel ?></h2>
<article class="carousel" id="<?= $carouselId ?>">
    <div class="carousel-block">
        <button class="carousel-control prev" onclick="moveSlide(-1, '<?= $carouselId  ?>')" ><img src="./assets/img/carousel/arrow.png" alt="Précédent"></button>

        <div class="carousel-inner">
            <?php foreach ($imageMap as $index => $image) : ?>
                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                    <img src="<?= htmlspecialchars($image['src']) ?>" class="carousel-image" alt="<?= htmlspecialchars($image['alt'] ?? 'Image') ?>">
                </div>
            <?php endforeach ?>
        </div>

        <button class="carousel-control next" onclick="moveSlide(1, '<?= $carouselId?>')"><img src="./assets/img/carousel/arrow.png" alt="Suivant"></button>
    </div>

    <div class="carousel-dots">
        <?php foreach ($imageMap as $index => $image) : ?>
            <span class="dot <?= $index === 0 ? 'active' : ''?>" onclick="currentSlide(<?= $index ?>, '<?= $carouselId ?>')"></span>
        <?php endforeach ?>
    </div>
</article>

<?php }
?>
