<?php

namespace App\Filament\Resources\RegistrationEntries\Schemas;

use App\Models\Area;
use Carbon\Carbon;
use Closure;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RegistrationEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin cơ bản')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Họ và tên')
                            ->required(),
                        Forms\Components\TextInput::make('papers')
                            ->label('Số CCCD')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('bks')
                            ->label('Biển kiểm soát')
                            ->prefixIcon('heroicon-o-truck')
                            ->formatStateUsing(fn (?string $state) => $state ? strtoupper(str_replace(' ', '', $state)) : '')
                            ->required(),
                        Forms\Components\TextInput::make('address')
                            ->label('Địa chỉ')
                            ->hidden(fn ($state) => $state === null)
                            ->prefixIcon('heroicon-o-map-pin'),
                        Forms\Components\TextInput::make('contact_person')
                            ->hidden(fn ($state) => $state === null)
                            ->label('Người liên hệ'),
                        Forms\Components\Toggle::make('is_priority')
                            ->label('Ưu tiên')
                            ->helperText('Đánh dấu nếu đây là đơn đăng ký ưu tiên')
                            ->onIcon('heroicon-s-arrow-up')
                            ->offIcon('heroicon-s-arrow-down')
                            ->inline(false),
                        Forms\Components\Textarea::make('job')
                            ->label('Mục đích công việc')
                            ->formatStateUsing(fn (?string $state) => $state ? implode("\n", [
                                'Loại phương tiện: '.(explode('|', $state)[0] ?? ''),
                                'Dịch vụ: '.(explode('|', $state)[1] ?? ''),
                            ]) : '')
                            ->rows(5),
                    ])->columnSpan(1),
                Section::make('Thông tin thẻ')
                    ->schema([
                        Forms\Components\Select::make('card_id')
                            ->label('Thẻ')
                            ->columnSpanFull()
                            ->relationship(
                                name: 'card',
                                titleAttribute: 'card_name',
                                modifyQueryUsing: fn (Builder $query) => $query->where('status', 'inactive')
                            )
                            ->searchable(['card_name', 'card_number'])
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('account_id')
                                    ->label('Mã tài khoản')
                                    ->required(),
                                Forms\Components\TextInput::make('card_number')
                                    ->label('Số thẻ')
                                    ->numeric()
                                    ->required(),
                                Forms\Components\TextInput::make('card_name')
                                    ->label('Tên thẻ')
                                    ->required(),
                                Forms\Components\Select::make('status')
                                    ->label('Trạng thái')
                                    ->options([
                                        'active' => 'Đang sử dụng',
                                        'inactive' => 'Chưa sử dụng',
                                        'blocked' => 'Bị khóa',
                                    ])
                                    ->default('inactive')
                                    ->required(),
                            ]),
                        Forms\Components\Select::make('areas')
                            ->label('Khu vực')
                            ->multiple()
                            ->options(Area::all()->pluck('name', 'code'))
                            ->preload()
                            ->columnSpanFull(),
                        Forms\Components\DateTimePicker::make('start_date')
                            ->displayFormat('d/m/Y h:i')
                            ->seconds(false)
                            ->label('Giờ vào')
                            ->required(),
                        Forms\Components\DateTimePicker::make('end_date')
                            ->displayFormat('d/m/Y h:i')
                            ->seconds(false)
                            ->label('Giờ ra dự kiến')
                            ->rules([
                                fn (Get $get, ?Model $record): Closure => function (string $attribute, $value, Closure $fail) use ($get, $record) {
                                    if ($record['status'] != 'sent') {
                                        if (Carbon::parse($value, 'Asia/Ho_Chi_Minh')->isBefore(Carbon::parse($get('start_date'), 'Asia/Ho_Chi_Minh'))) {
                                            $fail('Ngày, giờ kết thúc phải lớn hơn ngày, giờ bắt đầu.');
                                        }
                                    }
                                },
                                fn (Get $get, ?Model $record): Closure => function (string $attribute, $value, Closure $fail) use ($record) {
                                    if ($record['status'] != 'sent') {
                                        if (Carbon::parse($value, 'Asia/Ho_Chi_Minh')->lessThanOrEqualTo(Carbon::now('Asia/Ho_Chi_Minh'))) {
                                            $fail('Ngày, giờ kết thúc phải lớn hơn ngày, giờ hiện tại.');
                                        }
                                    }
                                },
                            ]),
                        Forms\Components\DateTimePicker::make('actual_date_in')
                            ->label('Giờ vào thực tế')
                            ->displayFormat('d/m/Y H:i A')
                            ->prefixIcon('heroicon-s-calendar-days')
                            ->seconds(false)
                            ->readonly(),
                        Forms\Components\DateTimePicker::make('actual_date_out')
                            ->label('Giờ ra thực tế')
                            ->displayFormat('d/m/Y H:i A')
                            ->prefixIcon('heroicon-s-calendar-days')
                            ->seconds(false)
                            ->readonly(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'none' => 'Chưa vào',
                                'coming_in' => 'Đang vào',
                                'came_out' => 'Đã ra',
                            ])
                            ->default('none')
                            ->label('Trạng thái')
                            ->columnSpanFull(),
                    ])->columnSpan(1)->columns(2),
            ])->columns(2);
    }
}
