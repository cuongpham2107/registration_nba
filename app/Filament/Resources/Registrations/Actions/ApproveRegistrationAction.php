<?php

namespace App\Filament\Resources\Registrations\Actions;

use App\Http\Controllers\RegistrationController;
use App\Models\Registration;
use App\Models\User;
use App\Models\UserApprover;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Size;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ApproveRegistrationAction
{
    public static function make(): Action
    {
        return Action::make('approve')
            ->label('Phê duyệt')
            ->icon('heroicon-m-check-circle')
            ->size(Size::Small)
            ->color('success')
            ->requiresConfirmation()
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
                    'approved_at' => now(),
                    'status' => 'approve',
                    'type' => $record->type ?? 'working',
                ]);
                (new RegistrationController)->createRegistrationEntryFromGuest($record);

                Notification::make()
                    ->title('Phê duyệt thành công')
                    ->success()
                    ->body('Đăng ký khách đã được phê duyệt.')
                    ->send();

                try {
                    $protectUsers = User::whereHas('roles', fn ($query) => $query->where('name', 'protect'))->get();
                    foreach ($protectUsers as $user) {
                        if (! $user instanceof Model) {
                            continue;
                        }
                        Notification::make()
                            ->title('Đơn xét duyệt đăng ký khách mới')
                            ->success()
                            ->body("Đã có 1 đăng ký khách của đơn vị {$record->name} được phê duyệt.")
                            ->broadcast($user);
                    }
                } catch (\Exception $e) {
                    Log::error('Broadcast notification failed: '.$e->getMessage());
                }
            });
    }
}
