let slideIndexes = {};
const AUTO_PLAY_INTERVAL_MS = 5000; // 5 secondes - vitesse optimale UX
const HOME_CAROUSEL_ID = 'carousel-future-event';
let autoPlayTimers = {};

function moveSlide(n, carouselId) {
    if (!slideIndexes[carouselId]) {
        slideIndexes[carouselId] = 1;
    }
    showSlide(slideIndexes[carouselId] += n, carouselId);
    resetAutoPlay(carouselId);
}

function currentSlide(n, carouselId) {
    showSlide(slideIndexes[carouselId] = n + 1, carouselId);
    resetAutoPlay(carouselId);
}

function showSlide(n, carouselId) {
    const carousel = document.getElementById(carouselId);
    const slides = carousel.querySelectorAll('.carousel-item');
    const dots = carousel.querySelectorAll('.dot');

    if (!slideIndexes[carouselId]) {
        slideIndexes[carouselId] = 1;
    }

    if (n > slides.length) {
        slideIndexes[carouselId] = 1;
    }
    if (n < 1) {
        slideIndexes[carouselId] = slides.length;
    }

    slides.forEach(slide => slide.classList.remove('active'));
    dots.forEach(dot => dot.classList.remove('active'));

    slides[slideIndexes[carouselId] - 1].classList.add('active');
    dots[slideIndexes[carouselId] - 1].classList.add('active');
}

function stopAutoPlay(carouselId) {
    if (autoPlayTimers[carouselId]) {
        clearInterval(autoPlayTimers[carouselId]);
        autoPlayTimers[carouselId] = null;
    }
}

function startAutoPlay(carouselId) {
    const carousel = document.getElementById(carouselId);
    if (!carousel) return;
    const slides = carousel.querySelectorAll('.carousel-item');
    if (slides.length <= 1) return;

    stopAutoPlay(carouselId);
    autoPlayTimers[carouselId] = setInterval(() => {
        moveSlide(1, carouselId);
    }, AUTO_PLAY_INTERVAL_MS);
}

function resetAutoPlay(carouselId) {
    if (carouselId !== HOME_CAROUSEL_ID) return;
    stopAutoPlay(carouselId);
    startAutoPlay(carouselId);
}

function initCarousels() {
    const carousels = document.querySelectorAll('.carousel');
    carousels.forEach(carousel => {
        const carouselId = carousel.id;
        if (carouselId) {
            slideIndexes[carouselId] = 1;
            showSlide(1, carouselId);
            if (carouselId === HOME_CAROUSEL_ID) {
                startAutoPlay(carouselId);
            }
        }
    });
}

// Initialiser au chargement de la page
document.addEventListener('DOMContentLoaded', initCarousels);

// Pause auto-play quand l'onglet n'est pas visible (économie ressources)
document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        stopAutoPlay(HOME_CAROUSEL_ID);
    } else {
        const carousel = document.getElementById(HOME_CAROUSEL_ID);
        if (carousel && carousel.querySelectorAll('.carousel-item').length > 1) {
            startAutoPlay(HOME_CAROUSEL_ID);
        }
    }
});
