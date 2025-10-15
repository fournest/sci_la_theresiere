document.addEventListener('DOMContentLoaded', () => {
    // Le code s'exécute uniquement après que le HTML de la page est entièrement chargé.

    const banner = document.getElementById('cookie-banner');
    const btnAccept = document.getElementById('btn-accept');
    const btnRefuse = document.getElementById('btn-refuse');
    const COOKIE_KEY = 'cookieConsent';

    // Fonction pour charger les scripts optionnels (Analytics, etc.)
    function loadOptionalScripts() {
        console.log("Consentement donné : Les scripts optionnels (comme Google Analytics) peuvent être chargés ici.");
        // TODO: Placez ici l'appel ou l'injection de vos scripts de tracking.
        // Exemple: injecter le script Google Analytics
        /*
        const script = document.createElement('script');
        script.src = 'https://www.googletagmanager.com/gtag/js?id=VOTRE_ID_GA';
        document.head.appendChild(script);
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'VOTRE_ID_GA');
        */
    }

    // Fonction pour enregistrer le choix
    function setConsent(accepted) {
        const status = accepted ? 'yes' : 'no';
        try {
            localStorage.setItem(COOKIE_KEY, status);
            // Masquer immédiatement le bandeau
            banner.style.display = 'none'; 

            if (accepted) {
                loadOptionalScripts();
            }
        } catch (e) {
            console.error("Impossible d'accéder à localStorage. Le bandeau reste visible. :", e);
        }
    }

    // Vérification initiale
    function checkConsent() {
        const consent = localStorage.getItem(COOKIE_KEY);
        if (consent === 'yes') {
            loadOptionalScripts();
            banner.style.display = 'none';
        } else if (consent === 'no') {
            banner.style.display = 'none';
        } else {
            // Pas de choix, afficher le bandeau
            banner.style.display = 'block';
        }
    }

    // Écouteurs d'événements
    // Vérification que les boutons existent avant d'ajouter les écouteurs
    if (btnAccept && btnRefuse) {
        btnAccept.addEventListener('click', () => setConsent(true));
        btnRefuse.addEventListener('click', () => setConsent(false));
    } else {
         console.error("Les boutons d'acceptation/refus des cookies sont manquants dans le HTML.");
    }

    // Lancement de la vérification
    checkConsent();
});