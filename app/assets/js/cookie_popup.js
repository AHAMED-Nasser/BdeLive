// cookie_popup.js
(function () {
    const cookieName = 'cookie_consent';
    const consentEl = document.getElementById('cookieConsent');

    function setCookie(name, value, days=365)
    {
        const expires = new Date(Date.now() + days * 24 * 60 * 60 * 1000).toUTCString();
        document.cookie = name + '=' + encodeURIComponent(value) + '; path=/; expires=' + expires + '; SameSite=Lax';
    }

    function getCookie(name)
    {
        return document.cookie.split('; ').reduce((r,v) => {
            const parts = v.split('=');
            return parts[0] === name ? decodeURIComponent(parts.slice(1).join('=')) : r;
        }, null);
    }

    // Cacher le popup si le consentement est déjà donné
    if (getCookie(cookieName) === 'yes') {
        if (consentEl) {
            consentEl.style.display = 'none';
        }
    }

    const acceptBtn = document.getElementById('acceptBtn');
    if (acceptBtn) {
        acceptBtn.addEventListener('click', () => {
            setCookie(cookieName,'yes');
            if (consentEl) {
                consentEl.style.display = 'none';
            }
        });
    }

    const declineBtn = document.getElementById('declineBtn');
    if (declineBtn) {
        declineBtn.addEventListener('click', () => {
            setCookie(cookieName,'no');
            if (consentEl) {
                consentEl.style.display = 'none';
            }
        });
    }
})();
