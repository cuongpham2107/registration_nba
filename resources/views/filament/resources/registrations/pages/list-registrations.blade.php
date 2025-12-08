<x-filament-panels::page>
    {{ $this->table }}
    
    @script
    <script>
        (function() {
            const userId = @js(auth()->id());
            
            
            const initEcho = () => {
                if (typeof window.Echo !== 'undefined' && window.Echo && typeof window.Livewire !== 'undefined') {
                    
                    window.Echo.private('App.Models.User.' + userId)
                        .notification((notification) => {
                            
                            if (notification.title && notification.title.includes('Yêu cầu đăng ký mới')) {
                                window.Livewire.dispatch('refresh-registration-table');
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
