/**
 * Privacy Page - Countdown Timer for Resend Buttons
 *
 * Manages countdown timers for email and password verification code resend buttons.
 * Prevents users from spamming resend requests by enforcing a cooldown period.
 *
 * @author BdeLive Team
 * @version 1.0.0
 */
(function () {
    'use strict';

    /**
     * Initialize countdown timer for resend button
     * @param {string} type - 'email' or 'password'
     * @param {number} initialSeconds - Initial countdown value in seconds
     */
    function initCountdown(type, initialSeconds)
    {
        var countdownEl = document.getElementById(type + '-countdown');
        var btnEl = document.getElementById(type + '-resend-btn');
        var textEl = document.getElementById(type + '-resend-text');

        if (!countdownEl || !btnEl || !textEl || initialSeconds <= 0) {
            return;
        }

        var seconds = initialSeconds;

        var interval = setInterval(function () {
            seconds--;

            if (seconds <= 0) {
                clearInterval(interval);
                btnEl.disabled = false;
                textEl.innerHTML = 'Renvoyer le code';
                btnEl.classList.add('privacy-resend-btn-ready');
            } else {
                countdownEl.textContent = seconds;
            }
        }, 1000);
    }

    // Initialize countdowns on page load
    document.addEventListener('DOMContentLoaded', function () {
        // Email countdown
        var emailCountdown = document.getElementById('email-countdown');
        if (emailCountdown) {
            var emailSeconds = parseInt(emailCountdown.textContent, 10);
            if (!isNaN(emailSeconds) && emailSeconds > 0) {
                initCountdown('email', emailSeconds);
            }
        }

        // Password countdown
        var passwordCountdown = document.getElementById('password-countdown');
        if (passwordCountdown) {
            var passwordSeconds = parseInt(passwordCountdown.textContent, 10);
            if (!isNaN(passwordSeconds) && passwordSeconds > 0) {
                initCountdown('password', passwordSeconds);
            }
        }
    });
})();
