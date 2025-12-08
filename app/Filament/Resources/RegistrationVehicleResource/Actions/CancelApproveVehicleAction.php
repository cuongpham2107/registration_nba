<?php

namespace App\Filament\Resources\RegistrationVehicleResource\Actions;

use App\Models\RegistrationVehicle;
use Filament\Notifications\Notification;
use Filament\Support\Enums\ActionSize;
use Filament\Tables\Actions\Action;

class CancelApproveVehicleAction
{
    public static function make(): Action
    {
        return Action::make('cancel_approve')
            ->label('Từ chối')
            ->icon('heroicon-m-x-circle')
            ->size(ActionSize::Small)
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Từ chối phê duyệt đăng ký xe')
            ->modalDescription('Bạn có chắc chắn muốn từ chối phê duyệt đăng ký này?')
            ->hidden(function (RegistrationVehicle $record) {
                $user = auth()->user();

                // Ẩn nếu không phải approve
                if ($record->status !== 'sent') {
                    return true;
                }

                // Ẩn nếu user không phải approve_vehicle hoặc không phải người duyệt
                if (!$user || !$user->hasRole('approve_vehicle') || $record->approved_by !== $user->id) {
                    return true;
                }

                return false;
            })
            ->action(function (RegistrationVehicle $record) {
                $record->update([
                    'status' => 'reject',
                    // 'approved_at' => null,
                    // 'approved_by' => null,
                ]);

                Notification::make()
                    ->title('Huỷ phê duyệt thành công')
                    ->warning()
                    ->body('Đăng ký xe đã bị huỷ phê duyệt.')
                    ->send();
            });
    }
}
