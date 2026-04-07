<?php

namespace App\Filament\Resources\GatheringPointFees;

use App\Filament\Resources\GatheringPointFees\Pages\CreateGatheringPointFee;
use App\Filament\Resources\GatheringPointFees\Pages\EditGatheringPointFee;
use App\Filament\Resources\GatheringPointFees\Pages\ListGatheringPointFees;
use App\Filament\Resources\GatheringPointFees\Schemas\GatheringPointFeeForm;
use App\Filament\Resources\GatheringPointFees\Tables\GatheringPointFeesTable;
use App\Models\GatheringPointFee;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class GatheringPointFeeResource extends Resource
{
    protected static ?string $model = GatheringPointFee::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static UnitEnum|string|null $navigationGroup = 'Các loại phí';

    protected static ?string $modelLabel = 'Phí địa điểm tập trung';

    protected static ?string $navigationLabel = 'Phí địa điểm tập trung';

    protected static ?string $pluralModelLabel = 'Biểu phí địa điểm tập trung';

    public static function form(Schema $schema): Schema
    {
        return GatheringPointFeeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GatheringPointFeesTable::configure($table);
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
            'index' => ListGatheringPointFees::route('/'),
            // 'create' => CreateGatheringPointFee::route('/create'),
            // 'edit' => EditGatheringPointFee::route('/{record}/edit'),
        ];
    }
}
