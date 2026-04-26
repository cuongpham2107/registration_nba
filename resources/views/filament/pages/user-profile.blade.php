<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex items-center gap-x-3">
            <div class="flex-1">
                <h1 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
                    Chào {{ auth()->user()->name }}
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Quản lý thông tin tài khoản của bạn tại đây.
                </p>
            </div>
        </div>

        <form wire:submit="save" class="space-y-6">
            {{ $this->form }}

            <div class="flex flex-wrap items-center gap-4 justify-start">
                <x-filament::button type="submit" size="lg">
                    Cập nhật thông tin
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
