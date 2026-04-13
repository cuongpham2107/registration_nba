<?php

namespace App\Filament\Resources\CarCatalogs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CarCatalogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('license_plate')
                    ->label('Biển số xe')
                    ->required()
                    ->columnSpanFull(),
                Toggle::make('is_paid')
                    ->label('Áp dụng không thu phí?')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
