/**
 * Event delegation for carousel controls and sidebar overlay (replaces inline onclick for CSP compliance).
 * Must be loaded after slider.js which defines moveSlide() and currentSlide().
 */
(function () {
    'use strict';

    function initCarouselDelegation() {
        // Sidebar overlay: close menu on click
        document.querySelector('.sidebar-overlay')?.addEventListener('click', function () {
            const menuToggle = document.getElementById('menu-toggle');
            if (menuToggle) {
                menuToggle.checked = false;
            }
        });

        // Carousel controls: prev, next, dots
        document.addEventListener('click', function (e) {
            const prevBtn = e.target.closest('.carousel-control.prev');
            const nextBtn = e.target.closest('.carousel-control.next');
            const dotBtn = e.target.closest('.dot');

            if (prevBtn && typeof window.moveSlide === 'function') {
                const carousel = prevBtn.closest('.carousel');
                if (carousel && carousel.id) {
                    e.preventDefault();
                    window.moveSlide(-1, carousel.id);
                }
            } else if (nextBtn && typeof window.moveSlide === 'function') {
                const carousel = nextBtn.closest('.carousel');
                if (carousel && carousel.id) {
                    e.preventDefault();
                    window.moveSlide(1, carousel.id);
                }
            } else if (dotBtn && typeof window.currentSlide === 'function') {
                const carousel = dotBtn.closest('.carousel');
                if (carousel && carousel.id) {
                    const dots = carousel.querySelectorAll('.dot');
                    const index = Array.prototype.indexOf.call(dots, dotBtn);
                    if (index >= 0) {
                        e.preventDefault();
                        window.currentSlide(index, carousel.id);
                    }
                }
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCarouselDelegation);
    } else {
        initCarouselDelegation();
    }
})();
