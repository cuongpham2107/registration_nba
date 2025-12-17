<div class="min-h-screen bg-gradient-to-br from-blue-500 to-blue-200 py-1 px-1 sm:px-3 lg:px-4 flex items-center justify-center">
    <div class="max-w-3xl mx-auto">
        <div class="bg-[#fbfbfbf6] rounded-2xl shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class=" px-1 py-1 text-center">
                <div class="flex justify-center mb-3">
                    <img src="{{ asset('images/ASG.png') }}" alt="ASG Logo" class="h-8 w-24">
                </div>
                <h1 class="text-xl font-bold mb-2">Đăng ký xe khai thác</h1>
            </div>

            <!-- Form Content -->
            <div class="px-3 py-2">
                <form wire:submit="create">
                    {{ $this->form }}
                    <div class="flex gap-4" style="margin-top: 16px;">
                        <button 
                            type="submit"
                            class="flex-1 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold py-3 px-6 rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200"
                            style="background: linear-gradient(45deg, #10b981, #059669); color: white; padding: 12px 24px; border-radius: 8px; border: none; font-weight: 600; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); transition: all 0.2s; cursor: pointer;"
                            onmouseover="this.style.background='linear-gradient(45deg, #059669, #047857)'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 12px rgba(0, 0, 0, 0.15)'"
                            onmouseout="this.style.background='linear-gradient(45deg, #10b981, #059669)'; this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px rgba(0, 0, 0, 0.1)'"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove>Tạo và gửi</span>
                            <span wire:loading>
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Đang xử lý...
                            </span>
                        </button>
                        
                        <a 
                            href="javascript:history.back()"
                            class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 px-6 rounded-lg shadow hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 text-center border-[1.5px] border-gray-400"
                            style="background: #f3f4f6; color: #374151; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.2s; display: flex; align-items: center; justify-content: center;"
                            onmouseover="this.style.background='#e5e7eb'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0, 0, 0, 0.15)'"
                            onmouseout="this.style.background='#f3f4f6'; this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(0, 0, 0, 0.1)'"
                        >
                            Hủy bỏ
                        </a>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                <p class="text-center text-sm text-gray-600">
                    © {{ date('Y') }} ASGL - Hệ thống đăng ký xe khai thác
                </p>
            </div>
        </div>
    </div>

    <x-filament-actions::modals />

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Load stored data on page load
            loadStoredData();

            // Listen for Livewire initialization
            document.addEventListener('livewire:initialized', () => {
                // Listen for successful form submission
                Livewire.on('registration-success', (event) => {
                    const data = event[0] || event;
                    saveToStorage(data);
                });

                // Listen for load-stored-data event from Livewire
                Livewire.on('load-stored-data', () => {
                    const saved = localStorage.getItem('livewireVehicleForm');
                    if (saved) {
                        try {
                            const data = JSON.parse(saved);
                            if (data.savedAt) {
                                // Send stored data to Livewire component
                                const component = window.Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'));
                                if (component) {
                                    component.call('loadStoredDataFromJs', data);
                                }
                            }
                        } catch (e) {
                            console.error('Error loading stored data:', e);
                        }
                    }
                });
            });
        });

        function saveToStorage(data) {
            const storageData = {
                driver_name: data.driver_name || '',
                driver_phone: data.driver_phone || '',
                driver_id_card: data.driver_id_card || '',
                vehicle_number: data.vehicle_number || '',
                name: data.name || '',
                notes: data.notes || '',
                savedAt: new Date().toISOString()
            };
            
            localStorage.setItem('livewireVehicleForm', JSON.stringify(storageData));
        }

        function loadStoredData() {
            const saved = localStorage.getItem('livewireVehicleForm');
            
            if (saved) {
                try {
                    const data = JSON.parse(saved);
                    
                    // Clean up old format - remove hawb_number if exists
                    if (data.hawb_number !== undefined) {
                        delete data.hawb_number;
                        localStorage.setItem('livewireVehicleForm', JSON.stringify(data));
                    }
                } catch (e) {
                    console.error('Error loading stored data:', e);
                }
            }
        }
    </script>
</div>
