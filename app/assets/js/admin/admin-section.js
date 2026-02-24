document.addEventListener('click', (e) => {

    if (!e.target.classList.contains('btn-unblock')) return;

    e.preventDefault();

    const button = e.target;
    const form = button.closest('form');

    const input = document.createElement('input');

    input.type = "hidden";
    input.name = "action";
    input.value = button.value;

    form.appendChild(input);
    //form.querySelector('[name="action"]')?.remove();

    const action = button.value;

    if (action === 'unblock' && !confirm('Débloquer cet utilisateur ?')) return;
    if (action === 'restore' && !confirm('Réactiver cet utilisateur ?')) return;

    submitAdminAction(form);
});

document.addEventListener('change', (e) => {

    if (!e.target.classList.contains('js-admin-select')) return;

    e.preventDefault();

    const select = e.target;
    const form = select.closest('form');
    const action = select.value;

    // Confirmation pour promouvoir un utilisateur en tant qu'admin
    if (action === 'promote' && !confirm('Promouvoir cet utilisateur en tant qu\'admin ?')) return;
    if (action === 'demote' && !confirm('Rétrograder cet admin en utilisateur normal ?')) return;
    if (action === 'block' && !confirm('Bloquer cet utilisateur ?')) return;
    if (action === 'soft_delete' && !confirm('Suppremier cet utilisateur ?')) return;

    submitAdminAction(form);
});

function submitAdminAction(form) {
    const formData = new FormData(form);

    fetch(window.location.pathname + window.location.search, { // On envoie à l'URL actuelle
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