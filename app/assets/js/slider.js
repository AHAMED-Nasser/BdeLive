let slideIndexes = {};

function moveSlide(n, carouselId) {
    if (!slideIndexes[carouselId]) slideIndexes[carouselId] = 1;
    showSlide(slideIndexes[carouselId] += n, carouselId);
}

function currentSlide(n, carouselId) {
    showSlide(slideIndexes[carouselId] = n + 1, carouselId);
}

function showSlide(n, carouselId) {
    const carousel = document.getElementById(carouselId);
    const slides = carousel.querySelectorAll('.carousel-item');
    const dots = carousel.querySelectorAll('.dot');

    if (!slideIndexes[carouselId]) slideIndexes[carouselId] = 1;

    if (n > slides.length) slideIndexes[carouselId] = 1;
    if (n < 1) slideIndexes[carouselId] = slides.length;

    slides.forEach(slide => slide.classList.remove('active'));
    dots.forEach(dot => dot.classList.remove('active'));

    slides[slideIndexes[carouselId] - 1].classList.add('active');
    dots[slideIndexes[carouselId] - 1].classList.add('active');
}