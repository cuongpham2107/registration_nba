<?php

namespace App\Filament\Resources\CarCatalogs\Pages;

use App\Filament\Resources\CarCatalogs\Actions\ImportCarCatalogsAction;
use App\Filament\Resources\CarCatalogs\CarCatalogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListCarCatalogs extends ListRecords
{
    protected static string $resource = CarCatalogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon('heroicon-o-plus')
                ->modalWidth(Width::Large)
                ->modal(),
            ImportCarCatalogsAction::make(),
        ];
    }
}
