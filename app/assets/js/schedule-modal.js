/**
 * Schedule Modal - Gestion de la modal mobile pour détails cours
 * Avec scroll lock pour éviter les bugs
 * Accessible au clavier et à la souris
 */

/**
 * Ouvre la modal avec les détails d'un cours
 * @param {HTMLElement} courseBlock L'élément cours cliqué
 */
function openCourseModal(courseBlock)
{
    const modal = document.getElementById('course-modal');
    if (!modal) {
        return;
    }

    // Extraire les données du cours
    const time = courseBlock.getAttribute('data-time') || '';
    const title = courseBlock.getAttribute('data-title') || '';
    const location = courseBlock.getAttribute('data-location') || '';
    const teacher = courseBlock.getAttribute('data-teacher') || '';

    // Remplir la modal
    document.getElementById('modal-time').textContent = time;
    document.getElementById('modal-title').textContent = title;
    document.getElementById('modal-location').textContent = location ? '📍 ' + location : '';
    document.getElementById('modal-teacher').textContent = teacher ? '👨‍🏫 ' + teacher : '';

    // Afficher la modal
    modal.classList.add('active');
    modal.setAttribute('aria-hidden', 'false');

    // BLOQUER LE SCROLL du body
    document.body.style.overflow = 'hidden';
    document.body.style.position = 'fixed';
    document.body.style.width = '100%';

    // Focus sur le bouton de fermeture pour l'accessibilité
    const closeBtn = modal.querySelector('.modal-close');
    if (closeBtn) {
        closeBtn.focus();
    }
}

/**
 * Ferme la modal
 */
function closeCourseModal()
{
    const modal = document.getElementById('course-modal');
    if (!modal) {
        return;
    }

    // Cacher la modal
    modal.classList.remove('active');
    modal.setAttribute('aria-hidden', 'true');

    // DÉBLOQUER LE SCROLL du body
    document.body.style.overflow = '';
    document.body.style.position = '';
    document.body.style.width = '';
}

/**
 * Gestionnaire d'événements accessible (souris et clavier)
 * Vérifie si l'événement est un clic ou une touche Enter/Space
 * @param {Event} e L'événement (click ou keydown)
 * @returns {boolean} true si l'événement doit déclencher l'action
 */
function isAccessibleEvent(e)
{
    // Gérer les clics de souris
    if (e.type === 'click') {
        return true;
    }
    // Gérer les événements clavier (Enter ou Space)
    if (e.type === 'keydown' && (e.key === 'Enter' || e.key === ' ')) {
        e.preventDefault();
        return true;
    }
    return false;
}

/**
 * Initialiser les event listeners accessibles pour les cours
 */
function initAccessibleCourseListeners()
{
    // Gérer les clics sur les blocs de cours (course-block et calendar-event)
    document.querySelectorAll('.course-block, .calendar-event').forEach(courseBlock => {
        // Supprimer l'ancien onclick s'il existe
        courseBlock.removeAttribute('onclick');

        // Ajouter les event listeners accessibles
        courseBlock.addEventListener('click', function (e) {
            if (isAccessibleEvent(e)) {
                openCourseModal(courseBlock);
            }
        });

        courseBlock.addEventListener('keydown', function (e) {
            if (isAccessibleEvent(e)) {
                openCourseModal(courseBlock);
            }
        });
    });

    // Gérer le modal overlay
    const modalOverlay = document.querySelector('.modal-overlay');
    if (modalOverlay) {
        modalOverlay.removeAttribute('onclick');
        modalOverlay.setAttribute('tabindex', '0');
        modalOverlay.setAttribute('role', 'button');
        modalOverlay.setAttribute('aria-label', 'Fermer la modal');

        modalOverlay.addEventListener('click', function (e) {
            if (isAccessibleEvent(e)) {
                closeCourseModal();
            }
        });

        modalOverlay.addEventListener('keydown', function (e) {
            if (isAccessibleEvent(e)) {
                closeCourseModal();
            }
        });
    }

    // Gérer le bouton de fermeture
    const closeBtn = document.querySelector('.modal-close');
    if (closeBtn) {
        closeBtn.removeAttribute('onclick');

        closeBtn.addEventListener('click', function (e) {
            if (isAccessibleEvent(e)) {
                closeCourseModal();
            }
        });

        closeBtn.addEventListener('keydown', function (e) {
            if (isAccessibleEvent(e)) {
                closeCourseModal();
            }
        });
    }
}

/**
 * Fermer au clic sur Escape
 */
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeCourseModal();
    }
});

// Initialiser les listeners accessibles au chargement de la page
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAccessibleCourseListeners);
} else {
    initAccessibleCourseListeners();
}

/**
 * Réinitialiser les listeners après un chargement AJAX
 */
function resetModalListeners()
{
    initAccessibleCourseListeners();
}

/**
 * Update de l'indicateur de temps actuel (chaque minute)
 */
function updateCurrentTimeIndicator()
{
    const indicator = document.querySelector('.current-time-indicator');
    if (!indicator) {
        return;
    }

    const now = new Date();
    const hours = now.getHours();
    const minutes = now.getMinutes();

    // Seulement entre 08:00 et 20:00
    if (hours < 8 || hours > 20) {
        indicator.style.display = 'none';
        return;
    }

    // Calculer position (minutes depuis 08:00)
    const top = ((hours - 8) * 60) + minutes;

    indicator.style.top = top + 'px';
    indicator.style.display = 'block';

    // Update le label
    const label = indicator.querySelector('.time-label');
    if (label) {
        label.textContent = hours.toString().padStart(2, '0') + ':' + minutes.toString().padStart(2, '0');
    }
}

// Update l'indicateur chaque minute
setInterval(updateCurrentTimeIndicator, 60000);

// Update au chargement de la page
document.addEventListener('DOMContentLoaded', updateCurrentTimeIndicator);
