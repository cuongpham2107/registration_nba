<x-filament-panels::page>
    {{ $this->table }}
    
    @script
    <script>
        (function() {
            const userId = @js(auth()->id());
            
            // console.log('🔄 Auto-reload initialized for user:', userId);
            
            const initEcho = () => {
                if (typeof window.Echo !== 'undefined' && window.Echo && typeof window.Livewire !== 'undefined') {
                    // console.log('👂 Listening: App.Models.User.' + userId);
                    
                    window.Echo.private('App.Models.User.' + userId)
                        .notification((notification) => {

                            if (notification.title && (notification.title.includes('Đăng ký xe khai thác mới') || notification.title.includes('Đơn xét duyệt đăng ký khách mới') || notification.title.includes('Cập nhập thứ tự ra vào cho xe khai thác'))) {
                                // Dispatch custom event to refresh the table
                                window.Livewire.dispatch('refresh-table');
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
