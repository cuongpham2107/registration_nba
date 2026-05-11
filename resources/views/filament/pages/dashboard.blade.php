<x-filament-panels::page>
    <div class="grid grid-cols-12 gap-8">
        <div class="col-span-12">
            @livewire(\App\Filament\Widgets\StatsOverviewWidget::class)
        </div>
        <div class="col-span-12" style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            <div style="min-width: 0;">
                @livewire(\App\Filament\Widgets\RevenueChartWidget::class)
            </div>
            <div style="min-width: 0;">
                @livewire(\App\Filament\Widgets\VehicleTrafficChartWidget::class)
            </div>
        </div>
    </div>
</x-filament-panels::page>
