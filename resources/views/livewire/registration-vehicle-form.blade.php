<div class="min-h-screen bg-gradient-to-br from-blue-500 to-blue-700 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-6 py-8 text-center">
                <div class="flex justify-center mb-4">
                    <img src="{{ asset('images/ASG.png') }}" alt="ASG Logo" class="h-16 w-16">
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">Đăng ký xe khai thác</h1>
                <p class="text-blue-100">Vui lòng điền đầy đủ thông tin bên dưới</p>
            </div>

            <!-- Form Content -->
            <div class="px-6 py-8">
                <form wire:submit="create">
                    {{ $this->form }}

                    <div class="mt-8 flex gap-4">
                        <button 
                            type="submit"
                            class="flex-1 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold py-3 px-6 rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200"
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
</div>
