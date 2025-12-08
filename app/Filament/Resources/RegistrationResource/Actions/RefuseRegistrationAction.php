<?php

namespace App\Filament\Resources\RegistrationResource\Actions;

use App\Models\Registration;
use Filament\Notifications\Notification;
use Filament\Support\Enums\ActionSize;
use Filament\Tables\Actions\Action;

class RefuseRegistrationAction
{
    public static function make(): Action
    {
        return Action::make('refuse')
            ->label('Từ chối')
            ->icon('heroicon-m-x-circle')
            ->size(ActionSize::Small)
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Từ chối đăng ký')
            ->modalDescription('Bạn có chắc chắn muốn từ chối đăng ký này?')
            ->hidden(function (Registration $record) {
                $user = auth()->user();

                // Ẩn nếu chưa gửi hoặc đã duyệt/từ chối
                if ($record->status !== 'sent' || $record->type === 'browse' || $record->type === 'refuse') {
                    return true;
                }

                // Ẩn nếu user không phải approver
                if (!$user || !$user->hasRole('approver')) {
                    return true;
                }

                // Ẩn nếu user không phải là người được chọn phê duyệt
                if ($record->approver_id !== $user->id) {
                    return true;
                }

                return false;
            })
            ->action(function (Registration $record) {
                $record->update([
                    'type' => 'refuse',
                    'type_date' => now(),
                ]);

                Notification::make()
                    ->title('Từ chối thành công')
                    ->warning()
                    ->body('Đăng ký khách đã bị từ chối.')
                    ->send();
            });
    }
}
