/**
 * Configurations de confirmation par action admin
 * Utilisées par showConfirmModal (modal.js)
 */
const ADMIN_ACTION_CONFIGS = {
    unblock: {
        title: 'Débloquer l\'utilisateur',
        msg: 'Êtes-vous sûr de vouloir débloquer cet utilisateur ?',
        icon: 'fas fa-unlock',
        confirmText: 'Débloquer',
        danger: false
    },
    restore: {
        title: 'Réactiver l\'utilisateur',
        msg: 'Êtes-vous sûr de vouloir réactiver ce compte supprimé ?',
        icon: 'fas fa-undo',
        confirmText: 'Réactiver',
        danger: false
    },
    promote: {
        title: 'Promouvoir en administrateur',
        msg: 'Êtes-vous sûr de vouloir promouvoir cet utilisateur en tant qu\'admin ?',
        icon: 'fas fa-user-shield',
        confirmText: 'Promouvoir',
        danger: false
    },
    demote: {
        title: 'Rétrograder en utilisateur',
        msg: 'Êtes-vous sûr de vouloir rétrograder cet administrateur en utilisateur normal ?',
        icon: 'fas fa-user-minus',
        confirmText: 'Rétrograder',
        danger: true
    },
    block: {
        title: 'Bloquer l\'utilisateur',
        msg: 'Êtes-vous sûr de vouloir bloquer cet utilisateur ?',
        icon: 'fas fa-ban',
        confirmText: 'Bloquer',
        danger: true
    },
    soft_delete: {
        title: 'Supprimer l\'utilisateur',
        msg: 'Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est réversible par un super-admin.',
        icon: 'fas fa-trash-alt',
        confirmText: 'Supprimer',
        danger: true
    }
};

/**
 * Affiche une modale de confirmation puis exécute le callback si confirmé.
 *
 * @param {string}   action - Clé de ADMIN_ACTION_CONFIGS
 * @param {Function} onConfirm - Fonction à appeler si l'utilisateur confirme
 */
function confirmAdminAction(action, onConfirm) {
    const cfg = ADMIN_ACTION_CONFIGS[action];

    if (!cfg) {
        onConfirm();
        return;
    }

    window.showConfirmModal(
        cfg.title,
        cfg.msg,
        onConfirm,
        null,
        {
            icon: cfg.icon,
            confirmText: cfg.confirmText,
            cancelText: 'Annuler',
            danger: cfg.danger
        }
    );
}

document.addEventListener('click', (e) => {
    if (!e.target.classList.contains('btn-unblock')) return;

    e.preventDefault();

    const button = e.target;
    const form = button.closest('form');
    const action = button.value;

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'action';
    input.value = action;
    form.appendChild(input);

    confirmAdminAction(action, () => submitAdminAction(form));
});

document.addEventListener('change', (e) => {
    if (!e.target.classList.contains('js-admin-select')) return;

    e.preventDefault();

    const select = e.target;
    const form = select.closest('form');
    const action = select.value;

    if (!action) return;

    confirmAdminAction(action, () => submitAdminAction(form));
});

function submitAdminAction(form) {
    const formData = new FormData(form);

    fetch(window.location.pathname + window.location.search, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Erreur: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erreur: ', error);
            alert('Une erreur est survenue lors de la requête.');
        });
}
