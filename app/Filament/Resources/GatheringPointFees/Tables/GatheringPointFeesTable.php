<?php

namespace App\Filament\Resources\GatheringPointFees\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GatheringPointFeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('vehicle_type')
                    ->label('Loại phương tiện')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('morning_fee')
                    ->label('Từ 7h - 12h')
                    ->money('VND')
                    ->sortable(),

                TextColumn::make('afternoon_fee')
                    ->label('Từ 12h - 17h')
                    ->money('VND')
                    ->sortable(),

                TextColumn::make('full_day_fee')
                    ->label('Cả ngày 7h - 17h')
                    ->money('VND')
                    ->sortable(),

                TextColumn::make('night_fee')
                    ->label('Sau 17h - 7h')
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
