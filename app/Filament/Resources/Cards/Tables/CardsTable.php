<?php

namespace App\Filament\Resources\Cards\Tables;

use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CardsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(self::getColumns())
            ->recordActions(self::getRecordActions())
            ->bulkActions(self::getBulkActions());
    }

    private static function getColumns(): array
    {
        return [
            TextColumn::make('card_number')
                ->label('Mã số thẻ')
                ->searchable(),
            TextColumn::make('card_name')
                ->label('Tên thẻ')
                ->searchable(),
            TextColumn::make('status')
                ->label('Trạng thái')
                ->searchable()
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'active' => 'success',
                    'inactive' => 'gray',
                    'blocked' => 'danger',
                })
                ->formatStateUsing(fn (string $state) => match ($state) {
                    'active' => 'Đang sử dụng',
                    'inactive' => 'Chưa sử dụng',
                    'blocked' => 'Bị khóa',
                }),
        ];
    }

    private static function getRecordActions(): array
    {
        return [
            EditAction::make()
                ->modalHeading('Chỉnh sửa thẻ'),
        ];
    }

    private static function getBulkActions(): array
    {
        return [
            DeleteBulkAction::make(),
        ];
    }
}
