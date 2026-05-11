<?php

namespace App\Filament\Widgets;

use App\Models\RegisterDirectly;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class VehicleTrafficChartWidget extends ChartWidget
{
    protected static ?string $heading = '🚗 Lưu lượng xe vào/ra';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 6;

    public ?string $filter = 'week';

    protected function getFilters(): ?array
    {
        return [
            'week' => '7 ngày gần nhất',
            'month' => 'Tháng này',
            'year' => 'Năm này',
        ];
    }

    protected function getData(): array
    {
        $tz = 'Asia/Ho_Chi_Minh';
        $now = Carbon::now($tz);
        $filter = $this->filter;

        $labels = [];
        $inData = [];
        $outData = [];

        if ($filter === 'week') {
            for ($i = 6; $i >= 0; $i--) {
                $day = $now->copy()->subDays($i);
                $labels[] = $day->format('d/m');
                $inData[] = RegisterDirectly::whereDate('actual_date_in', $day->toDateString())->whereNotNull('actual_date_in')->count();
                $outData[] = RegisterDirectly::whereDate('actual_date_out', $day->toDateString())->whereNotNull('actual_date_out')->count();
            }
        } elseif ($filter === 'month') {
            $daysInMonth = $now->daysInMonth;
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $day = Carbon::create($now->year, $now->month, $d, 0, 0, 0, $tz);
                if ($day->greaterThan($now)) {
                    break;
                }
                $labels[] = $day->format('d/m');
                $inData[] = RegisterDirectly::whereDate('actual_date_in', $day->toDateString())->whereNotNull('actual_date_in')->count();
                $outData[] = RegisterDirectly::whereDate('actual_date_out', $day->toDateString())->whereNotNull('actual_date_out')->count();
            }
        } else {
            for ($m = 1; $m <= 12; $m++) {
                if ($m > $now->month) {
                    break;
                }
                $labels[] = 'Tháng '.$m;
                $inData[] = RegisterDirectly::whereYear('actual_date_in', $now->year)
                    ->whereMonth('actual_date_in', $m)
                    ->whereNotNull('actual_date_in')
                    ->count();
                $outData[] = RegisterDirectly::whereYear('actual_date_out', $now->year)
                    ->whereMonth('actual_date_out', $m)
                    ->whereNotNull('actual_date_out')
                    ->count();
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Lượt xe vào',
                    'data' => $inData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.6)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Lượt xe ra',
                    'data' => $outData,
                    'backgroundColor' => 'rgba(245, 158, 11, 0.6)',
                    'borderColor' => 'rgba(245, 158, 11, 1)',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'x' => [
                    'stacked' => false,
                ],
                'y' => [
                    'stacked' => false,
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Số lượt',
                    ],
                ],
            ],
        ];
    }
}
