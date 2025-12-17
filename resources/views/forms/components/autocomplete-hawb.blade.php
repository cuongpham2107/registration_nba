@php
    $statePath = $getStatePath();
    $apiEndpoint = $getApiEndpoint();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div 
        x-data="{
            state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')") }},
            searchResults: [],
            isLoading: false,
            showResults: false,
            selectedIndex: -1,
            searchTimeout: null,
            
            async searchHawb(query) {
                if (!query || query.length < 2) {
                    this.searchResults = [];
                    this.showResults = false;
                    return;
                }
                
                this.isLoading = true;
                
                try {
                    const response = await fetch(`https://wh-nba.asgl.net.vn/api/check-in/hawb?search=${encodeURIComponent(query)}`);
                    const data = await response.json();
                    
                    if (data && data.success && data.data && data.data.hawb && Array.isArray(data.data.hawb)) {
                        this.searchResults = data.data.hawb.map(item => ({
                            hawb: item.Hawb,
                            pcs: item.Pcs
                        }));
                        this.showResults = this.searchResults.length > 0;
                    } else {
                        this.searchResults = [];
                        this.showResults = false;
                    }
                } catch (error) {
                    console.error('Error fetching HAWB data:', error);
                    this.searchResults = [];
                    this.showResults = false;
                } finally {
                    this.isLoading = false;
                }
            },
            
            selectHawb(hawb, pcs) {
                this.state = hawb;
                this.showResults = false;
                this.searchResults = [];
                this.selectedIndex = -1;
                
                // Trigger Livewire update to set PCS
                $wire.set('{{ $statePath }}', hawb);
            },
            
            handleInput(event) {
                const value = event.target.value;
                
                // Clear previous timeout
                if (this.searchTimeout) {
                    clearTimeout(this.searchTimeout);
                }
                
                // Debounce: wait 500ms after user stops typing
                this.searchTimeout = setTimeout(() => {
                    this.searchHawb(value);
                }, 500);
            },
            
            handleKeydown(event) {
                if (!this.showResults) return;
                
                // Arrow Down
                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    this.selectedIndex = Math.min(this.selectedIndex + 1, this.searchResults.length - 1);
                    this.scrollToSelected();
                }
                // Arrow Up
                else if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
                    this.scrollToSelected();
                }
                // Enter
                else if (event.key === 'Enter' && this.selectedIndex >= 0) {
                    event.preventDefault();
                    const selected = this.searchResults[this.selectedIndex];
                    this.selectHawb(selected.hawb, selected.pcs);
                }
                // Escape
                else if (event.key === 'Escape') {
                    this.showResults = false;
                    this.selectedIndex = -1;
                }
            },
            
            scrollToSelected() {
                this.$nextTick(() => {
                    const dropdown = this.$el.querySelector('.overflow-y-auto');
                    const selected = dropdown?.children[this.selectedIndex];
                    if (selected && dropdown) {
                        const dropdownRect = dropdown.getBoundingClientRect();
                        const selectedRect = selected.getBoundingClientRect();
                        
                        if (selectedRect.bottom > dropdownRect.bottom) {
                            selected.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                        } else if (selectedRect.top < dropdownRect.top) {
                            selected.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                        }
                    }
                });
            },
            
            handleClickOutside(event) {
                const element = this.$el;
                if (element && !element.contains(event.target)) {
                    this.showResults = false;
                    this.selectedIndex = -1;
                }
            },
            
            init() {
                const self = this;
                document.addEventListener('click', function(event) {
                    self.handleClickOutside(event);
                });
            }
        }"
        x-init="init()"
        class="relative"
    >
        <x-filament::input.wrapper>
            <x-filament::input
                type="text"
                x-model="state"
                x-on:input="handleInput"
                x-on:keydown="handleKeydown"
                x-on:focus="if (state && state.length >= 2) searchHawb(state)"
                placeholder="Nhập số HAWB..."
                {{ $attributes->merge($getExtraInputAttributes()) }}
            />
        </x-filament::input.wrapper>
        
        <!-- Loading Spinner -->
        <div 
            x-show="isLoading" 
            x-cloak
            class="absolute right-3 top-1/2 -translate-y-1/2"
        >
            <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
        
        <!-- Dropdown Results -->
        <div 
            x-show="showResults && searchResults.length > 0" 
            x-cloak
            class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg max-h-60 overflow-y-auto overflow-x-hidden"
            style="height: 300px; scrollbar-width: thin; scrollbar-color: rgba(156, 163, 175, 0.5) transparent;"
        >
            <template x-for="(result, index) in searchResults" :key="`hawb-${index}-${result.hawb || ''}`">
                <div 
                    x-on:click="selectHawb(result.hawb, result.pcs)"
                    :class="{
                        'bg-primary-50 dark:bg-primary-900': index === selectedIndex,
                        'hover:bg-gray-50 dark:hover:bg-gray-700': index !== selectedIndex
                    }"
                    class="px-4 py-3 cursor-pointer transition-colors border-b border-gray-100 dark:border-gray-700 last:border-b-0"
                >
                    <div class="flex justify-between items-center">
                        <span class="font-medium text-gray-900 dark:text-gray-100" x-text="result.hawb"></span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            <span x-text="result.pcs"></span> PCS
                        </span>
                    </div>
                </div>
            </template>
        </div>
        
        <!-- No Results -->
        <div 
            x-show="showResults && searchResults.length === 0 && !isLoading && state && state.length >= 2" 
            x-cloak
            class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-lg p-4 text-center text-gray-500 dark:text-gray-400"
        >
            Không tìm thấy HAWB
        </div>
    </div>
</x-dynamic-component>

<style>
    [x-cloak] {
        display: none !important;
    }
    
    /* Custom scrollbar for dropdown */
    .overflow-y-auto::-webkit-scrollbar {
        width: 8px;
    }
    
    .overflow-y-auto::-webkit-scrollbar-track {
        background: transparent;
    }
    
    .overflow-y-auto::-webkit-scrollbar-thumb {
        background-color: rgba(156, 163, 175, 0.5);
        border-radius: 4px;
    }
    
    .overflow-y-auto::-webkit-scrollbar-thumb:hover {
        background-color: rgba(156, 163, 175, 0.7);
    }
    
    /* Smooth scrolling */
    .overflow-y-auto {
        scroll-behavior: smooth;
    }
</style>
