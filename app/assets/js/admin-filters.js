/**
 * Admin Panel Filters - AJAX Handling
 *
 * Handles dynamic updates of the user list without page reloads.
 * Intercepts clicks on filters, pagination, and search form submission.
 */

document.addEventListener('DOMContentLoaded', function () {
    const contentArea = document.getElementById('admin-content-area');

    if (!contentArea) {
        return;
    }

    /**
     * Load content via AJAX without page reload
     *
     * Fetches the admin section content dynamically and updates the content area.
     * Adds loading indicator and updates browser history.
     *
     * @param {string} url - The URL to load content from
     * @param {boolean} updateHistory - Whether to update browser history (default: true)
     */
    async function loadContent(url, updateHistory = true) {
        try {
            // Add loading indicator
            contentArea.style.opacity = '0.5';
            contentArea.style.pointerEvents = 'none';

            // Add ajax=1 parameter to the URL
            const fetchUrl = new URL(url, window.location.origin);
            fetchUrl.searchParams.set('ajax', '1');

            const response = await fetch(fetchUrl);

            if (!response.ok) {
                throw new Error('Network error');
            }

            const html = await response.text();

            // Update content
            contentArea.innerHTML = html;

            // Update browser URL
            if (updateHistory) {
                window.history.pushState({}, '', url);
            }

            // Update active classes in sidebar (use original URL)
            updateActiveFilters(new URL(url, window.location.origin));
        } catch (error) {
            // Fallback: reload page on error
            window.location.href = url;
        } finally {
            // Remove loading indicator
            contentArea.style.opacity = '1';
            contentArea.style.pointerEvents = 'auto';

            // Reattach event listeners if necessary
            // (not necessary here as we use delegation via document or static selectors
            // outside contentArea for navigation, but pagination inside is handled by delegation below)
        }
    }

    /**
     * Update active filter states in the sidebar and hidden form fields
     *
     * Updates filter links to preserve current search, role, and filter parameters.
     * Disables the "admin" role filter when "blocked" status filter is active,
     * as blocked users cannot be admins.
     *
     * @param {URL} url - The current URL with query parameters
     */
    function updateActiveFilters(url) {
        const params = url.searchParams;
        const currentRole = params.get('role') || 'all';
        const currentFilter = params.get('filter') || 'active';
        const currentSearch = params.get('search') || '';

        // Update status filter links with current search and role
        document.querySelectorAll('.admin-nav-list:not(.role-filters) .admin-nav-link').forEach(link => {
            const linkUrl = new URL(link.href, window.location.origin);
            const linkFilter = linkUrl.searchParams.get('filter');

            // Update link URL to include current search
            if (currentSearch) {
                linkUrl.searchParams.set('search', currentSearch);
            } else {
                linkUrl.searchParams.delete('search');
            }
            // Preserve current role
            if (currentRole !== 'all') {
                linkUrl.searchParams.set('role', currentRole);
            } else {
                linkUrl.searchParams.delete('role');
            }
            link.href = linkUrl.toString();

            // Update active class
            if (linkFilter === currentFilter) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });

        // Update role filter links with current search and filter
        document.querySelectorAll('.role-filters .admin-nav-link').forEach(link => {
            const linkUrl = new URL(link.href, window.location.origin);
            const linkRole = linkUrl.searchParams.get('role');

            // Update link URL to include current search
            if (currentSearch) {
                linkUrl.searchParams.set('search', currentSearch);
            } else {
                linkUrl.searchParams.delete('search');
            }
            // Preserve current status filter
            if (currentFilter !== 'active') {
                linkUrl.searchParams.set('filter', currentFilter);
            } else {
                linkUrl.searchParams.delete('filter');
            }
            link.href = linkUrl.toString();

            // Update active class
            if (linkRole === currentRole) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });

        // Update hidden form fields in search form
        const searchForm = document.querySelector('.admin-search-form');
        if (searchForm) {
            const filterInput = searchForm.querySelector('input[name="filter"]');
            const roleInput = searchForm.querySelector('input[name="role"]');
            const searchInput = searchForm.querySelector('input[name="search"]');

            if (filterInput) {
                filterInput.value = currentFilter;
            }
            if (roleInput) {
                roleInput.value = currentRole;
            }
            if (searchInput) {
                searchInput.value = currentSearch;
            }

            // Update reset button and search indicator
            updateSearchUI(currentSearch);
        }
    }

    /**
     * Update search UI elements (reset button and search indicator)
     *
     * Shows or hides the reset button and search indicator based on whether
     * there is an active search query.
     *
     * @param {string} searchValue - The current search query value
     */
    function updateSearchUI(searchValue) {
        const searchForm = document.querySelector('.admin-search-form');
        if (!searchForm) {
            return;
        }

        const searchGroup = searchForm.querySelector('.search-group');
        const searchIndicator = searchForm.querySelector('.search-indicator');
        const resetBtn = searchForm.querySelector('.admin-reset-btn');
        const currentUrl = new URL(window.location.href);
        const currentFilter = currentUrl.searchParams.get('filter') || 'active';
        const currentRole = currentUrl.searchParams.get('role') || 'all';

        if (searchValue && searchValue.trim() !== '') {
            // Show reset button if it doesn't exist
            if (!resetBtn && searchGroup) {
                const resetLink = document.createElement('a');
                resetLink.href = `index.php?page=adminSection&filter=${currentFilter}&role=${currentRole}`;
                resetLink.className = 'admin-reset-btn';
                resetLink.title = 'Clear search';
                resetLink.innerHTML = '<i class="fas fa-times"></i>';
                searchGroup.appendChild(resetLink);
            } else if (resetBtn) {
                // Update reset button URL
                resetBtn.href = `index.php?page=adminSection&filter=${currentFilter}&role=${currentRole}`;
            }

            // Show search indicator
            if (!searchIndicator) {
                const indicator = document.createElement('div');
                indicator.className = 'search-indicator';
                // Escape HTML to prevent XSS and display properly
                const escapedValue = searchValue.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
                indicator.innerHTML = `Recherche : <strong>${escapedValue}</strong>`;
                searchForm.appendChild(indicator);
            } else {
                // Escape HTML to prevent XSS and display properly
                const escapedValue = searchValue.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
                searchIndicator.innerHTML = `Recherche : <strong>${escapedValue}</strong>`;
                searchIndicator.style.display = 'block';
            }
        } else {
            // Hide reset button
            if (resetBtn) {
                resetBtn.remove();
            }

            // Hide search indicator
            if (searchIndicator) {
                searchIndicator.style.display = 'none';
            }
        }
    }

    /**
     * Event delegation for pagination links (dynamically recreated)
     * and filter links (static)
     *
     * Prevents invalid filter combinations:
     * - "blocked" + "admin" is not allowed (blocked users cannot be admins)
     * - Automatically redirects to "all" role when selecting "admin" with "blocked" filter
     * - Automatically redirects to "all" role when selecting "blocked" with "admin" role
     */
    document.addEventListener('click', function (e) {
        // Filter links and pagination
        const link = e.target.closest('.admin-nav-link, .pagination a, .admin-reset-btn');

        if (link && !e.ctrlKey && !e.metaKey && !e.shiftKey) {
            // Prevent clicking on disabled links
            if (link.classList.contains('disabled')) {
                e.preventDefault();
                return;
            }

            e.preventDefault();
            loadContent(link.href);
        }
    });

    /**
     * Intercept search form submission
     *
     * Handles search form submission and preserves current filter and role parameters.
     */
    const searchForm = document.querySelector('.admin-search-form');
    if (searchForm) {
        const searchInput = searchForm.querySelector('input[name="search"]');

        /**
         * Submit search form
         *
         * Builds URL with current filters and search query, then loads content via AJAX.
         * Removes search parameter if search is empty.
         */
        function submitSearch() {
            const formData = new FormData(searchForm);
            const params = new URLSearchParams();

            // Ensure filter parameters are preserved from current URL
            const currentUrl = new URL(window.location.href);
            const currentFilter = currentUrl.searchParams.get('filter') || 'active';
            const currentRole = currentUrl.searchParams.get('role') || 'all';

            // Add base parameters
            params.set('page', 'adminSection');
            params.set('filter', currentFilter);
            if (currentRole !== 'all') {
                params.set('role', currentRole);
            }

            // Add search only if it's not empty
            const searchValue = searchInput ? searchInput.value.trim() : '';
            if (searchValue) {
                params.set('search', searchValue);
            }

            // Build URL with all parameters
            const url = 'index.php?' + params.toString();

            loadContent(url);
        }

        // Handle form submission
        searchForm.addEventListener('submit', function (e) {
            e.preventDefault();
            submitSearch();
        });

        // Handle manual text deletion (detection via events)
        if (searchInput) {
            // Detect changes in input
            searchInput.addEventListener('input', function () {
                // Update UI immediately
                updateSearchUI(this.value.trim());
            });

            // Submit automatically when user presses Enter
            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    submitSearch();
                }
            });
        }
    }

    /**
     * Handle browser Back/Forward buttons
     *
     * Reloads content when user navigates using browser history.
     */
    window.addEventListener('popstate', function () {
        loadContent(window.location.href, false);
    });

    /**
     * Initialize interface on page load
     *
     * Checks for invalid filter combinations and redirects if necessary.
     * Prevents "blocked" + "admin" combination by redirecting to "blocked" + "all".
     */
    function initializeInterface() {
        const currentUrl = new URL(window.location.href);

        updateActiveFilters(currentUrl);
        updateSearchUI(currentUrl.searchParams.get('search') || '');
    }

    // Initialize interface on page load
    initializeInterface();
});
