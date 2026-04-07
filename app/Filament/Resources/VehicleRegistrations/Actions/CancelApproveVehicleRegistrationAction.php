<?php

namespace App\Filament\Resources\VehicleRegistrations\Actions;

use App\Models\VehicleRegistration;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Size;

class CancelApproveVehicleRegistrationAction
{
    public static function make(): Action
    {
        return Action::make('cancel_approve')
            ->label('Từ chối')
            ->icon('heroicon-m-x-circle')
            ->size(Size::Small)
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Từ chối phê duyệt đăng ký xe')
            ->modalDescription('Bạn có chắc chắn muốn từ chối phê duyệt đăng ký này?')
            ->hidden(function (VehicleRegistration $record) {
                $user = auth()->user();

                if ($record->status !== 'sent') {
                    return true;
                }

                if (! $user || ! $user->hasRole('approve_vehicle') || $record->approved_by !== $user->id) {
                    return true;
                }

                return false;
            })
            ->action(function (VehicleRegistration $record) {
                $record->update([
                    'status' => 'reject',
                ]);

                Notification::make()
                    ->title('Huỷ phê duyệt thành công')
                    ->warning()
                    ->body('Đăng ký xe đã bị huỷ phê duyệt.')
                    ->send();
            });
    }
}
