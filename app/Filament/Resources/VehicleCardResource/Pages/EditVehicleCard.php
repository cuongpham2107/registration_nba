<?php

namespace App\Filament\Resources\VehicleCardResource\Pages;

use App\Filament\Resources\VehicleCardResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVehicleCard extends EditRecord
{
    protected static string $resource = VehicleCardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
