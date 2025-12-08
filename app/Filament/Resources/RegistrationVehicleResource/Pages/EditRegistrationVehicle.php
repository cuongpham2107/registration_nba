<?php

namespace App\Filament\Resources\RegistrationVehicleResource\Pages;

use App\Filament\Resources\RegistrationVehicleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRegistrationVehicle extends EditRecord
{
    protected static string $resource = RegistrationVehicleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    
}
