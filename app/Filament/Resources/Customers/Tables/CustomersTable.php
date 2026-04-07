<?php

namespace App\Filament\Resources\Customers\Tables;

use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns(self::getColumns())
            ->recordActions(self::getRecordActions())
            ->toolbarActions(self::getBulkActions());
    }

    private static function getColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label('Tên khách')
                ->searchable()
                ->sortable(),
            TextColumn::make('papers')
                ->label('Giấy tờ')
                ->searchable(),
            TextColumn::make('type')
                ->label('Loại')
                ->searchable(),
            TextColumn::make('license_plate')
                ->label('Biển số')
                ->searchable(),
            TextColumn::make('note')
                ->label('Ghi chú')
                ->searchable(),
        ];
    }

    private static function getRecordActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    private static function getBulkActions(): array
    {
        return [
            DeleteBulkAction::make(),
        ];
    }
}
