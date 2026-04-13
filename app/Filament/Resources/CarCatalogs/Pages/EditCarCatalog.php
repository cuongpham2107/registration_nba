<?php

namespace App\Filament\Resources\CarCatalogs\Pages;

use App\Filament\Resources\CarCatalogs\CarCatalogResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCarCatalog extends EditRecord
{
    protected static string $resource = CarCatalogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
