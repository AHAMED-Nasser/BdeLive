/**
 * Auto-dismiss alerts after a specified duration
 *
 * This script automatically hides alert messages (success, error, info, warning)
 * after a configurable duration to improve UX.
 *
 * @author BdeLive Team
 * @version 1.0.0
 */

document.addEventListener('DOMContentLoaded', function () {
    // Configuration
    const ALERT_DURATION = 1500; // 1.5 seconds
    const FADE_OUT_DURATION = 500; // 0.5 seconds for fade out animation

    // Select all alert elements
    const alerts = document.querySelectorAll('.alert');

    if (alerts.length === 0) {
        return; // No alerts to process
    }

    alerts.forEach(function (alert) {
        // Add fade-out animation styles
        // phpcs:ignore
        alert.style.transition = `opacity ${FADE_OUT_DURATION}ms ease-out, transform ${FADE_OUT_DURATION}ms ease-out`;

        // Set timeout to start fade out
        setTimeout(function () {
            // Start fade out animation
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-20px)';

            // Remove element from DOM after animation completes
            setTimeout(function () {
                alert.remove();
            }, FADE_OUT_DURATION);
        }, ALERT_DURATION);
    });

    // Optional: Allow manual dismissal by clicking on the alert
    alerts.forEach(function (alert) {
        alert.style.cursor = 'pointer';
        alert.title = 'Cliquer pour fermer';

        alert.addEventListener('click', function () {
            this.style.opacity = '0';
            this.style.transform = 'translateY(-20px)';

            setTimeout(() => {
                this.remove();
            }, FADE_OUT_DURATION);
        });
    });
});
