<?php

namespace App\Filament\Resources\Registrations\Schemas;

use App\Filament\Resources\Registrations\Actions\ImportGuestsAction;
use App\Models\Area;
use App\Models\Company;
use App\Models\Fee;
use Carbon\Carbon;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class RegistrationForm
{
    public static function configure(Schema $schema, string $type = 'working'): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->tabs([
                        self::formWizardStepRegistration($type),
                        self::formWizardStepInfoPeople(),
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
                Forms\Components\Hidden::make('user_id')
                    ->default(fn () => Auth::id()),
                Forms\Components\Hidden::make('approver_id')
                    ->default(fn () => Auth::user()?->approver?->id),
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
                // Toggle::make('wants_invoice')
                //     ->label('Có xuất hoá đơn hay không?')
                //     ->onIcon('heroicon-o-check')
                //     ->offIcon('heroicon-o-x-mark')
                //     ->onColor('success')
                //     ->inline(true)
                //     ->columnSpanFull()
                //     ->live(),
                // Select::make('company_id')
                //     ->label('Chọn công ty')
                //     ->relationship(name: 'company', titleAttribute: 'name')
                //     ->searchable()
                //     ->native(false)
                //     ->preload()
                //     ->visible(fn (Get $get): bool => $get('wants_invoice') === true)
                //     ->required(fn (Get $get): bool => $get('wants_invoice') === true)
                //     ->rules([
                //         fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                //             // When user wants an invoice, company must be selected/created.
                //             if ($get('wants_invoice') === true && blank($value)) {
                //                 $fail('Vui lòng chọn hoặc tạo công ty để xuất hoá đơn.');
                //             }

                //             // Defensive: when user doesn't want invoice, company should not be set.
                //             if ($get('wants_invoice') !== true && filled($value)) {
                //                 $fail('Không thể chọn công ty khi không xuất hoá đơn.');
                //             }
                //         },
                //     ])
                //     ->createOptionForm([
                //         TextInput::make('name')
                //             ->label('Tên công ty')
                //             ->required(),
                //         TextInput::make('tax_code')
                //             ->label('Mã số thuế')
                //             ->required(),
                //         TextInput::make('email')
                //             ->label('Email')
                //             ->email()
                //             ->required(),
                //         TextInput::make('phone')
                //             ->label('Số điện thoại'),
                //         TextInput::make('address')
                //             ->label('Địa chỉ'),
                //     ])
                //     ->editOptionForm([
                //         TextInput::make('name')
                //             ->label('Tên công ty')
                //             ->required(),
                //         TextInput::make('tax_code')
                //             ->label('Mã số thuế')
                //             ->required(),
                //         TextInput::make('email')
                //             ->label('Email')
                //             ->email()
                //             ->required(),
                //         TextInput::make('phone')
                //             ->label('Số điện thoại'),
                //         TextInput::make('address')
                //             ->label('Địa chỉ'),
                //     ])
                // ->helperText(function (Get $get): ?string {
                //     $company = Company::query()
                //         ->select(['id', 'name', 'tax_code', 'email', 'phone', 'address'])
                //         ->find($get('company_id'));

                //     if (! $company) {
                //         return 'Chọn hoặc tạo công ty để xuất hoá đơn.';
                //     }

                //     $lines = [];
                //     $lines[] = 'Tên: '.$company->name;

                //     if (filled($company->tax_code)) {
                //         $lines[] = ' | Mã số thuế: '.$company->tax_code;
                //     }

                //     return implode("\n", $lines);
                // })
                // ->columnSpanFull(),
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

    protected static function formWizardStepInfoPeople()
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
                            ->width('150px'),
                        TableColumn::make('Khu vực')
                            ->width('250px'),
                        // TableColumn::make('Loại phương tiện')
                        //     ->markAsRequired()
                        //     ->width('250px'),
                        TableColumn::make('Ghi chú')
                            ->width('150px'),
                    ])
                    ->relationship('guests')
                    ->label('Khách')
                    ->compact()
                    ->afterLabel(fn () => ImportGuestsAction::make())
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
                        // Select::make('fee_id')
                        //     ->label('Loại phương tiện')
                        //     ->options(Fee::all()->pluck('vehicle_type', 'id')->toArray())
                        //     ->searchable()
                        //     ->preload(),
                        TextInput::make('note'),
                    ])
                    ->defaultItems(1)
                    ->columns(6),
            ]);
    }
}
