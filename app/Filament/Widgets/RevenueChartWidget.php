<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class RevenueChartWidget extends ChartWidget
{
    protected static ?string $heading = '📈 Biểu đồ doanh thu';

    protected static ?int $sort = 2;

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
        $revenueData = [];

        if ($filter === 'week') {
            for ($i = 6; $i >= 0; $i--) {
                $day = $now->copy()->subDays($i);
                $labels[] = $day->format('d/m');
                $revenueData[] = (float) Invoice::where('is_paid', true)
                    ->whereDate('paid_at', $day->toDateString())
                    ->sum('amount');
            }
        } elseif ($filter === 'month') {
            $daysInMonth = $now->daysInMonth;
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $day = Carbon::create($now->year, $now->month, $d, 0, 0, 0, $tz);
                if ($day->greaterThan($now)) {
                    break;
                }
                $labels[] = $day->format('d/m');
                $revenueData[] = (float) Invoice::where('is_paid', true)
                    ->whereDate('paid_at', $day->toDateString())
                    ->sum('amount');
            }
        } else {
            for ($m = 1; $m <= 12; $m++) {
                if ($m > $now->month) {
                    break;
                }
                $labels[] = 'Tháng '.$m;
                $revenueData[] = (float) Invoice::where('is_paid', true)
                    ->whereYear('paid_at', $now->year)
                    ->whereMonth('paid_at', $m)
                    ->sum('amount');
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Doanh thu (đ)',
                    'data' => $revenueData,
                    'backgroundColor' => 'rgba(99, 102, 241, 0.15)',
                    'borderColor' => 'rgba(99, 102, 241, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
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
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'ticks' => [
                        'callback' => null,
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Doanh thu (đ)',
                    ],
                ],

            ],
        ];
    }
}
