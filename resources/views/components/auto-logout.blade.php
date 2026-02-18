@if($timeout > 0)
<script>
    (function() {
        let timeout = {{ $timeout * 1000 }}; // Pretvori v milisekunde
        let logoutTimer;
        let warningTimer;
        
        // Dinamično ugotovi logout URL glede na trenutni panel
        function getLogoutUrl() {
            const path = window.location.pathname;
            
            if (path.startsWith('/super-admin')) {
                return '/super-admin/logout';
            } else if (path.startsWith('/merila')) {
                return '/merila/logout';
            } else {
                return '/admin/logout'; // Default panel
            }
        }
        
        // Funkcija za reset timerja
        function resetTimer() {
            // Počisti obstoječe timerje
            clearTimeout(logoutTimer);
            clearTimeout(warningTimer);
            
            // Nastavi opozorilo 30 sekund pred odjavo
            if (timeout > 30000) {
                warningTimer = setTimeout(function() {
                    console.log('Opozorilo: Odjava čez 30 sekund zaradi nedejavnosti');
                    
                    // Lahko dodaš notification
                    if (window.Notification && Notification.permission === "granted") {
                        new Notification('Opozorilo', {
                            body: 'Zaradi nedejavnosti boste odjavljeni čez 30 sekund.',
                            icon: '/favicon.ico'
                        });
                    }
                }, timeout - 30000);
            }
            
            // Nastavi timer za odjavo
            logoutTimer = setTimeout(function() {
                console.log('Avtomatska odjava zaradi nedejavnosti');
                
                // Odjavi uporabnika na pravilnem panel URL-ju
                window.location.href = getLogoutUrl();
            }, timeout);
        }
        
        // Poslušaj dogodke aktivnosti
        const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
        
        events.forEach(function(event) {
            document.addEventListener(event, resetTimer, true);
        });
        
        // Zahtevaj dovoljenje za notifications (opcijsko)
        if (window.Notification && Notification.permission === "default") {
            Notification.requestPermission();
        }
        
        // Začni timer ob nalaganju strani
        resetTimer();
    })();
</script>
@endif
