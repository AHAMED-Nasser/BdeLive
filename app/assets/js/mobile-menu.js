/**
 * Mobile Menu Handler
 * 
 * Manages the mobile sidebar menu behavior:
 * - Closes menu when clicking on navigation links
 * - Prevents body scroll when menu is open
 * - Handles menu toggle state
 * 
 * @author BdeLive Team
 * @version 1.0.0
 */
(function () {
    'use strict';

    const menuToggle = document.getElementById('menu-toggle');
    const sidebarLinks = document.querySelectorAll('.sidebar-menu a');

    // Close menu when clicking on a link
    if (menuToggle && sidebarLinks.length > 0) {
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function () {
                // Close menu after a short delay to allow navigation
                setTimeout(function () {
                    menuToggle.checked = false;
                }, 100);
            });
        });
    }

    // Prevent body scroll when menu is open
    if (menuToggle) {
        menuToggle.addEventListener('change', function () {
            if (this.checked) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        });
    }
})();
