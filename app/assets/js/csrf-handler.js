/**
 * Global CSRF Token Handler
 *
 * Automatically injects the CSRF token into all fetch() requests.
 * The token is retrieved from the <meta name="csrf-token"> tag.
 *
 * Features:
 * - Auto-injection of X-CSRF-Token header in all fetch() requests
 * - Works with both GET and POST requests
 * - Compatible with existing code (no modifications needed)
 * - Graceful fallback if token is missing
 *
 * Usage:
 * Include this script after the CSRF meta tag in your HTML:
 * <meta name="csrf-token" content="<?= $csrf->getToken() ?>">
 * <script src="assets/js/csrf-handler.js"></script>
 *
 * @author BdeLive - Security Team
 * @version 1.0.0
 */

(function () {
    'use strict';

    // Retrieve CSRF token from meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    if (!csrfToken) {
        console.warn('CSRF Handler: Token not found in meta tag. CSRF protection may not work correctly.');
        return;
    }

    // Store original fetch for wrapping
    const originalFetch = window.fetch;

    /**
     * Wrapped fetch function with automatic CSRF injection
     *
     * Automatically adds X-CSRF-Token header to all requests.
     * Preserves all original fetch options and behavior.
     *
     * @param {string|Request} url - Request URL or Request object
     * @param {Object} options - Fetch options
     * @return {Promise<Response>} Fetch promise
     */
    window.fetch = function (url, options = {}) {
        // Initialize headers if not present
        if (!options.headers) {
            options.headers = {};
        }

        // Convert Headers object to plain object if needed
        if (options.headers instanceof Headers) {
            const plainHeaders = {};
            options.headers.forEach((value, key) => {
                plainHeaders[key] = value;
            });
            options.headers = plainHeaders;
        }

        // Add CSRF token header (will not override if already present)
        if (!options.headers['X-CSRF-Token'] && !options.headers['x-csrf-token']) {
            options.headers['X-CSRF-Token'] = csrfToken;
        }

        // Call original fetch with modified options
        return originalFetch(url, options);
    };

    // Expose token for manual usage if needed
    window.getCsrfToken = function () {
        return csrfToken;
    };

    console.log('CSRF Handler: Initialized successfully');
})();
