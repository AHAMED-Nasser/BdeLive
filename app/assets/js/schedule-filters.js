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
         * Redirects to the new URL with selected parameters
         * Adds a hash for automatic scroll if a group is selected
         */
        function updateUrl()
        {
            const year = yearSelect.value;
            const group = groupSelect.value;

            if (year) {
                const params = new URLSearchParams();
                params.set('page', 'schedule');
                params.set('year', year);
                if (group) {
                    params.set('group', group);
                }

                // Add hash for automatic scroll to schedule
                const hash = group ? '#calendar-view' : '';
                window.location.href = 'index.php?' + params.toString() + hash;
            }
        }

        /**
         * Automatic scroll to schedule if hash is present in URL
         */
        function autoScrollToCalendar()
        {
            if (window.location.hash === '#calendar-view') {
                // Wait briefly for page to be fully loaded
                setTimeout(function () {
                    const calendarView = document.getElementById('calendar-view');
                    if (calendarView) {
                        calendarView.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }, 100);
            }
        }

        // Execute automatic scroll on page load
        autoScrollToCalendar();
    });

})();
