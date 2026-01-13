/**
 * Form Submit Protection
 * 
 * Protège les formulaires contre les doubles soumissions en :
 * - Désactivant le bouton de soumission après le premier clic
 * - Affichant un indicateur de chargement
 * - Empêchant les soumissions multiples
 * 
 * @package BdeLive\Assets\JS
 * @version 1.0.0
 */

(function() {
    'use strict';
    
    /**
     * Initialise la protection pour tous les formulaires avec la classe 'protected-form'
     * ou pour un formulaire spécifique
     */
    function initFormProtection() {
        // Protéger tous les formulaires avec la classe 'protected-form'
        const protectedForms = document.querySelectorAll('form.protected-form');
        protectedForms.forEach(form => {
            protectForm(form);
        });
        
        // Protéger les formulaires de création/modification d'articles et événements
        const createArticleForm = document.querySelector('form[action*="createArticle"]');
        const updateArticleForm = document.querySelector('form[action*="updateArticle"]');
        const createEventForm = document.querySelector('form[action*="createEvent"]');
        const updateEventForm = document.querySelector('form[action*="updateEvent"]');
        
        if (createArticleForm) protectForm(createArticleForm);
        if (updateArticleForm) protectForm(updateArticleForm);
        if (createEventForm) protectForm(createEventForm);
        if (updateEventForm) protectForm(updateEventForm);
    }
    
    /**
     * Protège un formulaire spécifique contre les doubles soumissions
     * 
     * @param {HTMLFormElement} form Le formulaire à protéger
     */
    function protectForm(form) {
        const submitButton = form.querySelector('button[type="submit"]');
        if (!submitButton) return;
        
        let isSubmitting = false;
        const originalButtonText = submitButton.textContent || submitButton.innerHTML;
        
        form.addEventListener('submit', function(e) {
            // Si déjà en cours de soumission, empêcher la nouvelle soumission
            if (isSubmitting) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
            
            // Marquer comme en cours de soumission
            isSubmitting = true;
            
            // Désactiver le bouton
            submitButton.disabled = true;
            submitButton.style.opacity = '0.6';
            submitButton.style.cursor = 'not-allowed';
            
            // Afficher un indicateur de chargement
            const loadingText = submitButton.dataset.loadingText || 'Traitement en cours...';
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + loadingText;
            
            // Ne PAS désactiver les champs car cela empêche l'envoi des données POST
            // Au lieu de cela, on rend les champs readonly pour éviter les modifications
            // SAUF pour les checkboxes et radio buttons qui doivent rester tels quels
            const formFields = form.querySelectorAll('input:not([type="checkbox"]):not([type="radio"]):not([type="file"]), textarea, select');
            formFields.forEach(field => {
                if (!field.readOnly && !field.disabled) {
                    field.readOnly = true;
                    field.setAttribute('data-was-readonly', 'true');
                    field.style.backgroundColor = '#f5f5f5';
                }
            });
            
            // Si le formulaire est annulé (par exemple, validation HTML5), réactiver
            form.addEventListener('invalid', function() {
                isSubmitting = false;
                submitButton.disabled = false;
                submitButton.style.opacity = '1';
                submitButton.style.cursor = 'pointer';
                submitButton.innerHTML = originalButtonText;
                
                // Réactiver les champs
                formFields.forEach(field => {
                    if (field.getAttribute('data-was-readonly') === 'true') {
                        field.readOnly = false;
                        field.removeAttribute('data-was-readonly');
                        field.style.backgroundColor = '';
                    }
                });
            }, { once: true });
        });
        
        // Protection supplémentaire : empêcher les clics multiples sur le bouton
        submitButton.addEventListener('click', function(e) {
            if (isSubmitting) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        });
    }
    
    // Initialiser quand le DOM est prêt
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initFormProtection);
    } else {
        initFormProtection();
    }
})();
