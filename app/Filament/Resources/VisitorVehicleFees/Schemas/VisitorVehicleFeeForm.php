<?php

namespace App\Filament\Resources\VisitorVehicleFees\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;

class VisitorVehicleFeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin chung')
                    ->schema([
                        TextInput::make('vehicle_type')
                            ->label('Loại phương tiện')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2)
                            ->placeholder('VD: Xe máy, Ô tô dưới 9 chỗ, Xe tải...'),

                        TextInput::make('per_visit_fee')
                            ->label('Phí theo lượt (VNĐ)')
                            ->required()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->numeric()
                            ->default(0)
                            ->columnSpan(1)
                            ->prefix('VNĐ'),

                        TextInput::make('monthly_fee')
                            ->label('Phí theo tháng (VNĐ)')
                            ->required()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->numeric()
                            ->default(0)
                            ->columnSpan(1)
                            ->prefix('VNĐ'),

                        Toggle::make('is_active')
                            ->label('Đang áp dụng')
                            ->default(true)
                            ->columnSpan(2),

                        Textarea::make('notes')
                            ->label('Ghi chú')
                            ->rows(3)
                            ->columnSpan(2),
                    ])->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
