<?php

namespace App\Filament\Resources\Areas\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;

class AreaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->label('Mã khu vực')
                    ->maxLength(255),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('Tên khu vực')
                    ->maxLength(255),
            ]);
    }
}
