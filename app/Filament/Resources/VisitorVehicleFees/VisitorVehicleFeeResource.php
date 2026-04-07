<?php

namespace App\Filament\Resources\VisitorVehicleFees;

use App\Filament\Resources\VisitorVehicleFees\Pages\CreateVisitorVehicleFee;
use App\Filament\Resources\VisitorVehicleFees\Pages\EditVisitorVehicleFee;
use App\Filament\Resources\VisitorVehicleFees\Pages\ListVisitorVehicleFees;
use App\Filament\Resources\VisitorVehicleFees\Schemas\VisitorVehicleFeeForm;
use App\Filament\Resources\VisitorVehicleFees\Tables\VisitorVehicleFeesTable;
use App\Models\VisitorVehicleFee;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class VisitorVehicleFeeResource extends Resource
{
    protected static ?string $model = VisitorVehicleFee::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static UnitEnum|string|null $navigationGroup = 'Các loại phí';

    protected static ?string $modelLabel = 'Phí phương tiện ra vào làm việc';

    protected static ?string $navigationLabel = 'Phí ra vào làm việc';

    protected static ?string $pluralModelLabel = 'Biểu phí phương tiện ra vào làm việc';

    public static function form(Schema $schema): Schema
    {
        return VisitorVehicleFeeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VisitorVehicleFeesTable::configure($table);
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
            'index' => ListVisitorVehicleFees::route('/'),
            // 'create' => CreateVisitorVehicleFee::route('/create'),
            // 'edit' => EditVisitorVehicleFee::route('/{record}/edit'),
        ];
    }
}
