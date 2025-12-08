<?php

namespace App\Filament\Resources\RegisterDirectlyResource\Pages;

use App\Filament\Resources\RegisterDirectlyResource;
use Filament\Actions;

use Filament\Resources\Pages\ViewRecord;
class ViewRegisterDirectly extends ViewRecord
{
    protected static string $resource = RegisterDirectlyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
