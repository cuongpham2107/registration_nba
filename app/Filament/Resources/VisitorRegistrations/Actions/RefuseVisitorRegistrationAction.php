<?php

namespace App\Filament\Resources\VisitorRegistrations\Actions;

use App\Models\VisitorRegistration;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Size;
use Illuminate\Support\Facades\Auth;

class RefuseVisitorRegistrationAction
{
    public static function make(): Action
    {
        return Action::make('refuse')
            ->label('Từ chối')
            ->icon('heroicon-m-x-circle')
            ->size(Size::Small)
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Từ chối đăng ký')
            ->modalDescription('Bạn có chắc chắn muốn từ chối đăng ký này?')
            ->hidden(function (VisitorRegistration $record) {
                $user = Auth::user();

                // Ẩn nếu chưa gửi hoặc đã duyệt/từ chối
                if ($record->status !== 'sent' || $record->type === 'browse' || $record->type === 'refuse') {
                    return true;
                }

                // Ẩn nếu user không phải approver
                if (! $user || ! $user->hasRole('approver')) {
                    return true;
                }

                // Ẩn nếu user không phải là người được chọn phê duyệt
                if ($record->approver_id !== $user->id) {
                    return true;
                }

                return false;
            })
            ->action(function (VisitorRegistration $record) {
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
