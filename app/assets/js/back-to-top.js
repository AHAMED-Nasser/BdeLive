// back-to-top.js
(function() {
    'use strict';
    
    const backToTopButton = document.getElementById('back-to-top');
    
    if (!backToTopButton) {
        return;
    }
    
    // Afficher/masquer le bouton selon la position du scroll
    function toggleBackToTopButton() {
        if (window.pageYOffset > 300) {
            backToTopButton.classList.add('show');
        } else {
            backToTopButton.classList.remove('show');
        }
    }
    
    // Scroll fluide vers le haut
    function scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }
    
    // Écouter le scroll
    window.addEventListener('scroll', toggleBackToTopButton);
    
    // Écouter le clic sur le bouton
    backToTopButton.addEventListener('click', scrollToTop);
    
    // Initialiser l'état du bouton au chargement
    toggleBackToTopButton();
})();
