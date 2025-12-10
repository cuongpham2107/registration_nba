<div class="min-h-screen bg-gradient-to-br from-blue-500 to-blue-700 py-3 px-2 sm:px-3 lg:px-4">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-1 py-2 text-center">
                <div class="flex justify-center mb-4">
                    <img src="{{ asset('images/ASG.png') }}" alt="ASG Logo" class="h-8 w-24">
                </div>
                <h1 class="text-xl font-bold mb-2">Đăng ký xe khai thác</h1>
            </div>

            <!-- Form Content -->
            <div class="px-1 py-2">
                <!-- Thông báo dữ liệu đã lưu -->
                <div id="stored-data-alert" style="display: none;" class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg flex items-center gap-2">
                    <span style="font-size: 18px;">ℹ️</span>
                    <div>
                        <span id="stored-data-message" style="color: #1e40af; font-size: 14px; font-weight: 500;"></span>
                    </div>
                </div>

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
                            class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 px-6 rounded-lg shadow hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 text-center"
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

            // Listen for successful form submission
            document.addEventListener('livewire:initialized', () => {
                Livewire.on('registration-success', (event) => {
                    const data = event[0] || event;
                    saveToStorage(data);
                    showStoredDataAlert(data.savedAt);
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
                hawb_number: data.hawb_number || '',
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
                    if (data.savedAt) {
                        showStoredDataAlert(data.savedAt);
                    }
                } catch (e) {
                    console.error('Error loading stored data:', e);
                }
            }
        }

        function showStoredDataAlert(savedAt) {
            const alertElement = document.getElementById('stored-data-alert');
            const messageElement = document.getElementById('stored-data-message');
            
            if (alertElement && messageElement && savedAt) {
                const formattedTime = formatDateTime(savedAt);
                messageElement.textContent = `Lần gửi thành công: ${formattedTime}`;
                alertElement.style.display = 'flex';
            }
        }

        function formatDateTime(isoString) {
            const date = new Date(isoString);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');

            return `${day}/${month}/${year} ${hours}:${minutes}`;
        }

        function clearStorage() {
            localStorage.removeItem('livewireVehicleForm');
            const alertElement = document.getElementById('stored-data-alert');
            if (alertElement) {
                alertElement.style.display = 'none';
            }
        }
    </script>
</div>
