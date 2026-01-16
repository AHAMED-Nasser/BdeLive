/**
 * Delete Account JavaScript
 *
 * Gère la validation de l'email et empêche le copier-coller
 *
 * @author BdeLive Team
 * @version 1.0.0
 */

(function () {
    'use strict';

    let userEmail = '';
    let emailInput = null;
    let deleteBtn = null;
    let emailError = null;
    let hasTyped = false;

    /**
     * Initialise la fonctionnalité de suppression de compte
     *
     * @param {string} email - L'email de l'utilisateur à confirmer
     */
    function initDeleteAccount(email)
    {
        userEmail = email.toLowerCase().trim();
        emailInput = document.getElementById('confirm-email');
        deleteBtn = document.getElementById('deleteBtn');
        emailError = document.getElementById('email-error');
        const form = document.getElementById('deleteAccountForm');

        if (!emailInput || !deleteBtn || !form) {
            console.error('Delete account: éléments manquants');
            return;
        }

        // Empêcher le copier-coller
        preventPaste(emailInput);

        // Empêcher le glisser-déposer
        preventDragDrop(emailInput);

        // Désactiver l'autocomplétion
        emailInput.setAttribute('autocomplete', 'off');
        emailInput.setAttribute('spellcheck', 'false');

        // Suivre les changements
        emailInput.addEventListener('input', handleInput);
        emailInput.addEventListener('keydown', handleKeyDown);
        emailInput.addEventListener('focus', handleFocus);

        // Validation du formulaire
        form.addEventListener('submit', handleSubmit);

        // Désactiver le bouton initialement
        deleteBtn.disabled = true;
    }

    /**
     * Empêche le copier-coller dans le champ
     *
     * @param {HTMLElement} input - Le champ input
     */
    function preventPaste(input)
    {
        // Empêcher Ctrl+V / Cmd+V
        input.addEventListener('paste', function (e) {
            e.preventDefault();
            showError('Le copier-coller est désactivé. Veuillez taper votre email manuellement.');
            input.classList.add('error');
            input.classList.remove('success');
            deleteBtn.disabled = true;
            return false;
        });

        // Empêcher le menu contextuel (clic droit)
        input.addEventListener('contextmenu', function (e) {
            e.preventDefault();
            return false;
        });

        // Empêcher Ctrl+C, Ctrl+X, Ctrl+A
        input.addEventListener('keydown', function (e) {
            // Ctrl+C, Ctrl+X, Ctrl+A, Ctrl+V
            if ((e.ctrlKey || e.metaKey) && (e.key === 'c' || e.key === 'x' || e.key === 'a' || e.key === 'v')) {
                e.preventDefault();
                showError('Le copier-coller est désactivé. Veuillez taper votre email manuellement.');
                input.classList.add('error');
                input.classList.remove('success');
                deleteBtn.disabled = true;
                return false;
            }
        });
    }

    /**
     * Empêche le glisser-déposer dans le champ
     *
     * @param {HTMLElement} input - Le champ input
     */
    function preventDragDrop(input)
    {
        input.addEventListener('dragenter', function (e) {
            e.preventDefault();
            return false;
        });

        input.addEventListener('dragover', function (e) {
            e.preventDefault();
            return false;
        });

        input.addEventListener('drop', function (e) {
            e.preventDefault();
            showError('Le glisser-déposer est désactivé. Veuillez taper votre email manuellement.');
            input.classList.add('error');
            input.classList.remove('success');
            deleteBtn.disabled = true;
            return false;
        });
    }

    /**
     * Gère la saisie dans le champ
     */
    function handleInput(e)
    {
        hasTyped = true;
        const value = e.target.value.toLowerCase().trim();

        // Réinitialiser les classes
        emailInput.classList.remove('error', 'success');
        hideError();

        if (value === '') {
            deleteBtn.disabled = true;
            return;
        }

        // Vérifier si l'email correspond
        if (value === userEmail) {
            emailInput.classList.add('success');
            emailInput.classList.remove('error');
            deleteBtn.disabled = false;
        } else {
            emailInput.classList.add('error');
            emailInput.classList.remove('success');
            deleteBtn.disabled = true;

            if (value.length >= userEmail.length) {
                showError('L\'email saisi ne correspond pas à votre email actuel.');
            }
        }
    }

    /**
     * Gère les touches du clavier
     */
    function handleKeyDown(e)
    {
        // Détecter si l'utilisateur essaie de coller avec Shift+Insert
        if (e.shiftKey && e.key === 'Insert') {
            e.preventDefault();
            showError('Le copier-coller est désactivé. Veuillez taper votre email manuellement.');
            emailInput.classList.add('error');
            emailInput.classList.remove('success');
            deleteBtn.disabled = true;
            return false;
        }
    }

    /**
     * Gère le focus sur le champ
     */
    function handleFocus()
    {
        // Vérifier si le presse-papiers a été modifié (détection indirecte)
        if (!hasTyped && emailInput.value.length > 0) {
            // Si du texte apparaît sans avoir tapé, c'est suspect
            emailInput.value = '';
            showError('Veuillez taper votre email manuellement.');
            emailInput.classList.add('error');
            emailInput.classList.remove('success');
            deleteBtn.disabled = true;
        }
    }

    /**
     * Gère la soumission du formulaire
     */
    function handleSubmit(e)
    {
        const value = emailInput.value.toLowerCase().trim();

        if (value !== userEmail) {
            e.preventDefault();
            showError('L\'email saisi ne correspond pas à votre email actuel. Veuillez réessayer.');
            emailInput.classList.add('error');
            emailInput.classList.remove('success');
            deleteBtn.disabled = true;
            emailInput.focus();
            return false;
        }

        // Afficher une dernière confirmation
        if (!confirm('Êtes-vous absolument sûr de vouloir supprimer votre compte ? Cette action est irréversible.')) {
            e.preventDefault();
            return false;
        }

        // Le formulaire peut être soumis
        return true;
    }

    /**
     * Affiche un message d'erreur
     *
     * @param {string} message - Le message d'erreur
     */
    function showError(message)
    {
        if (emailError) {
            emailError.textContent = message;
            emailError.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + message;
            emailError.classList.add('show');
        }
    }

    /**
     * Cache le message d'erreur
     */
    function hideError()
    {
        if (emailError) {
            emailError.classList.remove('show');
        }
    }

    // Exposer la fonction globalement
    window.initDeleteAccount = initDeleteAccount;
})();
