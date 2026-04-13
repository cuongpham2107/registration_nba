<?php

namespace App\Filament\Resources\CarCatalogs;

use App\Filament\Resources\CarCatalogs\Pages\CreateCarCatalog;
use App\Filament\Resources\CarCatalogs\Pages\EditCarCatalog;
use App\Filament\Resources\CarCatalogs\Pages\ListCarCatalogs;
use App\Filament\Resources\CarCatalogs\Schemas\CarCatalogForm;
use App\Filament\Resources\CarCatalogs\Tables\CarCatalogsTable;
use App\Models\CarCatalog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CarCatalogResource extends Resource
{
    protected static ?string $model = CarCatalog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?string $recordTitleAttribute = 'license_plate';

    protected static ?string $modelLabel = 'Danh mục xe';

    public static function getNavigationLabel(): string
    {
        return 'Danh mục xe';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Quản lý danh mục';
    }

    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    public static function form(Schema $schema): Schema
    {
        return CarCatalogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CarCatalogsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCarCatalogs::route('/'),
            // 'create' => CreateCarCatalog::route('/create'),
            // 'edit' => EditCarCatalog::route('/{record}/edit'),
        ];
    }
}
