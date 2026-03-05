/**
 * Modal JavaScript
 *
 * Système de modales pour les confirmations
 *
 * @author BdeLive Team
 * @version 1.0.0
 */

(function () {
    'use strict';

    /**
     * Affiche une modale de confirmation
     *
     * @param {string} title - Titre de la modale
     * @param {string} message - Message à afficher
     * @param {Function} onConfirm - Fonction à exécuter si confirmé
     * @param {Function|null} onCancel - Fonction à exécuter si annulé (optionnel)
     * @param {Object} options - Options supplémentaires (icon, confirmText, cancelText, danger)
     */
    function showConfirmModal(title, message, onConfirm, onCancel, options = {})
    {
        // Options par défaut
        const defaults = {
            icon: 'fas fa-question-circle',
            confirmText: 'Confirmer',
            cancelText: 'Annuler',
            danger: false
        };

        const opts = Object.assign({}, defaults, options);

        // Créer l'overlay
        const overlay = document.createElement('div');
        overlay.className = 'modal-overlay';
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-modal', 'true');
        overlay.setAttribute('aria-labelledby', 'modal-title');

        // Créer le container
        const container = document.createElement('div');
        container.className = 'modal-container';

        // Créer l'en-tête
        const header = document.createElement('div');
        header.className = 'modal-header';

        const icon = document.createElement('i');
        icon.className = opts.icon + ' modal-icon';
        icon.setAttribute('aria-hidden', 'true');

        const titleEl = document.createElement('h2');
        titleEl.id = 'modal-title';
        titleEl.textContent = title;

        const closeBtn = document.createElement('button');
        closeBtn.className = 'modal-close';
        closeBtn.setAttribute('aria-label', 'Fermer la modale');
        closeBtn.innerHTML = '<i class="fas fa-times" aria-hidden="true"></i>';
        closeBtn.addEventListener('click', () => {
            closeModal();
            if (onCancel) {
                onCancel();
            }
        });

        header.appendChild(icon);
        header.appendChild(titleEl);
        header.appendChild(closeBtn);

        // Créer le corps
        const body = document.createElement('div');
        body.className = 'modal-body';
        const messageEl = document.createElement('p');
        messageEl.textContent = message;
        body.appendChild(messageEl);

        // Créer le pied
        const footer = document.createElement('div');
        footer.className = 'modal-footer';

        const cancelBtn = document.createElement('button');
        cancelBtn.className = 'modal-btn modal-btn-secondary';
        cancelBtn.textContent = opts.cancelText;
        cancelBtn.addEventListener('click', () => {
            closeModal();
            if (onCancel) {
                onCancel();
            }
        });

        const confirmBtn = document.createElement('button');
        confirmBtn.className = 'modal-btn ' + (opts.danger ? 'modal-btn-danger' : 'modal-btn-primary');
        confirmBtn.textContent = opts.confirmText;
        confirmBtn.addEventListener('click', () => {
            closeModal();
            if (onConfirm) {
                onConfirm();
            }
        });

        footer.appendChild(cancelBtn);
        footer.appendChild(confirmBtn);

        // Assembler la modale
        container.appendChild(header);
        container.appendChild(body);
        container.appendChild(footer);
        overlay.appendChild(container);

        // Ajouter au DOM
        document.body.appendChild(overlay);

        // Fonction pour fermer la modale
        function closeModal()
        {
            overlay.classList.remove('show');
            setTimeout(() => {
                if (overlay.parentNode) {
                    overlay.parentNode.removeChild(overlay);
                }
            }, 300);
        }

        // Gérer la touche Escape
        function handleEscape(e)
        {
            if (e.key === 'Escape') {
                closeModal();
                if (onCancel) {
                    onCancel();
                }
                document.removeEventListener('keydown', handleEscape);
            }
        }

        document.addEventListener('keydown', handleEscape);

        // Fermer au clic sur l'overlay
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                closeModal();
                if (onCancel) {
                    onCancel();
                }
            }
        });

        // Focus sur le bouton de confirmation
        setTimeout(() => {
            confirmBtn.focus();
        }, 100);

        // Afficher la modale avec animation
        setTimeout(() => {
            overlay.classList.add('show');
        }, 10);
    }

    /**
     * Affiche une modale de notification (un seul bouton OK, sans annulation)
     *
     * @param {string} title   - Titre de la modale
     * @param {string} message - Message à afficher
     * @param {Object} options - Options : type ('error'|'success'|'info'), confirmText
     */
    function showNotificationModal(title, message, options = {})
    {
        const typeDefaults = {
            error:   { icon: 'fas fa-circle-exclamation', danger: true },
            success: { icon: 'fas fa-circle-check',       danger: false },
            info:    { icon: 'fas fa-circle-info',        danger: false }
        };

        const type = options.type || 'info';
        const typeOpts = typeDefaults[type] || typeDefaults.info;

        const opts = Object.assign(
            { confirmText: 'OK' },
            typeOpts,
            options
        );

        // Overlay
        const overlay = document.createElement('div');
        overlay.className = 'modal-overlay';
        overlay.setAttribute('role', 'alertdialog');
        overlay.setAttribute('aria-modal', 'true');
        overlay.setAttribute('aria-labelledby', 'modal-notif-title');

        // Container
        const container = document.createElement('div');
        container.className = 'modal-container';

        // Header
        const header = document.createElement('div');
        header.className = 'modal-header';

        const icon = document.createElement('i');
        icon.className = opts.icon + ' modal-icon';
        icon.setAttribute('aria-hidden', 'true');

        const titleEl = document.createElement('h2');
        titleEl.id = 'modal-notif-title';
        titleEl.textContent = title;

        const closeBtn = document.createElement('button');
        closeBtn.className = 'modal-close';
        closeBtn.setAttribute('aria-label', 'Fermer la modale');
        closeBtn.innerHTML = '<i class="fas fa-times" aria-hidden="true"></i>';
        closeBtn.addEventListener('click', closeModal);

        header.appendChild(icon);
        header.appendChild(titleEl);
        header.appendChild(closeBtn);

        // Body
        const body = document.createElement('div');
        body.className = 'modal-body';
        const messageEl = document.createElement('p');
        messageEl.textContent = message;
        body.appendChild(messageEl);

        // Footer — bouton OK uniquement
        const footer = document.createElement('div');
        footer.className = 'modal-footer';

        const confirmBtn = document.createElement('button');
        confirmBtn.className = 'modal-btn ' + (opts.danger ? 'modal-btn-danger' : 'modal-btn-primary');
        confirmBtn.textContent = opts.confirmText;
        confirmBtn.addEventListener('click', closeModal);

        footer.appendChild(confirmBtn);

        container.appendChild(header);
        container.appendChild(body);
        container.appendChild(footer);
        overlay.appendChild(container);
        document.body.appendChild(overlay);

        function closeModal()
        {
            overlay.classList.remove('show');
            setTimeout(() => {
                if (overlay.parentNode) {
                    overlay.parentNode.removeChild(overlay);
                }
            }, 300);
        }

        function handleEscape(e)
        {
            if (e.key === 'Escape') {
                closeModal();
                document.removeEventListener('keydown', handleEscape);
            }
        }

        document.addEventListener('keydown', handleEscape);

        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                closeModal();
            }
        });

        setTimeout(() => { confirmBtn.focus(); }, 100);
        setTimeout(() => { overlay.classList.add('show'); }, 10);
    }

    // Exposer les fonctions globalement
    window.showConfirmModal = showConfirmModal;
    window.showNotificationModal = showNotificationModal;

    // Remplacer les confirm() existants si nécessaire
    // Cette fonction peut être appelée pour remplacer confirm() par défaut
    window.replaceConfirmWithModal = function () {
        const originalConfirm = window.confirm;
        window.confirm = function (message) {
            return new Promise((resolve) => {
                showConfirmModal(
                    'Confirmation',
                    message,
                    () => resolve(true),
                    () => resolve(false),
                    {
                        icon: 'fas fa-question-circle',
                        confirmText: 'OK',
                        cancelText: 'Annuler'
                    }
                );
            });
        };
    };
})();
