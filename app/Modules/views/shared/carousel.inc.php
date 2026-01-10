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
        <button class="carousel-control prev" onclick="moveSlide(-1, '<?= $carouselId  ?>')" aria-label="Précédent">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>

        <div class="carousel-inner">
            <?php foreach ($imageMap as $index => $image) : ?>
                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                    <img src="<?= htmlspecialchars($image['src']) ?>" class="carousel-image" alt="<?= htmlspecialchars($image['alt'] ?? ($carouselLabel . ' - Image ' . ($index + 1))) ?>">
                </div>
            <?php endforeach ?>
        </div>

        <button class="carousel-control next" onclick="moveSlide(1, '<?= $carouselId?>')" aria-label="Suivant">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
    </div>

    <div class="carousel-dots">
        <?php foreach ($imageMap as $index => $image) : ?>
            <button class="dot <?= $index === 0 ? 'active' : ''?>" 
                    type="button"
                    onclick="currentSlide(<?= $index ?>, '<?= $carouselId ?>')"
                    aria-label="Afficher l'image <?= $index + 1 ?>"
                    aria-current="<?= $index === 0 ? 'true' : 'false' ?>"></button>
        <?php endforeach ?>
    </div>
</article>

<?php }
?>
