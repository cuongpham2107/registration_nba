<x-filament-panels::page>
    {{ $this->table }}
    
    @script
    <script>
        (function() {
            const userId = @js(auth()->id());
            
            
            const initEcho = () => {
                if (typeof window.Echo !== 'undefined' && window.Echo && typeof window.Livewire !== 'undefined') {
                    console.log('👂 Listening for vehicle notifications: App.Models.User.' + userId);
                    
                    window.Echo.private('App.Models.User.' + userId)
                        .notification((notification) => {
                            
                            if (notification.title && notification.title.includes('Đăng ký xe khai thác mới')) {
                                // Dispatch custom event to refresh the vehicle table
                                window.Livewire.dispatch('refresh-vehicle-table');
                            }
                        });
                    
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
