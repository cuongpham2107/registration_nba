<x-filament-panels::page>
    {{ $this->table }}
    
    @script
    <script>
        (function() {
            const userId = @js(auth()->id());
            
            console.log('🔄 Registration table auto-reload initialized for user:', userId);
            
            const initEcho = () => {
                if (typeof window.Echo !== 'undefined' && window.Echo && typeof window.Livewire !== 'undefined') {
                    console.log('👂 Listening for registration notifications: App.Models.User.' + userId);
                    
                    window.Echo.private('App.Models.User.' + userId)
                        .notification((notification) => {
                            console.log('🔔 Registration notification received:', notification);
                            
                            if (notification.title && notification.title.includes('Yêu cầu đăng ký mới')) {
                                window.Livewire.dispatch('refresh-registration-table');
                            }
                        });
                    
                    console.log('✅ Registration listener ready');
                } else {
                    setTimeout(initEcho, 500);
                }
            };
            
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initEcho);
            } else {
                initEcho();
            }
        })();
    </script>
    @endscript
</x-filament-panels::page>
