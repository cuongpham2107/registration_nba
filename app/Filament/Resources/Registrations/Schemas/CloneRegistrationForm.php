<?php

namespace App\Filament\Resources\Registrations\Schemas;

use App\Filament\Resources\Registrations\Actions\ImportGuestsAction;
use App\Models\Area;
use Carbon\Carbon;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class CloneRegistrationForm
{
    public static function configure(Schema $schema, string $type = 'working'): Schema
    {
        return $schema
            ->components([
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
                Forms\Components\Hidden::make('approver_id'),
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
                    ->native(true)
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
                    ->native(true)
                    ->label('Giờ ra dự kiến')
                    ->required()
                    ->rules([
                        fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                            if (Carbon::parse($value, 'Asia/Ho_Chi_Minh')->isBefore(Carbon::parse($get('start_date'), 'Asia/Ho_Chi_Minh'))) {
                                $fail('Ngày, giờ kết thúc phải lớn hơn ngày, giờ bắt đầu.');
                            }
                        },
                        fn (): Closure => function (string $attribute, $value, Closure $fail) {
                            if (Carbon::parse($value, 'Asia/Ho_Chi_Minh')->lessThanOrEqualTo(Carbon::now('Asia/Ho_Chi_Minh'))) {
                                $fail('Ngày, giờ kết thúc phải lớn hơn ngày, giờ hiện tại.');
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
                Repeater::make('guests')
                    ->columnSpanFull()
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
                        TableColumn::make('Ghi chú')
                            ->width('150px'),
                    ])
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
                        TextInput::make('note'),
                    ])
                    ->defaultItems(1)
                    ->columns(6),
            ])
            ->columns([
                'sm' => 1,
                'md' => 2,
                'lg' => 6,
            ]);
    }
}
