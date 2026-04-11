<?php

namespace App\Filament\Resources\Fees\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('vehicle_type')
                    ->label('Loại phương tiện')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('full_day_fee')
                    ->label('Cả ngày 7h - 17h (Block 4h)')
                    ->money('VND')
                    ->sortable(),

                TextColumn::make('night_fee')
                    ->label('Sau 17h - 7h (Block 4h)')
                    ->money('VND')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Đang áp dụng')
                    ->boolean()
                    ->alignCenter()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->slideOver(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
