<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\RevenueChartWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\VehicleTrafficChartWidget;
use Carbon\Carbon;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Bảng điều khiển';

    protected static ?string $title = 'Bảng điều khiển';

    protected static ?int $navigationSort = -2;

    public function getWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
            RevenueChartWidget::class,
            VehicleTrafficChartWidget::class,
        ];
    }

    public function getColumns(): int|string|array
    {
        return [
            'md' => 2,
            'xl' => 2,
        ];
    }

    public function getSubheading(): ?string
    {
        $now = Carbon::now('Asia/Ho_Chi_Minh');

        return 'Cập nhật lúc: '.$now->format('H:i:s - d/m/Y');
    }
}
