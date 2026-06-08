<?php

namespace App\Filament\Resources\Registrations\Actions;

use App\Models\Registration;
use App\Models\UserApprover;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Size;
use Illuminate\Support\Facades\Auth;

class RefuseRegistrationAction
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
            ->hidden(function (Registration $record) {
                $user = Auth::user();

                // Ẩn nếu chưa gửi hoặc đã duyệt/từ chối
                if ($record->status !== 'sent') {
                    return true;
                }

                // Ẩn nếu user không có quyền Approver (dùng policy authorization)
                if (! $user || ! $user->can('approver', $record)) {
                    return true;
                }

                // Ẩn nếu user không phải là người phê duyệt của đơn này
                $isApproverForCreator = UserApprover::query()
                    ->where('user_id', $record->user_id)
                    ->where('approver_id', $user->id)
                    ->exists();

                if (! $isApproverForCreator) {
                    return true;
                }

                return false;
            })
            ->action(function (Registration $record) {
                $record->update([
                    'status' => 'reject',
                    'approved_at' => now(),
                ]);

                Notification::make()
                    ->title('Từ chối thành công')
                    ->warning()
                    ->body('Đăng ký khách đã bị từ chối.')
                    ->send();
            });
    }
}
