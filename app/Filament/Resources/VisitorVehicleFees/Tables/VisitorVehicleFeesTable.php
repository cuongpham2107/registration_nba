<?php

namespace App\Filament\Resources\VisitorVehicleFees\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VisitorVehicleFeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('vehicle_type')
                    ->label('Loại phương tiện')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('per_visit_fee')
                    ->label('Phí theo lượt')
                    ->money('VND')
                    ->sortable(),

                TextColumn::make('monthly_fee')
                    ->label('Phí theo tháng')
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
