// cookie_popup.js
(function(){
    const cookieName = 'cookie_consent';
    const consentEl = document.getElementById('cookieConsent');

    function setCookie(name, value, days=365){
        const expires = new Date(Date.now() + days*24*60*60*1000).toUTCString();
        document.cookie = name + '=' + encodeURIComponent(value) + '; path=/; expires=' + expires + '; SameSite=Lax';
    }

    function getCookie(name){
        return document.cookie.split('; ').reduce((r,v)=>{
            const parts = v.split('=');
            return parts[0]===name ? decodeURIComponent(parts.slice(1).join('=')) : r;
        }, null);
    }

    // Cacher le popup si le cookie est déjà accepté
    const consent = getCookie(cookieName);
    if(consent === 'yes'){
        consentEl.style.display = 'none';
    }

    document.getElementById('acceptBtn').addEventListener('click', ()=>{
        setCookie(cookieName,'yes');
        consentEl.style.display='none';
    });

    document.getElementById('declineBtn').addEventListener('click', ()=>{
        setCookie(cookieName,'no');
        consentEl.style.display='none';
    });

    document.getElementById('manageBtn').addEventListener('click', ()=>{
        alert('Ici, vous pourriez ouvrir une modal de gestion des cookies.');
    });
})();
