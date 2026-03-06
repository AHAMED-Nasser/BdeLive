/**
 * Schedule Filters Management
 * Allows year and group selection with automatic scroll to schedule display
 *
 * @author BdeLive Team
 * @version 1.0.0
 */

(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const yearSelect = document.getElementById('year-select');
        const groupSelect = document.getElementById('group-select');

        if (!yearSelect || !groupSelect) {
            return;
        }

        // Available groups by year (will be injected from PHP)
        const groupsByYear = window.scheduleGroupsData || {};

        /**
         * Updates the group list based on the selected year
         */
        yearSelect.addEventListener('change', function () {
            const selectedYear = this.value;
            groupSelect.innerHTML = '<option value="">-- Sélectionner un groupe --</option>';

            if (selectedYear && groupsByYear[selectedYear]) {
                Object.entries(groupsByYear[selectedYear]).forEach(function ([key, label]) {
                    const option = document.createElement('option');
                    option.value = key;
                    option.textContent = label;
                    groupSelect.appendChild(option);
                });
            }
            updateUrl();
        });

        /**
         * Updates the URL when the group changes
         */
        groupSelect.addEventListener('change', updateUrl);

        /**
         * Met à jour l'URL / ou charge la vue via AJAX selon le contexte.
         * Si scheduleLoadView est disponible et qu'un groupe est sélectionné,
         * on utilise l'AJAX (loadView) pour éviter le rechargement complet.
         * Sinon, on retombe sur le comportement de redirection classique.
         */
        function updateUrl()
        {
            const year = yearSelect.value;
            const group = groupSelect.value;

            if (!year) {
                return;
            }

            // Si la fonction AJAX globale est dispo ET qu'un groupe est choisi,
            // on charge la vue dynamiquement sans recharger la page.
            if (typeof window.scheduleLoadView === 'function' && group) {
                const urlParams = new URLSearchParams(window.location.search);
                const currentView = urlParams.get('view') || 'week';

                window.scheduleLoadView(currentView, {
                    year: year,
                    group: group
                });
            } else {
                // Fallback : mise à jour classique de l'URL sans scroll forcé
                const params = new URLSearchParams();
                params.set('page', 'schedule');
                params.set('year', year);
                if (group) {
                    params.set('group', group);
                }

                window.location.href = 'index.php?' + params.toString();
            }
        }

        // Plus de scroll automatique : l'utilisateur garde le contrôle de la position
    });

})();
