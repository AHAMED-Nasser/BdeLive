document.addEventListener('change', (e) => {
    // Si l'élément qui a changé est notre select admin
    if (e.target.classList.contains('js-admin-select')) {
        const select = e.target;
        const form = select.closest('form');
        const action = select.value;

        if (!action) return;

        // Confirmation pour promouvoir un utilisateur en tant qu'admin
        if (action === 'promote' && !confirm('Promouvoir cet utilisateur en tant qu\'admin ?')) {
            select.value = "";
            return;
        }

        // Confirmation pour rétrograder un admin en utilisateur normal
        if (action === 'demote' && !confirm('Rétrograder cet admin en utilisateur normal ?')) {
            select.value = "";
            return;
        }

        // Confirmation pour bloquer un utilisateur
        if (action === 'block' && !confirm('Bloquer cet utilisateur ?')) {
            select.value = "";
            return;
        }

        // Confirmation pour débloquer un utilisateur
        if (action === 'unblock' && !confirm('Débloquer cet utilisateur ?')) {
            select.value = "";
            return;
        }

        // Optionnel : Confirmation pour la suppression d'un utilisateur (case sensible)
        if (action === 'soft_delete' && !confirm('Suppremier cet utilisateur ?')) {
            select.value = ""; // On reset le select pour éviter une action non désirée
            return;
        }

        // Confirmation pour réactiver un utilisateur
        if (action === 'restore' && !confirm('Réactiver cet utilisateur ?')) {
            select.value = "";
            return;
        }

        submitAdminAction(form);
    }
});

function submitAdminAction(form) {
    const formData = new FormData(form);

    fetch(window.location.href, { // On envoie à l'URL actuelle
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest' // On indique à PHP de détecter l'AJAX
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // La page sera rechargé mais on aura une expérience plus fluide
            window.location.reload();
        } else {
            alert("Erreur: " + data.message);
        }
    })
    .catch(error => {
        console.error('Erreur: ', error);
        alert("Une erreur est survenue lors de la requête.");
    })
}