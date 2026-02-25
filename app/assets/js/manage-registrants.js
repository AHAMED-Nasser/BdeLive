/**
 * Manage Registrants Script
 *
 * Handles the admin UI for batch managing event registrants:
 * - Toggles manage mode (shows/hides checkboxes, delete button, add panel)
 * - Select all / deselect all checkbox logic
 * - Enables/disables submit button based on selection
 * - Confirmation dialog before batch deletion
 * - AJAX user search with debounce for adding registrants
 * - Searchable dropdown with checkboxes for multi-select
 * - Selected user chips with remove capability
 * - Auto-detection of search mode (user_id vs name)
 *
 * @author BdeLive - Group 8
 * @version 1.1.0
 */

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // ===================================================================
    // DOM references
    // ===================================================================

    var toggleBtn = document.getElementById('btn-toggle-manage');
    var registrantsSection = document.getElementById('registrants-section');
    var removeForm = document.getElementById('manage-registrants-form');
    var selectAllCheckbox = document.getElementById('select-all-registrants');
    var removeBtn = document.getElementById('btn-remove-registrants');

    // Add panel elements
    var searchInput = document.getElementById('search-user-input');
    var dropdown = document.getElementById('search-results-dropdown');
    var chipsContainer = document.getElementById('selected-users-chips');
    var addForm = document.getElementById('add-registrants-form');
    var addBtn = document.getElementById('btn-add-registrants');
    var addIdsContainer = document.getElementById('add-user-ids-container');

    // Exit early if toggle button or section are not on the page
    if (!toggleBtn || !registrantsSection) {
        return;
    }

    var checkboxes = removeForm ? removeForm.querySelectorAll('.registrant-checkbox') : [];
    var eventId = registrantsSection.getAttribute('data-event-id');

    // Selected users for add panel: Map of userId -> {first_name, last_name, user_status}
    var selectedUsers = {};
    var searchTimer = null;
    var currentQuery = '';

    // ===================================================================
    // Toggle manage mode
    // ===================================================================

    toggleBtn.addEventListener('click', function () {
        var isActive = registrantsSection.classList.toggle('manage-mode');
        toggleBtn.classList.toggle('active', isActive);

        // Reset state when exiting manage mode
        if (!isActive) {
            // Reset remove checkboxes
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = false;
            }
            if (checkboxes.length > 0) {
                for (var i = 0; i < checkboxes.length; i++) {
                    checkboxes[i].checked = false;
                }
            }
            updateRemoveButton();

            // Reset add panel
            resetAddPanel();
        }
    });

    // ===================================================================
    // Remove registrants — checkbox logic
    // ===================================================================

    /**
     * Update the remove button disabled state
     */
    function updateRemoveButton() {
        if (!removeBtn) return;
        var anyChecked = false;
        for (var i = 0; i < checkboxes.length; i++) {
            if (checkboxes[i].checked) {
                anyChecked = true;
                break;
            }
        }
        removeBtn.disabled = !anyChecked;
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            var isChecked = selectAllCheckbox.checked;
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = isChecked;
            }
            updateRemoveButton();
        });
    }

    if (checkboxes.length > 0) {
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].addEventListener('change', function () {
                var allChecked = true;
                for (var j = 0; j < checkboxes.length; j++) {
                    if (!checkboxes[j].checked) {
                        allChecked = false;
                        break;
                    }
                }
                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = allChecked;
                }
                updateRemoveButton();
            });
        }
    }

    // Remove form submit confirmation
    if (removeForm) {
        removeForm.addEventListener('submit', function (e) {
            var selectedCount = 0;
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i].checked) {
                    selectedCount++;
                }
            }

            if (selectedCount === 0) {
                e.preventDefault();
                return;
            }

            var message = selectedCount === 1
                ? 'Êtes-vous sûr de vouloir supprimer 1 inscrit ?'
                : 'Êtes-vous sûr de vouloir supprimer ' + selectedCount + ' inscrits ?';

            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    }

    // ===================================================================
    // Add registrants — AJAX search
    // ===================================================================

    if (!searchInput || !dropdown || !addForm) {
        return;
    }

    /**
     * Reset all add panel state
     */
    function resetAddPanel() {
        searchInput.value = '';
        dropdown.classList.remove('visible');
        dropdown.innerHTML = '';
        selectedUsers = {};
        renderChips();
        updateAddButton();
        currentQuery = '';
    }

    /**
     * Debounced user search via AJAX
     */
    searchInput.addEventListener('input', function () {
        var query = searchInput.value.trim();

        // Clear previous timer
        if (searchTimer) {
            clearTimeout(searchTimer);
        }

        // Hide dropdown if query is too short
        if (query.length < 1) {
            dropdown.classList.remove('visible');
            dropdown.innerHTML = '';
            currentQuery = '';
            return;
        }

        currentQuery = query;

        // Show loading state
        dropdown.innerHTML = '<div class="search-loading">Recherche...</div>';
        dropdown.classList.add('visible');

        // Debounce 300ms
        searchTimer = setTimeout(function () {
            performSearch(query);
        }, 300);
    });

    /**
     * Perform AJAX search for users
     *
     * @param {string} query The search query
     */
    function performSearch(query) {
        var url = 'index.php?page=searchUsers&event_id=' + encodeURIComponent(eventId) + '&q=' + encodeURIComponent(query);

        fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                // Only render if this is still the current query
                if (query !== currentQuery) return;

                if (data.results && data.results.length > 0) {
                    renderResults(data.results);
                } else {
                    dropdown.innerHTML = '<div class="search-no-results">Aucun utilisateur trouvé</div>';
                }
                dropdown.classList.add('visible');
            })
            .catch(function () {
                if (query !== currentQuery) return;
                dropdown.innerHTML = '<div class="search-no-results">Erreur de recherche</div>';
                dropdown.classList.add('visible');
            });
    }

    /**
     * Render search results in the dropdown
     *
     * @param {Array} results Array of user objects from the API
     */
    function renderResults(results) {
        dropdown.innerHTML = '';

        for (var i = 0; i < results.length; i++) {
            var user = results[i];
            var isSelected = selectedUsers.hasOwnProperty(user.user_id);

            var item = document.createElement('div');
            item.className = 'search-result-item' + (isSelected ? ' selected' : '');
            item.setAttribute('data-user-id', user.user_id);

            var checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.checked = isSelected;
            checkbox.setAttribute('data-user-id', user.user_id);

            var nameSpan = document.createElement('span');
            nameSpan.className = 'search-result-name';
            nameSpan.textContent = user.last_name + ' ' + user.first_name;

            var infoSpan = document.createElement('span');
            infoSpan.className = 'search-result-info';
            infoSpan.textContent = (user.user_status || 'N/A') + ' — ID: ' + user.user_id;

            item.appendChild(checkbox);
            item.appendChild(nameSpan);
            item.appendChild(infoSpan);

            // Click handler (closure for user data)
            (function (userData, itemEl, checkboxEl) {
                itemEl.addEventListener('click', function (e) {
                    // Prevent double-triggering from checkbox
                    if (e.target === checkboxEl) return;
                    checkboxEl.checked = !checkboxEl.checked;
                    toggleUserSelection(userData, checkboxEl.checked, itemEl);
                });

                checkboxEl.addEventListener('change', function () {
                    toggleUserSelection(userData, checkboxEl.checked, itemEl);
                });
            })(user, item, checkbox);

            dropdown.appendChild(item);
        }
    }

    /**
     * Toggle a user's selection state
     *
     * @param {Object} user User data object
     * @param {boolean} isSelected Whether the user is now selected
     * @param {HTMLElement} itemEl The dropdown item element
     */
    function toggleUserSelection(user, isSelected, itemEl) {
        if (isSelected) {
            selectedUsers[user.user_id] = {
                first_name: user.first_name,
                last_name: user.last_name,
                user_status: user.user_status
            };
            itemEl.classList.add('selected');
        } else {
            delete selectedUsers[user.user_id];
            itemEl.classList.remove('selected');
        }
        renderChips();
        updateAddButton();
    }

    /**
     * Render selected user chips
     */
    function renderChips() {
        chipsContainer.innerHTML = '';

        var userIds = Object.keys(selectedUsers);
        for (var i = 0; i < userIds.length; i++) {
            var userId = userIds[i];
            var user = selectedUsers[userId];

            var chip = document.createElement('span');
            chip.className = 'user-chip';

            var text = document.createTextNode(user.last_name + ' ' + user.first_name);
            chip.appendChild(text);

            var removeChipBtn = document.createElement('button');
            removeChipBtn.type = 'button';
            removeChipBtn.className = 'user-chip-remove';
            removeChipBtn.textContent = '✕';
            removeChipBtn.title = 'Retirer';

            // Closure for userId
            (function (uid) {
                removeChipBtn.addEventListener('click', function () {
                    delete selectedUsers[uid];
                    renderChips();
                    updateAddButton();
                    // Update dropdown checkbox if visible
                    var dropdownCheckbox = dropdown.querySelector('input[data-user-id="' + uid + '"]');
                    if (dropdownCheckbox) {
                        dropdownCheckbox.checked = false;
                        dropdownCheckbox.closest('.search-result-item').classList.remove('selected');
                    }
                });
            })(userId);

            chip.appendChild(removeChipBtn);
            chipsContainer.appendChild(chip);
        }
    }

    /**
     * Update add button state and hidden inputs
     */
    function updateAddButton() {
        var userIds = Object.keys(selectedUsers);
        var hasSelected = userIds.length > 0;

        addBtn.disabled = !hasSelected;

        // Update hidden inputs
        addIdsContainer.innerHTML = '';
        for (var i = 0; i < userIds.length; i++) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'user_ids[]';
            input.value = userIds[i];
            addIdsContainer.appendChild(input);
        }
    }

    /**
     * Add form submit confirmation
     */
    addForm.addEventListener('submit', function (e) {
        var count = Object.keys(selectedUsers).length;

        if (count === 0) {
            e.preventDefault();
            return;
        }

        var message = count === 1
            ? 'Ajouter 1 inscrit à cet événement ?'
            : 'Ajouter ' + count + ' inscrits à cet événement ?';

        if (!confirm(message)) {
            e.preventDefault();
        }
    });

    /**
     * Close dropdown when clicking outside
     */
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.search-container')) {
            dropdown.classList.remove('visible');
        }
    });

    /**
     * Re-open dropdown on focus if there's a query
     */
    searchInput.addEventListener('focus', function () {
        if (searchInput.value.trim().length >= 1 && dropdown.children.length > 0) {
            dropdown.classList.add('visible');
        }
    });
});
