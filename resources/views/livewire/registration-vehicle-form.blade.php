<div class="min-h-screen bg-[#5287ad] py-1 px-1 sm:px-3 lg:px-4 flex items-center justify-center">
    <div class="max-w-md mx-auto">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col w-full h-screen justify-between" >
            <!-- Header -->
            <div>
                <div class=" px-1 pt-2 text-center">
                    <div class="flex justify-center mb-3">
                        <img src="{{ asset('images/ASG.png') }}" alt="ASG Logo" class="h-8 w-24">
                    </div>
                    <h1 class="text-xl font-bold">Đăng ký xe khai thác</h1>
                </div>
                <div class="text-end">
                     @if (!$isListRegistered)
                    <a href="#" wire:click.prevent="showRegisteredList" class=" text-blue-600 hover:underline italic cursor-pointer text-xs font-semibold px-2 rounded-lg">
                        Kiểm tra đăng ký
                    </a>
                     @else
                     <a href="#" wire:click.prevent="showRegisteredList" class=" text-blue-600 hover:underline italic cursor-pointer text-xs font-semibold px-2 rounded-lg">
                        Quay lại đăng ký
                    </a>
                    @endif
                </div>
            </div>
            
            <!-- Main Content Area -->
             <div class="flex-1 overflow-y-auto">
                 <!-- Form Content -->
                 @if (! $isListRegistered)
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
                 @endif
 
                 <!-- Danh sach dang ky -->
                @if($isListRegistered)
                 <div class="px-3 py-2 flex flex-col min-h-screen" >
                     <div class="flex-shrink-0">
                         <div class="flex items-center justify-between mb-4">
                             <h2 class="text-lg font-semibold">Danh sách đăng ký</h2>
                         </div>

                         <!-- Search Bar -->
                         <div class="mb-4">
                             <div class="relative">
                                 <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                     <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                     </svg>
                                 </div>
                                 <input 
                                     type="text" 
                                     wire:model.live.debounce.500ms="searchDriver" 
                                     placeholder="Tìm tên tài xế hoặc biển số xe." 
                                     class="block w-full pl-10 pr-3 py-2 border border-blue-200 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 bg-blue-50/30"
                                 >
                                 <div wire:loading wire:target="searchDriver" class="absolute top-1/2 right-3 -translate-y-1/2">
                                     <svg class="animate-spin h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                         <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                         <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                     </svg>
                                 </div>
                             </div>
                         </div>
                     </div>

                     <!-- List/Loading/Empty Area -->
                     <div class="flex-1 flex flex-col min-h-0">
                         <!-- Loading indicator for the list -->
                         <div wire:loading.flex wire:target="searchDriver" class="flex-1 items-center justify-center py-10">
                             <div class="inline-flex items-center px-4 py-2 leading-6 text-sm text-black transition ease-in-out duration-150 cursor-not-allowed">
                                 <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-black" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                     <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                     <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                 </svg>
                                 Đang tìm kiếm...
                             </div>
                         </div>

                         <div wire:loading.remove wire:target="searchDriver" class="flex-1 flex flex-col">
                             @if(empty($registrations))
                                 <div class="flex-1 flex items-center justify-center py-10">
                                     <p class="text-sm text-gray-500 italic">Không có đăng ký nào.</p>
                                 </div>
                             @else
                                 <div class="space-y-3">
                                     <ul class="divide-y divide-gray-100">
                                         @foreach($registrations as $reg)
                                             <li class="py-3">
                                                 <div class="bg-white shadow-sm rounded-xl p-3 hover:shadow-md transition-shadow border border-gray-200">
                                                     <div class="flex items-start justify-between gap-3">
                                                         <div class="flex-1 min-w-0">
                                                             <p class="text-sm font-semibold text-gray-900 truncate">{{ $reg['driver_name'] ?? '-' }}</p>
                                                             <p class="text-xs text-gray-500 mt-0.5 truncate">{{ $reg['vehicle_number'] ?? '-' }}</p>

                                                             @if(!empty($reg['hawbs']))
                                                                 <div class="mt-2">
                                                                     <div class="flex gap-2 overflow-x-hidden" style="max-width:18rem; -webkit-overflow-scrolling: touch;">
                                                                         @foreach($reg['hawbs'] as $hawb)
                                                                             <span class="flex-shrink-0 text-xs bg-gray-100 text-gray-800 px-2 py-0.5 rounded-full">{{ $hawb }}</span>
                                                                         @endforeach
                                                                     </div>
                                                                 </div>
                                                             @endif
                                                         </div>

                                                         <div class="flex-shrink-0 text-right">
                                                             <p class="text-xs text-gray-500">{{ $reg['expected_in_at'] ?? '' }}</p>
                                                             <div class="mt-2">
                                                                 <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium {{ $reg['status_classes'] ?? '' }}">{{ $reg['status_label'] ?? $reg['status'] ?? '' }}</span>
                                                             </div>
                                                         </div>
                                                     </div>
                                                 </div>
                                             </li>
                                         @endforeach
                                     </ul>
                                 </div>
                             @endif
                         </div>
                     </div>
                 </div>
                @endif
         </div>

            <!-- Footer -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 ">
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
