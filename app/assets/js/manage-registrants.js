/**
 * Manage Registrants Script
 *
 * Handles the admin UI for batch managing event registrants on both
 * individual and group events.
 *
 * Individual events:
 * - Toggles manage mode (shows/hides checkboxes, delete button, add panel)
 * - Select all / deselect all checkbox logic
 * - Confirmation dialog before batch deletion
 * - AJAX user search with debounce for adding registrants
 *
 * Group events:
 * - Toggles manage mode for group section
 * - Per-group checkboxes with batch remove
 * - Per-user "move to group" dropdown with auto-submit
 * - Delete group confirmation and submit
 * - AJAX user search for adding to a specific group
 * - Create new group option
 *
 * @author BdeLive - Group 8
 * @version 2.1.0
 */

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    var individualSection = document.getElementById('registrants-section');
    var groupSection = document.getElementById('group-registrants-section');

    if (individualSection) initIndividualMode(individualSection);
    if (groupSection) initGroupMode(groupSection);

    // ===================================================================
    // Individual event management
    // ===================================================================

    function initIndividualMode(section) {
        var toggleBtn = document.getElementById('btn-toggle-manage');
        var removeForm = document.getElementById('manage-registrants-form');
        var selectAllCheckbox = document.getElementById('select-all-registrants');
        var removeBtn = document.getElementById('btn-remove-registrants');
        var searchInput = document.getElementById('search-user-input');
        var dropdown = document.getElementById('search-results-dropdown');
        var chipsContainer = document.getElementById('selected-users-chips');
        var addForm = document.getElementById('add-registrants-form');
        var addBtn = document.getElementById('btn-add-registrants');
        var addIdsContainer = document.getElementById('add-user-ids-container');

        if (!toggleBtn) return;

        var checkboxes = removeForm ? removeForm.querySelectorAll('.registrant-checkbox') : [];
        var eventId = section.getAttribute('data-event-id');
        var selectedUsers = {};
        var searchTimer = null;
        var currentQuery = '';

        toggleBtn.addEventListener('click', function () {
            var isActive = section.classList.toggle('manage-mode');
            toggleBtn.classList.toggle('active', isActive);
            if (!isActive) {
                resetCheckboxes(selectAllCheckbox, checkboxes);
                updateButtonState(removeBtn, checkboxes);
                if (searchInput) resetSearchPanel(searchInput, dropdown, selectedUsers, chipsContainer, addBtn, addIdsContainer);
                currentQuery = '';
            }
        });

        setupCheckboxLogic(selectAllCheckbox, checkboxes, removeBtn);

        if (removeForm) {
            removeForm.addEventListener('submit', function (e) {
                var count = countChecked(checkboxes);
                if (count === 0) { e.preventDefault(); return; }
                if (!confirm('Êtes-vous sûr de vouloir supprimer ' + count + ' inscrit(s) ?')) {
                    e.preventDefault();
                }
            });
        }

        if (searchInput && dropdown && addForm) {
            setupSearchPanel(searchInput, dropdown, chipsContainer, addBtn, addIdsContainer, addForm, eventId, selectedUsers,
                function () { return currentQuery; }, function (q) { currentQuery = q; },
                function () { return searchTimer; }, function (t) { searchTimer = t; });
        }
    }

    // ===================================================================
    // Group event management
    // ===================================================================

    function initGroupMode(section) {
        var toggleBtn = document.getElementById('btn-toggle-group-manage');
        if (!toggleBtn) return;

        var eventId = section.getAttribute('data-event-id');
        var groupBlocks = section.querySelectorAll('.group-block');

        // Collect all elements that should toggle with manage mode
        var manageMoveEls = section.querySelectorAll('.manage-group-move-col');
        var manageForms = section.querySelectorAll('.group-manage-form');
        var deleteGroupForms = section.querySelectorAll('.group-delete-form');
        var addPanel = document.getElementById('group-add-panel');

        var groupSelectedUsers = {};
        var groupSearchTimer = null;
        var groupCurrentQuery = '';

        // Toggle manage mode
        toggleBtn.addEventListener('click', function () {
            var isActive = section.classList.toggle('manage-mode');
            toggleBtn.classList.toggle('active', isActive);

            // Toggle inline display on elements
            var i;
            for (i = 0; i < manageMoveEls.length; i++) {
                manageMoveEls[i].style.display = isActive ? 'table-cell' : 'none';
            }
            for (i = 0; i < manageForms.length; i++) {
                manageForms[i].style.display = isActive ? 'block' : 'none';
            }
            for (i = 0; i < deleteGroupForms.length; i++) {
                deleteGroupForms[i].style.display = isActive ? 'block' : 'none';
            }
            if (addPanel) {
                addPanel.style.display = isActive ? 'block' : 'none';
            }

            if (!isActive) {
                // Reset all group states
                groupBlocks.forEach(function (block) {
                    var selectAll = block.querySelector('.select-all-group');
                    var checkboxes = block.querySelectorAll('.group-registrant-checkbox');
                    var removeBtn = block.querySelector('.btn-group-remove');
                    resetCheckboxes(selectAll, checkboxes);
                    if (removeBtn) removeBtn.disabled = true;
                    var moveSelects = block.querySelectorAll('.group-move-select');
                    moveSelects.forEach(function (sel) { sel.value = ''; });
                });
                // Reset add panel
                var searchInput = document.getElementById('group-search-user-input');
                var dropdown = document.getElementById('group-search-results-dropdown');
                var chipsContainer = document.getElementById('group-selected-users-chips');
                var addBtn = document.getElementById('group-btn-add');
                var addIdsContainer = document.getElementById('group-add-user-ids-container');
                if (searchInput) resetSearchPanel(searchInput, dropdown, groupSelectedUsers, chipsContainer, addBtn, addIdsContainer);
                groupCurrentQuery = '';
            }
        });

        // Per-group checkbox and action logic
        groupBlocks.forEach(function (block) {
            var selectAll = block.querySelector('.select-all-group');
            var checkboxes = block.querySelectorAll('.group-registrant-checkbox');
            var teamId = block.getAttribute('data-team-id');
            var removeForm = document.getElementById('group-remove-form-' + teamId);
            var removeBtn = removeForm ? removeForm.querySelector('.btn-group-remove') : null;

            setupCheckboxLogic(selectAll, checkboxes, removeBtn);

            // Remove from group confirmation
            if (removeForm) {
                removeForm.addEventListener('submit', function (e) {
                    var count = countChecked(checkboxes);
                    if (count === 0) { e.preventDefault(); return; }
                    if (!confirm('Retirer ' + count + ' inscrit(s) du groupe ?')) {
                        e.preventDefault();
                    }
                });
            }

            // Delete group confirmation
            var deleteForm = block.querySelector('.group-delete-form');
            if (deleteForm) {
                deleteForm.addEventListener('submit', function (e) {
                    var teamNumber = block.getAttribute('data-team-number') || '?';
                    if (!confirm('Supprimer le groupe ' + teamNumber + ' et tous ses membres ?')) {
                        e.preventDefault();
                    }
                });
            }

            // Move user dropdown — auto-submit on change
            var moveSelects = block.querySelectorAll('.group-move-select[data-user-id]');
            moveSelects.forEach(function (sel) {
                sel.addEventListener('change', function () {
                    var newTeamId = sel.value;
                    var userId = sel.getAttribute('data-user-id');
                    if (!newTeamId || !userId) return;

                    if (!confirm('Déplacer cet inscrit vers un autre groupe ?')) {
                        sel.value = '';
                        return;
                    }

                    submitHiddenForm(eventId, 'group_move', block, {
                        'user_id': userId,
                        'new_team_id': newTeamId
                    });
                });
            });
        });

        // Group add panel
        var groupSearchInput = document.getElementById('group-search-user-input');
        var groupDropdown = document.getElementById('group-search-results-dropdown');
        var groupChipsContainer = document.getElementById('group-selected-users-chips');
        var groupAddForm = document.getElementById('group-add-form');
        var groupAddBtn = document.getElementById('group-btn-add');
        var groupAddIdsContainer = document.getElementById('group-add-user-ids-container');
        var groupTeamSelect = document.getElementById('group-team-select');

        if (groupSearchInput && groupDropdown && groupAddForm) {
            setupSearchPanel(groupSearchInput, groupDropdown, groupChipsContainer, groupAddBtn, groupAddIdsContainer, groupAddForm, eventId, groupSelectedUsers,
                function () { return groupCurrentQuery; }, function (q) { groupCurrentQuery = q; },
                function () { return groupSearchTimer; }, function (t) { groupSearchTimer = t; });
        }

        // Handle "create new group" option in team select
        if (groupAddForm && groupTeamSelect) {
            groupAddForm.addEventListener('submit', function (e) {
                if (groupTeamSelect.value === 'new') {
                    // Change action to group_create before submit
                    var actionInput = groupAddForm.querySelector('input[name="action"]');
                    if (actionInput) actionInput.value = 'group_create';
                }
            });
        }
    }

    // ===================================================================
    // Shared: submit a hidden form for group actions
    // ===================================================================

    function submitHiddenForm(eventId, action, block, extraFields) {
        var form = document.createElement('form');
        form.method = 'post';
        form.action = 'index.php?page=manageRegistrants';
        form.style.display = 'none';

        var fields = { 'event_id': eventId, 'action': action };
        for (var key in extraFields) {
            if (extraFields.hasOwnProperty(key)) fields[key] = extraFields[key];
        }

        for (var name in fields) {
            if (fields.hasOwnProperty(name)) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = fields[name];
                form.appendChild(input);
            }
        }

        // Copy CSRF token
        var csrfInput = block.querySelector('input[name="csrf_token"]');
        if (csrfInput) {
            var csrfField = document.createElement('input');
            csrfField.type = 'hidden';
            csrfField.name = 'csrf_token';
            csrfField.value = csrfInput.value;
            form.appendChild(csrfField);
        }

        document.body.appendChild(form);
        form.submit();
    }

    // ===================================================================
    // Shared utilities
    // ===================================================================

    function resetCheckboxes(selectAll, checkboxes) {
        if (selectAll) selectAll.checked = false;
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = false;
        }
    }

    function countChecked(checkboxes) {
        var count = 0;
        for (var i = 0; i < checkboxes.length; i++) {
            if (checkboxes[i].checked) count++;
        }
        return count;
    }

    function updateButtonState(btn, checkboxes) {
        if (!btn) return;
        btn.disabled = countChecked(checkboxes) === 0;
    }

    function setupCheckboxLogic(selectAll, checkboxes, btn) {
        if (selectAll) {
            selectAll.addEventListener('change', function () {
                for (var i = 0; i < checkboxes.length; i++) {
                    checkboxes[i].checked = selectAll.checked;
                }
                updateButtonState(btn, checkboxes);
            });
        }
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].addEventListener('change', function () {
                if (selectAll) {
                    var allChecked = true;
                    for (var j = 0; j < checkboxes.length; j++) {
                        if (!checkboxes[j].checked) { allChecked = false; break; }
                    }
                    selectAll.checked = allChecked;
                }
                updateButtonState(btn, checkboxes);
            });
        }
    }

    function resetSearchPanel(searchInput, dropdown, selectedUsers, chipsContainer, addBtn, addIdsContainer) {
        searchInput.value = '';
        dropdown.classList.remove('visible');
        dropdown.innerHTML = '';
        for (var key in selectedUsers) {
            if (selectedUsers.hasOwnProperty(key)) delete selectedUsers[key];
        }
        renderChips(chipsContainer, selectedUsers, dropdown, addBtn, addIdsContainer);
        updateAddState(addBtn, addIdsContainer, selectedUsers);
    }

    function setupSearchPanel(searchInput, dropdown, chipsContainer, addBtn, addIdsContainer, addForm, eventId, selectedUsers, getCurrentQuery, setCurrentQuery, getTimer, setTimer) {
        searchInput.addEventListener('input', function () {
            var query = searchInput.value.trim();
            var timer = getTimer();
            if (timer) clearTimeout(timer);

            if (query.length < 1) {
                dropdown.classList.remove('visible');
                dropdown.innerHTML = '';
                setCurrentQuery('');
                return;
            }

            setCurrentQuery(query);
            dropdown.innerHTML = '<div class="search-loading">Recherche...</div>';
            dropdown.classList.add('visible');

            setTimer(setTimeout(function () {
                performSearch(query, eventId, dropdown, selectedUsers, chipsContainer, addBtn, addIdsContainer, getCurrentQuery);
            }, 300));
        });

        addForm.addEventListener('submit', function (e) {
            var count = Object.keys(selectedUsers).length;
            if (count === 0) { e.preventDefault(); return; }
            if (!confirm('Ajouter ' + count + ' inscrit(s) ?')) {
                e.preventDefault();
            }
        });

        document.addEventListener('click', function (e) {
            if (!e.target.closest('.search-container')) {
                dropdown.classList.remove('visible');
            }
        });

        searchInput.addEventListener('focus', function () {
            if (searchInput.value.trim().length >= 1 && dropdown.children.length > 0) {
                dropdown.classList.add('visible');
            }
        });
    }

    function performSearch(query, eventId, dropdown, selectedUsers, chipsContainer, addBtn, addIdsContainer, getCurrentQuery) {
        var url = 'index.php?page=searchUsers&event_id=' + encodeURIComponent(eventId) + '&q=' + encodeURIComponent(query);

        fetch(url, {
            method: 'GET',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (query !== getCurrentQuery()) return;
                if (data.results && data.results.length > 0) {
                    renderResults(data.results, dropdown, selectedUsers, chipsContainer, addBtn, addIdsContainer);
                } else {
                    dropdown.innerHTML = '<div class="search-no-results">Aucun utilisateur trouvé</div>';
                }
                dropdown.classList.add('visible');
            })
            .catch(function () {
                if (query !== getCurrentQuery()) return;
                dropdown.innerHTML = '<div class="search-no-results">Erreur de recherche</div>';
                dropdown.classList.add('visible');
            });
    }

    function renderResults(results, dropdown, selectedUsers, chipsContainer, addBtn, addIdsContainer) {
        dropdown.innerHTML = '';
        for (var i = 0; i < results.length; i++) {
            var user = results[i];
            var isSelected = selectedUsers.hasOwnProperty(user.user_id);

            var item = document.createElement('div');
            item.className = 'search-result-item' + (isSelected ? ' selected' : '');

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

            (function (userData, itemEl, checkboxEl) {
                itemEl.addEventListener('click', function (e) {
                    if (e.target === checkboxEl) return;
                    checkboxEl.checked = !checkboxEl.checked;
                    toggleSelection(userData, checkboxEl.checked, itemEl, selectedUsers, chipsContainer, dropdown, addBtn, addIdsContainer);
                });
                checkboxEl.addEventListener('change', function () {
                    toggleSelection(userData, checkboxEl.checked, itemEl, selectedUsers, chipsContainer, dropdown, addBtn, addIdsContainer);
                });
            })(user, item, checkbox);

            dropdown.appendChild(item);
        }
    }

    function toggleSelection(user, isSelected, itemEl, selectedUsers, chipsContainer, dropdown, addBtn, addIdsContainer) {
        if (isSelected) {
            selectedUsers[user.user_id] = { first_name: user.first_name, last_name: user.last_name, user_status: user.user_status };
            itemEl.classList.add('selected');
        } else {
            delete selectedUsers[user.user_id];
            itemEl.classList.remove('selected');
        }
        renderChips(chipsContainer, selectedUsers, dropdown, addBtn, addIdsContainer);
        updateAddState(addBtn, addIdsContainer, selectedUsers);
    }

    function renderChips(chipsContainer, selectedUsers, dropdown, addBtn, addIdsContainer) {
        chipsContainer.innerHTML = '';
        var userIds = Object.keys(selectedUsers);
        for (var i = 0; i < userIds.length; i++) {
            var userId = userIds[i];
            var user = selectedUsers[userId];
            var chip = document.createElement('span');
            chip.className = 'user-chip';
            chip.appendChild(document.createTextNode(user.last_name + ' ' + user.first_name));

            var removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'user-chip-remove';
            removeBtn.textContent = '✕';
            removeBtn.title = 'Retirer';

            (function (uid) {
                removeBtn.addEventListener('click', function () {
                    delete selectedUsers[uid];
                    renderChips(chipsContainer, selectedUsers, dropdown, addBtn, addIdsContainer);
                    updateAddState(addBtn, addIdsContainer, selectedUsers);
                    var cb = dropdown.querySelector('input[data-user-id="' + uid + '"]');
                    if (cb) {
                        cb.checked = false;
                        cb.closest('.search-result-item').classList.remove('selected');
                    }
                });
            })(userId);

            chip.appendChild(removeBtn);
            chipsContainer.appendChild(chip);
        }
    }

    function updateAddState(addBtn, addIdsContainer, selectedUsers) {
        var userIds = Object.keys(selectedUsers);
        addBtn.disabled = userIds.length === 0;
        addIdsContainer.innerHTML = '';
        for (var i = 0; i < userIds.length; i++) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'user_ids[]';
            input.value = userIds[i];
            addIdsContainer.appendChild(input);
        }
    }
});
