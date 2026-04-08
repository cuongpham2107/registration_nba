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
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('license_plate')
                            ->label('Biển số xe')
                            ->default(fn (RegistrationEntry $record) => $record->license_plate)
                            ->disabled(),
                        Forms\Components\TextInput::make('guest.visitorVehicleFee.vehicle_type')
                            ->label('Loại phương tiện')
                            ->hidden(fn (RegistrationEntry $record): bool => blank($record->guest?->visitorVehicleFee))
                            ->default(fn (RegistrationEntry $record) => $record->guest?->visitorVehicleFee?->vehicle_type)
                            ->disabled(),
                        Forms\Components\TextInput::make('guest.gatheringPointFee.vehicle_type')
                            ->label('Loại phương tiện')
                            ->hidden(fn (RegistrationEntry $record): bool => blank($record->guest?->gatheringPointFee))
                            ->default(fn (RegistrationEntry $record) => $record->guest?->gatheringPointFee?->vehicle_type)
                            ->disabled(),
                        Forms\Components\Select::make('id')
                            ->label('Thẻ')
                            ->options(fn () => Card::query()
                                ->where('status', '!=', 'active')
                                ->orderBy('card_name')
                                ->pluck('card_name', 'id'))
                            ->searchable(['card_name', 'card_number'])
                            ->preload(),
                        Forms\Components\DateTimePicker::make('start_date')
                            ->label('Giờ vào')
                            ->default(now())
                            ->readOnly()
                            ->required(),
                    ])->columns(2),
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
