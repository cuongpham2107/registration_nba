<?php

namespace App\Filament\Resources\VisitorVehicleFees\Pages;

use App\Filament\Resources\VisitorVehicleFees\VisitorVehicleFeeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVisitorVehicleFee extends EditRecord
{
    protected static string $resource = VisitorVehicleFeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
