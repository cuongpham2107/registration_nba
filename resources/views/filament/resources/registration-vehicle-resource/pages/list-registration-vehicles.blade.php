<x-filament-panels::page>
    {{ $this->table }}
    
    @script
    <script>
        (function() {
            const userId = @js(auth()->id());
            
            console.log('🔄 Vehicle table auto-reload initialized for user:', userId);
            
            const initEcho = () => {
                if (typeof window.Echo !== 'undefined' && window.Echo && typeof window.Livewire !== 'undefined') {
                    console.log('👂 Listening for vehicle notifications: App.Models.User.' + userId);
                    
                    window.Echo.private('App.Models.User.' + userId)
                        .notification((notification) => {
                            console.log('🔔 Vehicle notification received:', notification);
                            
                            if (notification.title && notification.title.includes('Đăng ký xe khai thác mới')) {
                                console.log('✅ Refreshing vehicle table...');
                                // Dispatch custom event to refresh the vehicle table
                                window.Livewire.dispatch('refresh-vehicle-table');
                            }
                        });
                    
                    console.log('✅ Vehicle listener ready');
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
