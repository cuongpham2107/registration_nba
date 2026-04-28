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

    protected int|string|array $columnSpan = 'full';

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

        $todayIn = RegisterDirectly::whereDate('actual_date_in', $todayDate)->count();
        $todayOut = RegisterDirectly::whereDate('actual_date_out', $todayDate)->count();
        $todayRevenue = Invoice::where('is_paid', true)
            ->whereDate('paid_at', $todayDate)
            ->sum('amount');

        // So sánh với hôm qua
        $yesterdayDate = $now->copy()->subDay()->toDateString();
        $yesterdayRevenue = Invoice::where('is_paid', true)
            ->whereDate('paid_at', $yesterdayDate)
            ->sum('amount');

        // ===== THÁNG NÀY =====
        $monthRevenue = Invoice::where('is_paid', true)
            ->whereYear('paid_at', $now->year)
            ->whereMonth('paid_at', $now->month)
            ->sum('amount');

        $monthIn = RegisterDirectly::whereYear('actual_date_in', $now->year)
            ->whereMonth('actual_date_in', $now->month)
            ->whereNotNull('actual_date_in')
            ->count();

        $monthOut = RegisterDirectly::whereYear('actual_date_out', $now->year)
            ->whereMonth('actual_date_out', $now->month)
            ->whereNotNull('actual_date_out')
            ->count();

        // So sánh với tháng trước
        $lastMonth = $now->copy()->subMonth();
        $lastMonthRevenue = Invoice::where('is_paid', true)
            ->whereYear('paid_at', $lastMonth->year)
            ->whereMonth('paid_at', $lastMonth->month)
            ->sum('amount');

        // ===== NĂM NAY =====
        $yearRevenue = Invoice::where('is_paid', true)
            ->whereYear('paid_at', $now->year)
            ->sum('amount');

        $yearIn = RegisterDirectly::whereYear('actual_date_in', $now->year)
            ->whereNotNull('actual_date_in')
            ->count();

        $yearOut = RegisterDirectly::whereYear('actual_date_out', $now->year)
            ->whereNotNull('actual_date_out')
            ->count();

        // ===== ĐANG TRONG BÃI =====
        $currentlyInside = RegisterDirectly::where('status', 'coming_in')->count();

        // ===== CHART DATA (7 ngày gần nhất) =====
        $revenueChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = $now->copy()->subDays($i)->toDateString();
            $revenueChart[] = (float) Invoice::where('is_paid', true)->whereDate('paid_at', $day)->sum('amount');
        }

        $inChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = $now->copy()->subDays($i)->toDateString();
            $inChart[] = RegisterDirectly::whereDate('actual_date_in', $day)->count();
        }

        $outChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = $now->copy()->subDays($i)->toDateString();
            $outChart[] = RegisterDirectly::whereDate('actual_date_out', $day)->count();
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
                    : 'Chưa có dữ liệu tháng trước')
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
