<?php

namespace App\Filament\Resources\Registrations\Schemas;

use App\Models\Area;
use App\Models\GatheringPointFee;
use App\Models\VisitorVehicleFee;
use Carbon\Carbon;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class RegistrationForm
{
    public static function configure(Schema $schema, string $type = 'working'): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->tabs([
                        self::formWizardStepRegistration($type),
                        $type === 'inspection' ? self::formWizardStepInfoGuestInspection() : self::formWizardStepInfoGuestWorking(),
                    ])->columnSpanFull(),
            ]);
    }

    protected static function formWizardStepRegistration(string $type)
    {
        return Tab::make('Đăng ký khách')
            ->icon('heroicon-o-information-circle')
            ->schema([
                TextInput::make('name')
                    ->label('Đơn vị khách')
                    ->required()
                    ->columnSpan([
                        'sm' => 1,
                        'md' => 2,
                        'lg' => 6,
                    ]),
                Forms\Components\Hidden::make('type')
                    ->default($type),
                Forms\Components\Textarea::make('purpose')
                    ->label('Mục đích')
                    ->required()
                    ->columnSpan([
                        'sm' => 1,
                        'md' => 2,
                        'lg' => 6,
                    ]),
                Forms\Components\DateTimePicker::make('start_date')
                    ->displayFormat('d/m/Y h:i')
                    ->locale('vi')
                    ->seconds(false)
                    ->native(false)
                    ->label('Giờ vào dự kiến')
                    ->required()
                    ->columnSpan([
                        'sm' => 1,
                        'md' => 1,
                        'lg' => 3,
                    ]),
                Forms\Components\DateTimePicker::make('end_date')
                    ->displayFormat('d/m/Y h:i')
                    ->locale('vi')
                    ->seconds(false)
                    ->native(false)
                    ->label('Giờ ra dự kiến')
                    ->required()
                    ->rules([
                        fn (Get $get, ?Model $record): Closure => function (string $attribute, $value, Closure $fail) use ($get, $record) {
                            if (($record['status'] ?? null) != 'sent') {
                                if (Carbon::parse($value, 'Asia/Ho_Chi_Minh')->isBefore(Carbon::parse($get('start_date'), 'Asia/Ho_Chi_Minh'))) {
                                    $fail('Ngày, giờ kết thúc phải lớn hơn ngày, giờ bắt đầu.');
                                }
                            }

                        },
                        fn (Get $get, ?Model $record): Closure => function (string $attribute, $value, Closure $fail) use ($record) {
                            if (($record['status'] ?? null) != 'sent') {
                                if (Carbon::parse($value, 'Asia/Ho_Chi_Minh')->lessThanOrEqualTo(Carbon::now('Asia/Ho_Chi_Minh'))) {
                                    $fail('Ngày, giờ kết thúc phải lớn hơn ngày, giờ hiện tại.');
                                }
                            }

                        },
                    ])
                    ->columnSpan([
                        'sm' => 1,
                        'md' => 1,
                        'lg' => 3,
                    ]),
                Forms\Components\Textarea::make('asset')
                    ->label('Tài sản')
                    ->rows(2)
                    ->columnSpan([
                        'sm' => 1,
                        'md' => 1,
                        'lg' => 3,
                    ]),
                Forms\Components\Textarea::make('note')
                    ->label('Ghi chú')
                    ->rows(2)
                    ->columnSpan([
                        'sm' => 1,
                        'md' => 1,
                        'lg' => 3,
                    ]),
            ])->columns([
                'sm' => 1,
                'md' => 2,
                'lg' => 6,
            ]);
    }

    protected static function formWizardStepInfoGuestInspection()
    {
        return Tab::make('Thông tin khách')
            ->icon('heroicon-o-users')
            ->schema([
                Repeater::make('guests')
                    ->table([
                        TableColumn::make('Tên khách')
                            ->markAsRequired()
                            ->width('200px'),
                        TableColumn::make('Số giấy tờ')
                            ->markAsRequired()
                            ->width('150px'),
                        TableColumn::make('Loại giấy tờ')
                            ->markAsRequired()
                            ->width('150px'),
                        TableColumn::make('Biển số')
                            ->markAsRequired()
                            ->width('150px'),
                        TableColumn::make('Khu vực')
                            ->width('250px'),
                        TableColumn::make('Loại phương tiện')
                            ->markAsRequired()
                            ->width('250px'),
                        TableColumn::make('Ghi chú')
                            ->width('150px'),
                    ])
                    ->relationship('guests')
                    ->label('Khách')
                    ->compact()
                    ->schema([
                        TextInput::make('name')
                            ->required(),
                        TextInput::make('papers')
                            ->required(),
                        TextInput::make('type')
                            ->required(),
                        TextInput::make('license_plate'),
                        Select::make('areas')
                            ->multiple()
                            ->options(Area::all()->pluck('name', 'code'))
                            ->searchable()
                            ->preload(),
                        Select::make('gathering_point_fee_id')
                            ->label('Loại phương tiện')
                            ->options(GatheringPointFee::all()->pluck('vehicle_type', 'id')->toArray()
                            )
                            ->searchable()
                            ->preload(),
                        TextInput::make('note'),
                    ])
                    ->defaultItems(1)
                    ->columns(6),
            ]);
    }

    protected static function formWizardStepInfoGuestWorking()
    {
        return Tab::make('Thông tin khách')
            ->icon('heroicon-o-users')
            ->schema([
                Repeater::make('guests')
                    ->table([
                        TableColumn::make('Tên khách')
                            ->markAsRequired()
                            ->width('200px'),
                        TableColumn::make('Số giấy tờ')
                            ->markAsRequired()
                            ->width('150px'),
                        TableColumn::make('Loại giấy tờ')
                            ->markAsRequired()
                            ->width('150px'),
                        TableColumn::make('Biển số')
                            ->markAsRequired()
                            ->width('150px'),
                        TableColumn::make('Khu vực')
                            ->width('250px'),
                        TableColumn::make('Loại phương tiện')
                            ->markAsRequired()
                            ->width('250px'),
                        TableColumn::make('Ghi chú')
                            ->width('150px'),
                    ])
                    ->relationship('guests')
                    ->label('Khách')
                    ->compact()
                    ->schema([
                        TextInput::make('name')
                            ->required(),
                        TextInput::make('papers')
                            ->required(),
                        TextInput::make('type')
                            ->required(),
                        TextInput::make('license_plate'),
                        Select::make('areas')
                            ->multiple()
                            ->options(Area::all()->pluck('name', 'code'))
                            ->searchable()
                            ->preload(),
                        // Working: không dùng GatheringPointFee, chỉ hiển thị select phí phương tiện của khách làm việc.
                        Select::make('visitor_vehicle_fee_id')
                            ->label('Loại phương tiện')
                            ->options(VisitorVehicleFee::all()->pluck('vehicle_type', 'id'))
                            ->searchable()
                            ->preload(),
                        TextInput::make('note'),
                    ])
                    ->defaultItems(1)
                    ->columns(6),
            ]);
    }
}
