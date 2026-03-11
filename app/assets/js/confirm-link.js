/**
 * Handles links with data-confirm attribute (replaces inline onclick for CSP compliance).
 * When a link with data-confirm is clicked, shows a confirmation dialog before navigating.
 */
(function () {
    'use strict';

    function initConfirmLinks() {
        document.addEventListener('click', function (e) {
            const link = e.target.closest('a[data-confirm]');
            if (!link) {
                return;
            }
            e.preventDefault();
            const message = link.getAttribute('data-confirm');
            if (message && window.confirm(message)) {
                window.location.href = link.getAttribute('href') || '';
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initConfirmLinks);
    } else {
        initConfirmLinks();
    }
})();
