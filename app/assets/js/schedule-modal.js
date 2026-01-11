/**
 * Schedule Modal - Gestion de la modal mobile pour détails cours
 * Avec scroll lock pour éviter les bugs
 */

/**
 * Ouvre la modal avec les détails d'un cours
 * @param {HTMLElement} courseBlock L'élément cours cliqué
 */
function openCourseModal(courseBlock) {
    const modal = document.getElementById('course-modal');
    if (!modal) return;

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
}

/**
 * Ferme la modal
 */
function closeCourseModal() {
    const modal = document.getElementById('course-modal');
    if (!modal) return;

    // Cacher la modal
    modal.classList.remove('active');
    modal.setAttribute('aria-hidden', 'true');

    // DÉBLOQUER LE SCROLL du body
    document.body.style.overflow = '';
    document.body.style.position = '';
    document.body.style.width = '';
}

/**
 * Fermer au clic sur Escape
 */
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeCourseModal();
    }
});

/**
 * Update de l'indicateur de temps actuel (chaque minute)
 */
function updateCurrentTimeIndicator() {
    const indicator = document.querySelector('.current-time-indicator');
    if (!indicator) return;

    const now = new Date();
    const hours = now.getHours();
    const minutes = now.getMinutes();

    // Seulement entre 07:00 et 20:00
    if (hours < 7 || hours > 20) {
        indicator.style.display = 'none';
        return;
    }

    // Calculer position (minutes depuis 07:00)
    const top = ((hours - 7) * 60) + minutes;

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
