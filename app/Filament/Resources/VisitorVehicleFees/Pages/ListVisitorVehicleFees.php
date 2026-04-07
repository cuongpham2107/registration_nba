<?php

namespace App\Filament\Resources\VisitorVehicleFees\Pages;

use App\Filament\Resources\VisitorVehicleFees\VisitorVehicleFeeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVisitorVehicleFees extends ListRecords
{
    protected static string $resource = VisitorVehicleFeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->slideOver(),
        ];
    }
}
