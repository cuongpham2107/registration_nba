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
                $revenueData[] = (float) Invoice::whereHas('registerDirectly', fn ($q) => $q->whereDate('actual_date_out', $day->toDateString()))
                    // ->where('is_paid', true)
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
                $revenueData[] = (float) Invoice::whereHas('registerDirectly', fn ($q) => $q->whereDate('actual_date_out', $day->toDateString()))
                    // ->where('is_paid', true)
                    ->sum('amount');
            }
        } else {
            $manualRevenue = [1 => 44905000, 2 => 37760000, 3 => 64665000, 4 => 58555000, 5 => 69150000]; 

            for ($m = 1; $m <= 12; $m++) {
                if ($m > $now->month) break;
                $labels[] = 'Tháng '.$m;
                $invoiceSum = (float) Invoice::whereHas('registerDirectly', fn ($q) => $q->whereYear('actual_date_out', $now->year)->whereMonth('actual_date_out', $m))
                    // ->where('is_paid', true)
                    ->sum('amount');
                $revenueData[] = $invoiceSum ?: ($manualRevenue[$m] ?? 0);
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
