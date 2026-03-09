/**
 * Confirmation avant suppression via modale (articles, événements)
 * Intercepte le clic sur les boutons [data-confirm-msg].btn-delete
 * et affiche une modale de confirmation au lieu du confirm() natif.
 */
(function () {
    'use strict';

    function init() {
        document.querySelectorAll('button[data-confirm-msg].btn-delete').forEach(function (btn) {
            var form = btn.closest('form');
            if (!form) return;

            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                var msg   = btn.getAttribute('data-confirm-msg')   || 'Êtes-vous sûr de vouloir supprimer ? Cette action est irréversible.';
                var title = btn.getAttribute('data-confirm-title') || 'Confirmer la suppression';

                window.showConfirmModal(
                    title,
                    msg,
                    function () {
                        form.submit();
                    },
                    null,
                    {
                        icon:        'fas fa-trash-alt',
                        confirmText: 'Supprimer',
                        cancelText:  'Annuler',
                        danger:      true
                    }
                );
            }, true);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
