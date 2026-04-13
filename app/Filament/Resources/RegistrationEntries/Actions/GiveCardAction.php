<?php

namespace App\Filament\Resources\RegistrationEntries\Actions;

use App\Models\Card;
use App\Models\RegistrationEntry;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Fieldset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GiveCardAction
{
    public static function make(): Action
    {
        return Action::make('give-card')
            ->label('Vào')
            ->button()
            ->hidden(fn (RegistrationEntry $record) => $record->status !== 'none')
            ->icon('heroicon-o-inbox-arrow-down')
            ->modalHeading(function (RegistrationEntry $record) {
                if ($record->type === 'inspection') {
                    return 'Xe kiểm hoá: '.$record->license_plate;
                } else {
                    return 'Khách: '.$record->name.' | CMND: '.$record->papers;
                }
            })
            ->schema([
                Fieldset::make('Lựa chọn')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Họ và tên')
                            ->default(fn (RegistrationEntry $record) => $record->name)
                            ->disabled()
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('license_plate')
                            ->label('Biển số xe')
                            ->default(fn (RegistrationEntry $record) => $record->license_plate)
                            ->disabled()
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('guest.fee.vehicle_type')
                            ->label('Loại phương tiện')
                            ->default(fn (RegistrationEntry $record) => $record->guest?->fee?->vehicle_type)
                            ->disabled()
                            ->columnSpan(2),
                        Forms\Components\Select::make('id')
                            ->label('Thẻ')
                            ->options(fn () => Card::query()
                                ->where('status', 'inactive')
                                ->orderBy('card_name')
                                ->get()
                                ->mapWithKeys(fn (Card $card) => [
                                    $card->id => trim($card->card_name.' - '.$card->card_number),
                                ])
                                ->toArray())
                            ->searchable(['card_name', 'card_number'])
                            ->preload()
                            ->native(false)
                            ->columnSpan(3),
                        Forms\Components\DateTimePicker::make('start_date')
                            ->label('Giờ vào')
                            ->default(now())
                            ->displayFormat('d/m/Y H:i')
                            ->readOnly()
                            ->required()
                            ->native(false)
                            ->columnSpan(3),
                    ])->columns(6),
            ])
            ->action(function (array $data, RegistrationEntry $record): void {
                try {
                    DB::transaction(function () use ($data, $record) {
                        $record->status = 'entering';
                        $record->actual_date_in = $data['start_date'];

                        if (! empty($data['id'])) {
                            /** @var Card|null $card */
                            $card = Card::query()
                                ->whereKey($data['id'])
                                ->lockForUpdate()
                                ->first();

                            if (! $card) {
                                Notification::make()
                                    ->title('Không tìm thấy thẻ')
                                    ->body('Thẻ bạn chọn không tồn tại hoặc đã bị xoá. Vui lòng chọn lại hoặc bỏ trống.')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            if ($card->status === 'active') {
                                Notification::make()
                                    ->title('Thẻ đang được sử dụng')
                                    ->body('Thẻ này đang ở trạng thái hoạt động. Vui lòng chọn thẻ khác hoặc bỏ trống.')
                                    ->warning()
                                    ->send();

                                return;
                            }

                            $card->status = 'active';
                            $card->save();

                            $record->card_id = $card->id;
                        }

                        if ($record->relationLoaded('registration') || $record->registration) {
                            $registration = $record->registration;
                            if ($registration instanceof Model) {
                                $registration->status = 'entering';
                                $registration->save();
                            }
                        }
                        $record->save();
                    });

                    Notification::make()
                        ->title('Thành công')
                        ->body('Đã chuyển trạng thái cho khách vào')
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Lỗi')
                        ->body('Đã có lỗi xảy ra: '.$e->getMessage())
                        ->danger()
                        ->send();
                }
            })
            ->modalCancelAction(fn (Action $action) => $action
                ->label('Hủy')
                ->extraAttributes(['class' => 'ml-auto'])
            )
            ->modalSubmitAction(fn (Action $action) => $action
                ->label('Vào')
            );
    }
}
