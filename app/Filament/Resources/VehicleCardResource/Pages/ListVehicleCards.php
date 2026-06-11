<?php

namespace App\Filament\Resources\VehicleCardResource\Pages;

use App\Filament\Resources\VehicleCardResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVehicleCards extends ListRecords
{
    protected static string $resource = VehicleCardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modal()
                ->modalHeading('Thêm thẻ xe mới')
                ->modalDescription('Nhập thông tin thẻ xe mới'),
        ];
    }
}
