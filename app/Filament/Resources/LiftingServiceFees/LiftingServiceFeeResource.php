<?php

namespace App\Filament\Resources\LiftingServiceFees;

use App\Filament\Resources\LiftingServiceFees\Pages\CreateLiftingServiceFee;
use App\Filament\Resources\LiftingServiceFees\Pages\EditLiftingServiceFee;
use App\Filament\Resources\LiftingServiceFees\Pages\ListLiftingServiceFees;
use App\Filament\Resources\LiftingServiceFees\Schemas\LiftingServiceFeeForm;
use App\Filament\Resources\LiftingServiceFees\Tables\LiftingServiceFeesTable;
use App\Models\LiftingServiceFee;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LiftingServiceFeeResource extends Resource
{
    protected static ?string $model = LiftingServiceFee::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static UnitEnum|string|null $navigationGroup = 'Các loại phí';

    protected static ?string $modelLabel = 'Phí dịch vụ nâng hạ';

    protected static ?string $navigationLabel = 'Phí dịch vụ nâng hạ';

    protected static ?string $pluralModelLabel = 'Biểu phí dịch vụ nâng hạ';

    public static function form(Schema $schema): Schema
    {
        return LiftingServiceFeeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LiftingServiceFeesTable::configure($table);
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
            'index' => ListLiftingServiceFees::route('/'),
            // 'create' => CreateLiftingServiceFee::route('/create'),
            // 'edit' => EditLiftingServiceFee::route('/{record}/edit'),
        ];
    }
}
