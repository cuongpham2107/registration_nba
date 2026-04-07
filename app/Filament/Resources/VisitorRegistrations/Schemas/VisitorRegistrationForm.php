<?php

namespace App\Filament\Resources\VisitorRegistrations\Schemas;

use App\Filament\Resources\VisitorRegistrations\Actions\ImportCustomersAction;
use App\Models\Area;
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

class VisitorRegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->tabs([
                        self::formWizardStepRegistration(),
                        self::formWizardStepInfoCustomer(),
                    ])->columnSpanFull(),
            ]);
    }

    protected static function formWizardStepRegistration()
    {
        return Tab::make('Đăng ký khách')
            ->schema([
                TextInput::make('name')
                    ->label('Đơn vị khách')
                    ->required()
                    ->columnSpan([
                        'sm' => 1,
                        'md' => 2,
                        'lg' => 6,
                    ]),
                // TextInput::make('bks')
                //     ->prefixIcon('heroicon-o-truck')
                //     ->label('BKS ô tô')
                //     ->columnSpan([
                //         'sm' => 1,
                //         'md' => 2,
                //         'lg' => 3,
                //     ]),
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

    protected static function formWizardStepInfoCustomer()
    {
        return Tab::make('Thông tin khách')
            ->schema([
                Repeater::make('customers')
                    ->table([
                        TableColumn::make('Tên khách')
                            ->width('200px'),
                        TableColumn::make('Số giấy tờ')
                            ->width('150px'),
                        TableColumn::make('Loại giấy tờ')
                            ->width('150px'),
                        TableColumn::make('Biển số')
                            ->width('150px'),
                        TableColumn::make('Khu vực')
                            ->width('250px'),
                        TableColumn::make('Loại phương tiện')
                            ->width('250px'),
                        TableColumn::make('Ghi chú')
                            ->width('150px'),
                    ])
                    ->relationship()
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
                        Select::make('vehicle_type')
                            ->options([
                                '1' => 'Xe đạp, Xe đạp điện, Xe máy, Xe máy điện',
                                '2' => 'Ô tô đến 9 chỗ, xe tải đến 1.5 tấn, xe 3 bánh và xe bán tải',
                                '3' => 'Xe ô tô 10-16 chỗ, xe tải lớn hơn 1.5 tấn đến 3.5 tấn',
                                '4' => 'Xe ô tô từ 17-27 chỗ, xe tải lớn hơn 3.5 tấn đến 7 tấn',
                                '5' => 'Xe ô tô từ 30 chỗ trở lên, xe tải trên 7 tấn, xe container, xe kéo rơ moóc',
                            ])
                            ->searchable()
                            ->preload(),
                        TextInput::make('note'),
                    ])
                    ->defaultItems(1)
                    ->columns(6),
                // ->extraActions([
                //     ImportCustomersAction::make(),
                // ]),
            ]);
    }
}
