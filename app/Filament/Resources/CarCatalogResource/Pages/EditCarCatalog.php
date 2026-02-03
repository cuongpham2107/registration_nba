<?php

namespace App\Filament\Resources\CarCatalogResource\Pages;

use App\Filament\Resources\CarCatalogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCarCatalog extends EditRecord
{
    protected static string $resource = CarCatalogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
