<?php

namespace App\Filament\Resources\LiftingServiceFees\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;

class LiftingServiceFeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin dịch vụ')
                    ->schema([
                        TextInput::make('service_name')
                            ->label('Tên dịch vụ')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('VD: Nâng hạ container, Nâng hạ hàng hóa...'),

                        Select::make('weight_category')
                            ->label('Phân loại trọng tải')
                            ->options([
                                'under_2_tons' => 'Dưới 2 tấn',
                                'over_2_tons' => 'Trên 2 tấn',
                            ])
                            ->required()
                            ->default('under_2_tons')
                            ->live(),
                    ])->columnSpanFull(),

                Section::make('Biểu phí')
                    ->description('Giá áp dụng cho dịch vụ nâng hạ')
                    ->schema([
                        TextInput::make('regular_hours_fee')
                            ->label('Giá giờ hành chính 7h30 - 16h30 (VNĐ)')
                            ->required()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->numeric()
                            ->default(0)
                            ->prefix('VNĐ'),

                        TextInput::make('eight_hour_shift_fee')
                            ->label('Giá ca 8h (VNĐ)')
                            ->required()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->numeric()
                            ->default(0)
                            ->prefix('VNĐ')
                            ->helperText('Áp dụng cho cả dưới 2 tấn và trên 2 tấn'),

                        TextInput::make('four_hour_shift_fee')
                            ->label('Giá ca 4h (VNĐ)')
                            ->numeric()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->default(0)
                            ->prefix('VNĐ')
                            ->helperText('Chỉ áp dụng cho trên 2 tấn'),

                        TextInput::make('after_hours_fee')
                            ->label('% giá sau 16h30')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->prefix('%'),
                    ])->columnSpanFull(),

                Section::make('Trạng thái')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Đang áp dụng')
                            ->default(true),

                        Textarea::make('notes')
                            ->label('Ghi chú')
                            ->rows(3),
                    ])->columnSpanFull(),
            ]);
    }
}
