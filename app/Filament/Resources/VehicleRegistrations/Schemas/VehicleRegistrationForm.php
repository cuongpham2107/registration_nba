<?php

namespace App\Filament\Resources\VehicleRegistrations\Schemas;

use App\Models\GatheringPointFee;
use App\Models\LiftingServiceFee;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class VehicleRegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make([
                    'default' => 1,
                    'md' => 2,
                ])
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('driver_name')
                            ->label('Tên tài xế')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('driver_phone')
                            ->label('Số điện thoại')
                            ->required()
                            ->maxLength(20),
                        TextInput::make('driver_id_card')
                            ->label('Số CMND/CCCD')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('vehicle_number')
                            ->label('Biển số xe')
                            ->required()
                            ->maxLength(255),
                        Select::make('gathering_point_fee_id')
                            ->label('Loại xe, trọng tải (Biểu phí địa điểm tập trung)')
                            ->options(GatheringPointFee::where('is_active', true)->pluck('vehicle_type', 'id'))
                            ->required()
                            ->columnSpan(2),
                        Toggle::make('has_lifting_service')
                            ->label('Có sử dụng dịch vụ nâng hạ không')
                            ->onIcon('heroicon-o-check')
                            ->offIcon('heroicon-o-x-mark')
                            ->onColor('primary')
                            ->inline(false)
                            ->live(),
                        Select::make('lifting_service_fee_id')
                            ->label('Loại dịch vụ nâng hạ')
                            ->options(LiftingServiceFee::where('is_active', true)->pluck('service_name', 'id'))
                            ->visible(fn (Get $get): bool => $get('has_lifting_service') === true)
                            ->live(onBlur: true)
                            ->columnSpan(2),
                        Toggle::make('is_priority')
                            ->label('Ưu tiên')
                            ->helperText('Đánh dấu nếu Đăng ký xe kiểm hoá này là ưu tiên')
                            ->onIcon('heroicon-o-arrow-up')
                            ->offIcon('heroicon-o-arrow-down')
                            ->inline(false),
                        DateTimePicker::make('expected_in_at')
                            ->label('Thời gian vào dự kiến')
                            ->seconds(false)
                            ->required()
                            ->columnSpanFull(),
                        Textarea::make('notes')
                            ->label('Ghi chú')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
