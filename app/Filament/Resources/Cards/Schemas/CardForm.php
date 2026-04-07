<?php

namespace App\Filament\Resources\Cards\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;

class CardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('account_id')
                    ->label('Mã số thẻ')
                    ->required(),
                Forms\Components\TextInput::make('card_number')
                    ->label('Số thẻ')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('card_name')
                    ->label('Tên thẻ')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'active' => 'Đang sử dụng',
                        'inactive' => 'Chưa sử dụng',
                        'blocked' => 'Bị khóa',
                    ])
                    ->default('inactive')
                    ->required(),
            ])->columns(1);
    }
}
