<?php

namespace App\Livewire;

use App\Models\VisitorRegistration;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RegistrationStart extends BaseWidget
{
    protected function getColumns(): int
    {
        $count = count($this->getCachedStats());

        if ($count < 3) {
            return 3;
        }

        if (($count % 3) !== 1) {
            return 3;
        }

        return 4;
    }

    protected function getStats(): array
    {
        $registration_count = VisitorRegistration::get()->count();
        $registrater_count = RegistrationEntry::get()->count();
        $registrater_new_date_now = RegistrationEntry::where('end_date', Carbon::now('Asia/Ho_Chi_Minh'))->get()->count();

        return [
            Stat::make('Đăng kí mới hôm nay', $registrater_new_date_now.' người')
                ->description('7% increase')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
            Stat::make('Tổng số lượt đăng kí trước', $registration_count.' lượt'),
            // ->description('32k increase')
            // ->descriptionIcon('heroicon-m-arrow-trending-up')
            // ->color('success'),
            Stat::make('Tổng số lượng đăng kí trực tiếp', $registrater_count.' lượt'),
            // ->description('7% increase')
            // ->descriptionIcon('heroicon-m-arrow-trending-down')
            // ->color('danger'),

        ];
    }
}
