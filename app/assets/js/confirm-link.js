(function () {
    'use strict'; function initConfirmLinks() {
        document.addEventListener('click', function (e) {
            const link = e.target.closest('a[data-confirm]'); if (!link) { return }
            e.preventDefault(); const message = link.getAttribute('data-confirm'); if (message && window.confirm(message)) { window.location.href = link.getAttribute('href') || '' }
        })
    }
    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', initConfirmLinks) } else { initConfirmLinks() }
})()
