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

class ReEnterCardAction
{
    public static function make(): Action
    {
        return Action::make('re-enter-card')
            ->label('Vào lại')
            ->button()
            ->color('warning')
            ->hidden(fn (RegistrationEntry $record) => $record->status !== 'exited')
            ->icon('heroicon-o-arrow-uturn-left')
            ->modalHeading(function (RegistrationEntry $record) {
                if ($record->type === 'inspection') {
                    return 'Xe vào lại: '.$record->license_plate;
                } else {
                    return 'Khách vào lại: '.$record->name.' | CMND: '.$record->papers;
                }
            })
            ->schema([
                Fieldset::make('Lựa chọn')
                    ->schema([
                        Forms\Components\TextInput::make('license_plate')
                            ->label('Biển số xe')
                            ->default(fn (RegistrationEntry $record) => $record->license_plate)
                            ->disabled()
                            ->columnSpan(3),
                        Forms\Components\TextInput::make('guest.fee.vehicle_type')
                            ->label('Loại phương tiện')
                            ->default(fn (RegistrationEntry $record) => $record->guest?->fee?->vehicle_type)
                            ->disabled()
                            ->columnSpan(3),
                        Forms\Components\TextInput::make('id')
                            ->label('Thẻ')
                            ->autofocus()
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
                        $record->actual_date_out = null; // Clear previous exit time on re-entry

                        if (! empty($data['id'])) {
                            /** @var Card|null $card */
                            $card = Card::query()
                                ->where('account_id', $data['id'])
                                ->first();

                            if (! $card) {
                                // Kiểm tra độ dài thẻ mới
                                if (strlen($data['id']) <= 8) {
                                    Notification::make()
                                        ->title('Thẻ không hợp lệ')
                                        ->body('Mã thẻ mới không hợp lệ. Vui lòng quét mã thẻ có độ dài lớn hơn 8 ký tự.')
                                        ->danger()
                                        ->send();

                                    return;
                                }

                                // Nếu không có thẻ thì tự tạo mới theo mã nhập.
                                $card = new Card;
                                $card->account_id = $data['id'];
                                // Giữ tương thích với schema hiện tại: các cột này là bắt buộc.
                                $card->card_number = $data['id'];
                                $card->card_name = 'Thẻ '.$data['id'];
                                $card->status = 'inactive';
                                $card->save();
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
                        Notification::make()
                            ->title('Thành công')
                            ->body('Đã chuyển trạng thái cho khách vào lại')
                            ->success()
                            ->send();
                    });
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
                ->label('Vào lại')
            );
    }
}
