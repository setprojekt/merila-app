{{-- Avtomatska preusmeritev na login pri 419 napaki --}}
<script>
    // Presliši Livewire napake in preusmeri pri 419
    document.addEventListener('livewire:init', () => {
        Livewire.hook('request', ({ fail }) => {
            fail(({ status, preventDefault }) => {
                if (status === 419) {
                    preventDefault();
                    
                    // Določi pravi panel za redirect
                    const path = window.location.pathname;
                    let loginUrl = '/admin';
                    
                    if (path.startsWith('/super-admin')) {
                        loginUrl = '/super-admin';
                    } else if (path.startsWith('/merila')) {
                        loginUrl = '/merila';
                    }
                    
                    // Preusmeri brez dialoga
                    window.location.href = loginUrl;
                }
            });
        });
    });
    
    // Backup: če Livewire ni naložen, presliši window errors
    window.addEventListener('unhandledrejection', function(event) {
        if (event.reason && event.reason.status === 419) {
            event.preventDefault();
            const path = window.location.pathname;
            let loginUrl = '/admin';
            
            if (path.startsWith('/super-admin')) {
                loginUrl = '/super-admin';
            } else if (path.startsWith('/merila')) {
                loginUrl = '/merila';
            }
            
            window.location.href = loginUrl;
        }
    });
</script>
