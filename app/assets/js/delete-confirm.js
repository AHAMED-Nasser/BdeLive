/**
 * Confirmation avant suppression (articles, événements)
 * Intercepte le clic sur le bouton pour afficher la confirmation AVANT la soumission.
 */
(function () {
    'use strict';

    function init() {
        document.querySelectorAll('button[data-confirm-msg].btn-delete').forEach(function (btn) {
            var form = btn.closest('form');
            if (!form) return;

            btn.addEventListener('click', function (e) {
                var msg = btn.getAttribute('data-confirm-msg') || 'Êtes-vous sûr de vouloir supprimer ? Cette action est irréversible.';
                if (!confirm(msg)) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            }, true);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
