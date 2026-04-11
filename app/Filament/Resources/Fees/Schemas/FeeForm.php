<?php

namespace App\Filament\Resources\Fees\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;

class FeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin chung')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('vehicle_type')
                            ->label('Loại phương tiện')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('VD: Xe container, Xe tải...')
                            ->columnSpanFull(),
                    ]),

                Section::make('Biểu phí theo khung giờ')
                    ->description('Giá áp dụng cho từng khung giờ trong ngày')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('morning_fee')
                            ->label('Giá từ 7h - 12h (VNĐ)')
                            ->required()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->numeric()
                            ->default(0)
                            ->prefix('VNĐ'),

                        TextInput::make('afternoon_fee')
                            ->label('Giá từ 12h - 17h (VNĐ)')
                            ->required()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->numeric()
                            ->default(0)
                            ->prefix('VNĐ'),

                        TextInput::make('full_day_fee')
                            ->label('Giá cả ngày 7h - 17h (VNĐ)')
                            ->required()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->numeric()
                            ->default(0)
                            ->prefix('VNĐ'),

                        TextInput::make('night_fee')
                            ->label('Giá sau 17h - 7h sáng hôm sau (VNĐ)')
                            ->required()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->numeric()
                            ->default(0)
                            ->prefix('VNĐ'),
                    ]),

                Section::make('Trạng thái')
                    ->columnSpanFull()
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Đang áp dụng')
                            ->default(true)
                            ->columnSpanFull(),

                        Textarea::make('notes')
                            ->label('Ghi chú')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
