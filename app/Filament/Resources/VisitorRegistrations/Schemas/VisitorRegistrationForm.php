<?php

namespace App\Filament\Resources\VisitorRegistrations\Schemas;

use App\Filament\Resources\VisitorRegistrations\Actions\ImportCustomersAction;
use App\Models\Area;
use Carbon\Carbon;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
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
                Forms\Components\TextInput::make('name')
                    ->label('Đơn vị khách')
                    ->required()
                    ->columnSpan([
                        'sm' => 1,
                        'md' => 2,
                        'lg' => 3,
                    ]),
                Forms\Components\TextInput::make('bks')
                    ->prefixIcon('heroicon-o-truck')
                    ->label('BKS ô tô')
                    ->columnSpan([
                        'sm' => 1,
                        'md' => 2,
                        'lg' => 3,
                    ]),
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

                    ])
                    ->relationship()
                    ->label('')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Tên khách')
                            ->required(),
                        Forms\Components\TextInput::make('papers')
                            ->label('Số giấy tờ')
                            ->required(),
                        Forms\Components\TextInput::make('type')
                            ->label('Loại giấy tờ')
                            ->required(),
                        Forms\Components\Select::make('areas')
                            ->label('Khu vực')
                            ->multiple()
                            ->options(Area::all()->pluck('name', 'code'))
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('license_plate')
                            ->label('Biển số'),
                        Forms\Components\TextInput::make('note')
                            ->label('Ghi chú'),
                    ])
                    ->defaultItems(1)
                    ->columns(6),
                // ->extraActions([
                //     ImportCustomersAction::make(),
                // ]),
            ]);
    }
}
