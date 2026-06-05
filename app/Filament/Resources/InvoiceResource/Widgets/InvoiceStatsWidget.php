<?php

namespace App\Filament\Resources\InvoiceResource\Widgets;

use App\Models\Invoice;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InvoiceStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 12;

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        $tz = 'Asia/Ho_Chi_Minh';
        $now = Carbon::now($tz);

        $total = Invoice::sum('amount');
        $paid = Invoice::where('is_paid', true)->sum('amount');
        $unpaid = Invoice::where('is_paid', false)->sum('amount');

        $todayRevenue = Invoice::where('is_paid', true)
            ->whereDate('paid_at', $now->toDateString())
            ->sum('amount');

        $yesterdayRevenue = Invoice::where('is_paid', true)
            ->whereDate('paid_at', $now->copy()->subDay()->toDateString())
            ->sum('amount');

        $paidCount = Invoice::where('is_paid', true)->count();
        $unpaidCount = Invoice::where('is_paid', false)->count();

        $paidChart = [];
        $unpaidChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = $now->copy()->subDays($i)->toDateString();
            $paidChart[] = (float) Invoice::where('is_paid', true)->whereDate('paid_at', $day)->sum('amount');
            $unpaidChart[] = (float) Invoice::where('is_paid', false)->whereDate('created_at', $day)->sum('amount');
        }

        return [
            Stat::make('💰 Tổng cộng', number_format($total, 0, ',', '.') . ' đ')
                ->description('Tổng số tiền tất cả hóa đơn')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),

            Stat::make('✅ Đã thanh toán', number_format($paid, 0, ',', '.') . ' đ')
                ->description($paidCount . ' hóa đơn · Hôm nay: ' . number_format((int) $todayRevenue, 0, ',', '.') . ' đ')
                ->descriptionIcon($todayRevenue >= $yesterdayRevenue ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color('success')
                ->chart($paidChart),

            Stat::make('⏳ Chưa thanh toán', number_format($unpaid, 0, ',', '.') . ' đ')
                ->description($unpaidCount . ' hóa đơn chưa thanh toán')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->chart($unpaidChart),
        ];
    }
}
