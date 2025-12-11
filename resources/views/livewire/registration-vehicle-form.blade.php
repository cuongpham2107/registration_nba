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
                <div id="stored-data-alert" style="display: none;" class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span style="font-size: 18px;">ℹ️</span>
                            <div>
                                <span id="stored-data-message" style="color: #1e40af; font-size: 14px; font-weight: 500;">Đã có dữ liệu được lưu trước đó</span>
                            </div>
                        </div>
                        <button 
                            onclick="clearStorage()" 
                            class="text-red-600 hover:text-red-800 text-sm font-medium px-2 py-1 rounded hover:bg-red-50 transition-colors"
                            title="Xóa dữ liệu đã lưu"
                        >
                            ✕ Xóa
                        </button>
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
        let alertShown = false; // Flag to prevent showing alert multiple times
        let alertObserver = null; // Observer for alert element
        
        document.addEventListener('DOMContentLoaded', function() {
            // Setup observer for alert element to prevent unexpected hiding
            setupAlertObserver();
            
            console.log('DOM loaded, checking for stored data...');
            
            // Load stored data on page load
            loadStoredData();

            // Listen for successful form submission
            document.addEventListener('livewire:initialized', () => {
                console.log('Livewire initialized');
                
                // Load stored data again after Livewire is initialized (only if not shown yet)
                setTimeout(() => {
                    if (!alertShown) {
                        console.log('Livewire ready, attempting to load stored data again...');
                        loadStoredData();
                    }
                }, 100);

                Livewire.on('registration-success', (event) => {
                    const data = event[0] || event;
                    saveToStorage(data);
                    alertShown = false; // Reset flag for new data
                    showStoredDataAlert(data.savedAt);
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
                                if (!alertShown) {
                                    showStoredDataAlert(data.savedAt);
                                }
                            }
                        } catch (e) {
                            console.error('Error loading stored data:', e);
                        }
                    }
                });
            });
        });

        function setupAlertObserver() {
            const alertElement = document.getElementById('stored-data-alert');
            if (alertElement && window.MutationObserver) {
                alertObserver = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                            const target = mutation.target;
                            if (alertShown && target.style.display === 'none') {
                                console.log('Alert was hidden unexpectedly, restoring...');
                                setTimeout(() => {
                                    target.style.display = 'block';
                                    target.style.visibility = 'visible';
                                    target.style.opacity = '1';
                                }, 10);
                            }
                        }
                    });
                });
                
                alertObserver.observe(alertElement, {
                    attributes: true,
                    attributeFilter: ['style']
                });
            }
        }

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
            
            // Clean up old data format by removing hawb_number if exists
            localStorage.removeItem('livewireVehicleForm');
            localStorage.setItem('livewireVehicleForm', JSON.stringify(storageData));
            
            console.log('Saved to storage:', storageData);
        }

        function loadStoredData() {
            // This function is now mainly for showing the alert
            // The actual form filling is handled by the load-stored-data event
            const saved = localStorage.getItem('livewireVehicleForm');
            console.log('Loading stored data:', saved);
            
            if (saved) {
                try {
                    const data = JSON.parse(saved);
                    console.log('Parsed stored data:', data);
                    
                    // Clean up old format - remove hawb_number if exists
                    if (data.hawb_number !== undefined) {
                        console.log('Cleaning up old storage format...');
                        delete data.hawb_number;
                        localStorage.setItem('livewireVehicleForm', JSON.stringify(data));
                    }
                    
                    // Always try to show alert if we have stored data
                    if (!alertShown) {
                        console.log('Attempting to show alert with savedAt:', data.savedAt);
                        showStoredDataAlert(data.savedAt || null);
                    } else {
                        console.log('Alert already shown, skipping');
                    }
                } catch (e) {
                    console.error('Error loading stored data:', e);
                    // Even if there's an error, try to show a basic alert
                    if (!alertShown) {
                        showStoredDataAlert(null);
                    }
                }
            } else {
                console.log('No stored data found');
            }
        }

        function showStoredDataAlert(savedAt) {
            if (alertShown) return; // Prevent showing multiple times
            
            const alertElement = document.getElementById('stored-data-alert');
            const messageElement = document.getElementById('stored-data-message');
            
            console.log('showStoredDataAlert called with:', savedAt);
            console.log('Alert element:', alertElement);
            console.log('Message element:', messageElement);
            
            if (alertElement && messageElement) {
                let displayText = 'Đã có dữ liệu được lưu trước đó';
                
                if (savedAt) {
                    try {
                        const formattedTime = formatDateTime(savedAt);
                        displayText = `Lần gửi thành công: ${formattedTime}`;
                        console.log('Formatted time:', formattedTime);
                    } catch (e) {
                        console.error('Error formatting date:', e);
                    }
                }
                
                messageElement.textContent = displayText;
                console.log('Set message text to:', displayText);
                
                // Show the alert and mark as shown
                alertElement.style.display = 'block';
                alertElement.style.visibility = 'visible';
                alertElement.style.opacity = '1';
                alertShown = true;
                
                console.log('Alert shown, alertShown flag set to true');
                
                // Ensure it stays visible by setting it again after a short delay
                setTimeout(() => {
                    if (alertElement.style.display !== 'none') {
                        alertElement.style.display = 'block';
                        alertElement.style.visibility = 'visible';
                        alertElement.style.opacity = '1';
                        messageElement.textContent = displayText; // Ensure text is still there
                        console.log('Alert visibility reinforced');
                    }
                }, 200);
            } else {
                console.error('Alert or message element not found');
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
                alertElement.style.visibility = 'hidden';
                alertElement.style.opacity = '0';
            }
            
            // Reset the flag
            alertShown = false;
            
            // Reload the page to reset the form
            window.location.reload();
        }
    </script>
</div>
