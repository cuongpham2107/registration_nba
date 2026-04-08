<?php

namespace App\Filament\Resources\Guests\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;

class GuestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('name')
                    ->label('Tên khách')
                    ->required(),
                Forms\Components\TextInput::make('papers')
                    ->label('Giấy tờ')
                    ->required(),
                Forms\Components\TextInput::make('type')
                    ->label('Loại')
                    ->required(),
                Forms\Components\TextInput::make('license_plate')
                    ->label('Biển số')
                    ->required(),
                Forms\Components\TextInput::make('note')
                    ->label('Ghi chú'),
            ]);
    }
}
