<?php

namespace App\Filament\Resources\RegisterDirectlyResource\Actions;

use App\Models\Card;
use App\Models\RegisterDirectly;
use Carbon\Carbon;
use Closure;
use Filament\Actions\StaticAction;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GiveCardAction
{
    public static function make(): Action
    {
        return Action::make('give-card')
            ->label('Vào')
            ->button()

            ->hidden(fn (RegisterDirectly $record) => $record->status === 'coming_in' || $record->status === 'came_out')
            ->icon('heroicon-o-inbox-arrow-down')
            ->modalHeading(function (RegisterDirectly $record) {
                if ($record->type === 'vehicle') {
                    return 'Xe: '.$record->bks;
                } else {
                    return 'Khách: '.$record->name.' | CMND: '.$record->papers;
                }
            })
            ->form([
                Fieldset::make('Lựa chọn')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Họ và tên')
                            ->default(fn (RegisterDirectly $record) => $record->name)
                            ->disabled(),
                        Forms\Components\TextInput::make('bks')
                            ->label('Biển số xe')
                            ->default(fn (RegisterDirectly $record) => $record->bks)
                            ->disabled(),
                        Forms\Components\TextInput::make('fee_id')
                            ->label('Trọng tải')
                            ->default(fn (RegisterDirectly $record) => $record?->fee?->ticket_code)
                            ->hidden(fn (RegisterDirectly $record) => $record->type !== 'vehicle')
                            ->disabled()

                            ->columnSpanFull(),
                        Forms\Components\Select::make('card_ids')
                            ->label('Thẻ')
                            ->multiple()
                            ->options(Card::where('status', 'inactive')
                                ->where(function ($q) {
                                    $q->whereNull('expiry_date')
                                        ->orWhere('expiry_date', '>=', now());
                                })
                                ->get()
                                ->pluck('card_name', 'id'))
                            ->searchable(['card_name', 'card_number'])
                            ->preload(),
                        Forms\Components\DateTimePicker::make('start_date')
                            ->label('Giờ vào')
                            ->placeholder('Chọn ngày, giờ vào')
                            ->native(false)
                            ->prefixIcon('heroicon-o-calendar')
                            ->default(now())
                            ->readOnly()
                            ->required(),
                        // Forms\Components\DateTimePicker::make('end_date')
                        //     ->label('Giờ ra dự kiến')
                        //     ->default(fn(RegisterDirectly $record) => $record->end_date)
                        //     ->required()
                        //     ->readOnly()
                        //     ->rules([
                        //         fn(Get $get, ?Model $record): Closure => function (string $attribute, $value, Closure $fail) use ($get, $record) {
                        //             if ($record['status'] != 'sent') {
                        //                 if (Carbon::parse($value, 'Asia/Ho_Chi_Minh')->isBefore(Carbon::parse($get('start_date'), 'Asia/Ho_Chi_Minh'))) {
                        //                     $fail('Ngày, giờ kết thúc phải lớn hơn ngày, giờ bắt đầu.');
                        //                 }
                        //             }
                        //         },
                        //         fn(Get $get, ?Model $record): Closure => function (string $attribute, $value, Closure $fail) use ($get, $record) {
                        //             if ($record['status'] != 'sent') {
                        //                 if (Carbon::parse($value, 'Asia/Ho_Chi_Minh')->lessThanOrEqualTo(Carbon::now('Asia/Ho_Chi_Minh'))) {
                        //                     $fail('Ngày, giờ kết thúc phải lớn hơn ngày, giờ hiện tại.');
                        //                 }
                        //             }
                        //         }
                        //     ]),
                    ])->columns(2),
            ])
            ->action(function (array $data, RegisterDirectly $record): void {
                // try {
                DB::transaction(function () use ($data, $record) {
                    // Update record status and actual_date_in
                    $record->status = 'coming_in';
                    $record->actual_date_in = $data['start_date'];

                    // Assign selected cards
                    $cardIds = $data['card_ids'] ?? [];
                    $record->cards()->sync($cardIds);
                    Card::whereIn('id', $cardIds)->update(['status' => 'active']);

                    // Update registration vehicle status (guard and update safely)
                    if ($record->relationLoaded('registrationVehicle') || $record->registrationVehicle) {
                        $registrationVehicle = $record->registrationVehicle;
                        if ($registrationVehicle instanceof Model) {
                            $registrationVehicle->status = 'entering';
                            $registrationVehicle->save();
                        }
                    }
                    // Save record
                    $record->save();
                });

                Notification::make()
                    ->title('Thành công')
                    ->body('Đã chuyển trạng thái cho khách vào')
                    ->success()
                    ->send();
                // } catch (\Exception $e) {
                //     Notification::make()
                //         ->title('Thất bại')
                //         ->body('Lỗi: ' . $e->getMessage())
                //         ->danger()
                //         ->send();
                // }
            })
            ->modalCancelAction(fn (StaticAction $action) => $action
                ->label('Hủy')
                ->extraAttributes(['class' => 'ml-auto'])
            )
            ->modalSubmitAction(fn (StaticAction $action) => $action
                ->label('Vào')
            );
    }
}
