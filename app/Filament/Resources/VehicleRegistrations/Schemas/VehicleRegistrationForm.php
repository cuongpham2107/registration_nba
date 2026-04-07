<?php

namespace App\Filament\Resources\VehicleRegistrations\Schemas;

use App\Models\Company;
use App\Models\GatheringPointFee;
use App\Models\LiftingServiceFee;
use Closure;
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
                    'md' => 6,
                ])
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('driver_name')
                            ->label('Tên tài xế')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(3),
                        TextInput::make('driver_phone')
                            ->label('Số điện thoại')
                            ->required()
                            ->maxLength(20)
                            ->columnSpan(3),
                        TextInput::make('driver_id_card')
                            ->label('Số CMND/CCCD')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(3),
                        TextInput::make('vehicle_number')
                            ->label('Biển số xe')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(3),
                        DateTimePicker::make('expected_in_at')
                            ->label('Thời gian vào dự kiến')
                            ->seconds(false)
                            ->required()
                            ->columnSpanFull(),
                        Select::make('gathering_point_fee_id')
                            ->label('Loại xe, trọng tải (Biểu phí địa điểm tập trung)')
                            ->options(GatheringPointFee::where('is_active', true)->pluck('vehicle_type', 'id'))
                            ->required()
                            ->columnSpanFull(),
                        Toggle::make('has_lifting_service')
                            ->label('Có sử dụng dịch vụ nâng hạ không')
                            ->onIcon('heroicon-o-check')
                            ->offIcon('heroicon-o-x-mark')
                            ->onColor('primary')
                            ->inline(false)
                            ->live()
                            ->columnSpanFull(),
                        Select::make('lifting_service_fee_id')
                            ->label('Loại dịch vụ nâng hạ')
                            ->options(LiftingServiceFee::where('is_active', true)->pluck('service_name', 'id'))
                            ->visible(fn (Get $get): bool => $get('has_lifting_service') === true)
                            ->native(false)
                            ->required(fn (Get $get): bool => $get('has_lifting_service') === true)
                            ->live()
                            ->columnSpan(function (Get $get): int {
                                $liftingServiceFee = LiftingServiceFee::query()
                                    ->select(['id', 'weight_category'])
                                    ->find($get('lifting_service_fee_id'));

                                if ($liftingServiceFee?->weight_category === 'under_2_tons') {
                                    return 4;
                                }

                                return 6;
                            }),
                        TextInput::make('count_package')
                            ->label('Số kiện')
                            ->required()
                            ->validationMessages([
                                'required' => 'Số kiện không được để trống.',
                            ])
                            ->extraAttributes(['class' => '!bg-gray-100'])
                            ->maxLength(255)
                            ->visible(function (Get $get): bool {
                                $liftingServiceFee = LiftingServiceFee::query()
                                    ->select(['id', 'weight_category'])
                                    ->find($get('lifting_service_fee_id'));

                                return $liftingServiceFee?->weight_category === 'under_2_tons';
                            })
                            ->columnSpan(2),

                        Toggle::make('wants_invoice')
                            ->label('Có xuất hoá đơn hay không?')
                            ->onIcon('heroicon-o-check')
                            ->offIcon('heroicon-o-x-mark')
                            ->onColor('success')
                            ->inline(true)
                            ->columnSpanFull()
                            ->live(),

                        Select::make('company_id')
                            ->label('Chọn công ty')
                            ->relationship(name: 'company', titleAttribute: 'name')
                            ->searchable()
                            ->native(false)
                            ->preload()
                            ->visible(fn (Get $get): bool => $get('wants_invoice') === true)
                            ->required(fn (Get $get): bool => $get('wants_invoice') === true)
                            ->rules([
                                fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                    // When user wants an invoice, company must be selected/created.
                                    if ($get('wants_invoice') === true && blank($value)) {
                                        $fail('Vui lòng chọn hoặc tạo công ty để xuất hoá đơn.');
                                    }

                                    // Defensive: when user doesn't want invoice, company should not be set.
                                    if ($get('wants_invoice') !== true && filled($value)) {
                                        $fail('Không thể chọn công ty khi không xuất hoá đơn.');
                                    }
                                },
                            ])
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('Tên công ty')
                                    ->required(),
                                TextInput::make('tax_code')
                                    ->label('Mã số thuế'),
                                TextInput::make('email')
                                    ->label('Email')
                                    ->email(),
                                TextInput::make('phone')
                                    ->label('Số điện thoại'),
                                TextInput::make('address')
                                    ->label('Địa chỉ'),
                            ])
                            ->editOptionForm([
                                TextInput::make('name')
                                    ->label('Tên công ty')
                                    ->required(),
                                TextInput::make('tax_code')
                                    ->label('Mã số thuế'),
                                TextInput::make('email')
                                    ->label('Email')
                                    ->email(),
                                TextInput::make('phone')
                                    ->label('Số điện thoại'),
                                TextInput::make('address')
                                    ->label('Địa chỉ'),
                            ])
                            ->helperText(function (Get $get): ?string {
                                $company = Company::query()
                                    ->select(['id', 'name', 'tax_code', 'email', 'phone', 'address'])
                                    ->find($get('company_id'));

                                if (! $company) {
                                    return 'Chọn hoặc tạo công ty để xuất hoá đơn.';
                                }

                                $lines = [];
                                $lines[] = 'Tên: '.$company->name;

                                if (filled($company->tax_code)) {
                                    $lines[] = ' | Mã số thuế: '.$company->tax_code;
                                }

                                return implode("\n", $lines);
                            })
                            ->columnSpanFull(),
                        Toggle::make('is_priority')
                            ->label('Ưu tiên')
                            ->helperText('Đánh dấu nếu Đăng ký xe kiểm hoá này là ưu tiên')
                            ->onIcon('heroicon-o-arrow-up')
                            ->offIcon('heroicon-o-arrow-down')
                            ->inline(false)
                            ->columnSpanFull(),
                        Textarea::make('notes')
                            ->label('Ghi chú')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
