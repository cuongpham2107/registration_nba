<?php

namespace App\Filament\Resources\RegisterDirectlyResource\Actions;

use App\Models\Card;
use App\Models\RegisterDirectly;
use Carbon\Carbon;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;

class GiveCardAction
{
    public static function make(): Action
    {
        return Action::make('give-card')
            ->label('Vào')
            ->button()
            ->hidden(fn(RegisterDirectly $record) => $record->status === 'coming_in' || $record->status === 'came_out')
            ->icon('heroicon-o-inbox-arrow-down')
            ->form([
                Fieldset::make('Lựa chọn')
                    ->schema([
                        Forms\Components\Select::make('id')
                            ->label('Thẻ')
                            ->options(Card::where('status', 'inactive')->get()->pluck('card_name', 'id'))
                            ->required()
                            ->searchable(['card_name', 'card_number'])
                            ->preload(),
                        Forms\Components\DateTimePicker::make('start_date')
                            ->label('Giờ vào')
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
                    ])->columns(2)
            ])
            ->action(function (array $data, RegisterDirectly $record): void {
                try {
                    $record->status = 'coming_in';
                    $record->actual_date_in =  $data['start_date'];
                    $card = Card::where('id', $data['id'])->first();
                    $card->status = 'active';
                    $card->save();
                    $record->card_id = $card->id;
                    $record->save();

                    Notification::make()
                        ->title('Thành công')
                        ->body('Đã chuyển trạng thái cho khách vào')
                        ->success();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Thất bại')
                        ->body('Lỗi: ' . $e->getMessage())
                        ->warning();
                }
            });
    }
}
