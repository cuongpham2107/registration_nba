<?php

namespace App\Filament\Resources\LiftingServiceFees\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LiftingServiceFeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('service_name')
                    ->label('Tên dịch vụ')
                    ->searchable()
                    ->sortable(),

                BadgeColumn::make('weight_category')
                    ->label('Phân loại')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'under_2_tons' => 'Dưới 2 tấn',
                        'over_2_tons' => 'Trên 2 tấn',
                        default => $state,
                    })
                    ->colors([
                        'success' => 'under_2_tons',
                        'warning' => 'over_2_tons',
                    ])
                    ->sortable(),

                TextColumn::make('regular_hours_fee')
                    ->label('Từ 7h30 đến 16h30')
                    ->money('VND')
                    ->sortable(),
                TextColumn::make('four_hour_shift_fee')
                    ->label('Ca 4h')
                    ->money('VND')
                    ->sortable(),
                TextColumn::make('eight_hour_shift_fee')
                    ->label('Ca 8h')
                    ->money('VND')
                    ->sortable(),
                TextColumn::make('after_hours_fee')
                    ->label('Sau 16h30 (% theo đơn giá)')
                    ->alignCenter()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Đang áp dụng')
                    ->alignCenter()
                    ->boolean()
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
