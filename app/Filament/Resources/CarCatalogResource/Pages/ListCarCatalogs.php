<?php

namespace App\Filament\Resources\CarCatalogResource\Pages;

use App\Filament\Resources\CarCatalogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCarCatalogs extends ListRecords
{
    protected static string $resource = CarCatalogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Thêm danh mục xe mới')
                ->modal()
                ->modalHeading('Thêm danh mục xe mới')
                ->modalDescription('Nhập thông tin xe cần thêm mới'),
        ];
    }
}
