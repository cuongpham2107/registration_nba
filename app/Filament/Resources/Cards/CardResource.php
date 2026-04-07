<?php

namespace App\Filament\Resources\Cards;

use App\Filament\Resources\Cards\Pages\ListCards;
use App\Filament\Resources\Cards\Schemas\CardForm;
use App\Filament\Resources\Cards\Tables\CardsTable;
use App\Models\Card;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CardResource extends Resource
{
    protected static ?string $model = Card::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $modelLabel = 'Thẻ';

    public static function getNavigationLabel(): string
    {
        return 'Danh sách thẻ';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Quản lý danh mục';
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function form(Schema $schema): Schema
    {
        return CardForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CardsTable::configure($table);
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
            'index' => ListCards::route('/'),
        ];
    }
}
