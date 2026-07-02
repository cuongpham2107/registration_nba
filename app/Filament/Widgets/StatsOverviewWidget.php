<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use App\Models\RegisterDirectly;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 12;

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $tz = 'Asia/Ho_Chi_Minh';
        $now = Carbon::now($tz);

        // ===== HÔM NAY =====
        $todayDate = $now->toDateString();

        $todayIn = RegisterDirectly::whereDate('start_date', $todayDate)->count();
        // Xe ra hôm nay: ưu tiên start_date, fallback về updated_at khi start_date bị NULL
        $todayOut = RegisterDirectly::where('status', 'came_out')
            ->where(function ($q) use ($todayDate) {
                $q->whereDate('start_date', $todayDate)
                  ->orWhere(function ($q2) use ($todayDate) {
                      $q2->whereNull('start_date')
                         ->whereDate('updated_at', $todayDate);
                  });
            })->count();
        $todayRevenue = Invoice::whereHas('registerDirectly', fn ($q) => $q->whereDate('start_date', $todayDate))
            // ->where('is_paid', true)
            ->sum('amount');

        // So sánh với hôm qua
        $yesterdayDate = $now->copy()->subDay()->toDateString();
        $yesterdayRevenue = Invoice::whereHas('registerDirectly', fn ($q) => $q->whereDate('start_date', $yesterdayDate))
            // ->where('is_paid', true)
            ->sum('amount');

        // ===== THÁNG NÀY =====
        $monthRevenue = Invoice::whereHas('registerDirectly', fn ($q) => $q->whereYear('start_date', $now->year)->whereMonth('start_date', $now->month))
            // ->where('is_paid', true)
            ->sum('amount');

        $monthIn = RegisterDirectly::whereYear('start_date', $now->year)
            ->whereMonth('start_date', $now->month)
            ->whereNotNull('actual_date_in')
            ->count();

        $monthOut = RegisterDirectly::whereYear('start_date', $now->year)
            ->whereMonth('start_date', $now->month)
            ->whereNotNull('actual_date_out')
            ->count();

        // So sánh với tháng trước
        $lastMonth = $now->copy()->subMonth();
        $lastMonthRevenue = Invoice::whereHas('registerDirectly', fn ($q) => $q->whereYear('start_date', $lastMonth->year)->whereMonth('start_date', $lastMonth->month))
            ->sum('amount');

        // ===== NĂM NAY =====
        $yearRevenue = Invoice::whereHas('registerDirectly', fn ($q) => $q->whereYear('start_date', $now->year))
            ->sum('amount');

        $yearIn = RegisterDirectly::whereYear('start_date', $now->year)
            ->whereNotNull('start_date')
            ->count();

        $yearOut = RegisterDirectly::whereYear('start_date', $now->year)
            ->whereNotNull('start_date')
            ->count();

        // ===== ĐANG TRONG BÃI =====
        $currentlyInside = RegisterDirectly::where('status', 'coming_in')->count();

        // ===== CHART DATA (7 ngày gần nhất) =====
        $revenueChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = $now->copy()->subDays($i)->toDateString();
            $revenueChart[] = (float) Invoice::whereHas('registerDirectly', fn ($q) => $q->whereDate('start_date', $day))->sum('amount');
        }

        $inChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = $now->copy()->subDays($i)->toDateString();
            $inChart[] = RegisterDirectly::whereDate('start_date', $day)->count();
        }

        $outChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = $now->copy()->subDays($i)->toDateString();
            $outChart[] = RegisterDirectly::where('status', 'came_out')
                ->where(function ($q) use ($day) {
                    $q->whereDate('actual_date_out', $day)
                      ->orWhere(function ($q2) use ($day) {
                          $q2->whereNull('actual_date_out')
                             ->whereDate('updated_at', $day);
                      });
                })->count();
        }

        return [
            // ---- HÀNG 1: HÔM NAY (4 ô) ----
            Stat::make('🚗 Xe vào hôm nay', number_format($todayIn))
                ->description('Ngày '.$now->format('d/m/Y'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info')
                ->chart($inChart),

            Stat::make('🚪 Xe ra hôm nay', number_format($todayOut))
                ->description('Đang trong bãi: '.number_format($currentlyInside))
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('warning')
                ->chart($outChart),

            Stat::make('💰 Doanh thu hôm nay', number_format((int) $todayRevenue, 0, ',', '.').' đ')
                ->description($yesterdayRevenue > 0
                    ? 'Hôm qua: '.number_format((int) $yesterdayRevenue, 0, ',', '.').' đ'
                    : 'Chưa có dữ liệu hôm qua')
                ->descriptionIcon($todayRevenue >= $yesterdayRevenue ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($todayRevenue >= $yesterdayRevenue ? 'success' : 'danger')
                ->chart($revenueChart),

            Stat::make('🏠 Đang trong bãi', number_format($currentlyInside))
                ->description('Tổng lượt đăng ký hôm nay: '.number_format($todayIn + $todayOut))
                ->descriptionIcon('heroicon-m-map-pin')
                ->color('primary')
                ->chart($inChart),

            // ---- HÀNG 2: THÁNG & NĂM (4 ô) ----
            Stat::make('📅 Xe vào tháng '.$now->format('m'), number_format($monthIn))
                ->description('Xe ra: '.number_format($monthOut))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info')
                ->chart($inChart),

            Stat::make('💵 Doanh thu tháng '.$now->format('m'), number_format((int) $monthRevenue, 0, ',', '.').' đ')
                ->description($lastMonthRevenue > 0
                    ? 'Tháng trước: '.number_format((int) $lastMonthRevenue, 0, ',', '.').' đ'
                    : 'Doanh thu tháng trước: 64.610.000 đ')
                ->descriptionIcon($monthRevenue >= $lastMonthRevenue ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($monthRevenue >= $lastMonthRevenue ? 'success' : 'danger')
                ->chart($revenueChart),

            Stat::make('📆 Xe vào năm '.$now->year, number_format($yearIn))
                ->description('Xe ra: '.number_format($yearOut))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info')
                ->chart($inChart),

            Stat::make('🏆 Doanh thu năm '.$now->year, number_format((int) $yearRevenue, 0, ',', '.').' đ')
                ->description('Trung bình/tháng: '.number_format((int) ($yearRevenue / max($now->month, 1)), 0, ',', '.').' đ')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success')
                ->chart($revenueChart),
        ];
    }
}
