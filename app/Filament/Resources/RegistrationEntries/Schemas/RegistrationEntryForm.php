<?php

namespace App\Filament\Resources\RegistrationEntries\Schemas;

use App\Models\Area;
use App\Models\Card;
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
                        Forms\Components\TextInput::make('license_plate')
                            ->label('Biển kiểm soát')
                            ->prefixIcon('heroicon-o-truck')
                            ->formatStateUsing(fn (?string $state) => $state ? strtoupper(str_replace(' ', '', $state)) : '')
                            ->required(),
                        Forms\Components\TextInput::make('address')
                            ->label('Địa chỉ')
                            ->hidden(fn ($state) => $state === null)
                            ->prefixIcon('heroicon-o-map-pin'),
                        Forms\Components\Select::make('type')
                            ->label('Loại ra vào')
                            ->options([
                                'inspection' => 'Đăng ký kiểm hoá',
                                'working' => 'Đăng ký khách ra vào',
                            ])
                            ->searchable(),
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
                                modifyQueryUsing: fn (Builder $query, Get $get): Builder => $query
                                    ->where(function (Builder $query) use ($get): Builder {
                                        $cardId = $get('card_id');

                                        return $query
                                            ->where('status', 'inactive')
                                            ->when(
                                                filled($cardId),
                                                fn (Builder $query) => $query->orWhere(function (Builder $query) use ($cardId) {
                                                    $query->whereKey($cardId);
                                                }),
                                            );
                                    }),
                            )
                            ->getOptionLabelFromRecordUsing(fn (Model $record): string => (string) ($record->card_name ?? $record->getKey()))
                            ->getOptionLabelUsing(function ($value): ?string {
                                if (blank($value)) {
                                    return null;
                                }

                                return Card::query()
                                    ->whereKey($value)
                                    ->value('card_name');
                            })
                            ->searchable(['card_name', 'card_number'])
                            ->preload(),
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
                                'entering' => 'Đang vào',
                                'exited' => 'Đã ra',
                            ])
                            ->default('none')
                            ->label('Trạng thái')
                            ->columnSpanFull(),
                    ])->columnSpan(1)->columns(2),
            ])->columns(2);
    }
}
