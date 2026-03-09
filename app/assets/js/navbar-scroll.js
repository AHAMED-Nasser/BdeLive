'use strict';

/**
 * Shrinks the navbar when the user scrolls down,
 * to keep the header more compact and professional.
 */
document.addEventListener('DOMContentLoaded', function () {
    var nav = document.querySelector('.nav');
    if (!nav) {
        return;
    }

    var SCROLL_THRESHOLD = 80;

    function updateNavbarSize() {
        if (window.scrollY > SCROLL_THRESHOLD) {
            nav.classList.add('nav--compact');
        } else {
            nav.classList.remove('nav--compact');
        }
    }

    window.addEventListener('scroll', updateNavbarSize, { passive: true });
    updateNavbarSize();

    document.addEventListener('click', function (e) {
        document.querySelectorAll('.profile-dropdown-toggle').forEach(function (toggle) {
            var container = toggle.closest('.profile-dropdown-container');
            if (container && !container.contains(e.target)) {
                toggle.checked = false;
            }
        });
    });
});

