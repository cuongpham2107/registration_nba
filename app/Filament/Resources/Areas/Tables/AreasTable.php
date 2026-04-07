<?php

namespace App\Filament\Resources\Areas\Tables;

use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AreasTable
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
            TextColumn::make('code')
                ->label('Mã khu vực')
                ->searchable(),
            TextColumn::make('name')
                ->label('Tên khu vực')
                ->searchable(),
            TextColumn::make('created_at')
                ->label('Ngày tạo')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
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
