<?php

namespace App\Livewire;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\RegisterDirectly;
use App\Models\Registration;
use Carbon\Carbon;

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
        $registration_count = Registration::get()->count();
        $registrater_count = RegisterDirectly::get()->count();
        $registrater_new_date_now = RegisterDirectly::where('end_date', Carbon::now('Asia/Ho_Chi_Minh'))->get()->count();
        return [
            Stat::make('Đăng kí mới hôm nay', $registrater_new_date_now  .' người')
                ->description('7% increase')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
            Stat::make('Tổng số lượt đăng kí trước', $registration_count . ' lượt'),
                // ->description('32k increase')
                // ->descriptionIcon('heroicon-m-arrow-trending-up')
                // ->color('success'),
            Stat::make('Tổng số lượng đăng kí trực tiếp', $registrater_count . ' lượt')
                // ->description('7% increase')
                // ->descriptionIcon('heroicon-m-arrow-trending-down')
                // ->color('danger'),
           
           
        ];
    }
}
