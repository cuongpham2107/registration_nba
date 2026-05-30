<?php

namespace App\Filament\Resources\RegistrationResource\Actions;

use App\Models\Registration;
use App\Models\User;
use App\Services\RegistrationService;
use Filament\Notifications\Notification;
use Filament\Support\Enums\ActionSize;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Log;

class ApproveRegistrationAction
{
    public static function make(): Action
    {
        return Action::make('approve')
            ->label('Phê duyệt')
            ->icon('heroicon-m-check-circle')
            ->size(ActionSize::Small)
            ->color('success')
            ->requiresConfirmation()
            ->hidden(function (Registration $record) {
                /** @var User $user */
                $user = auth()->user();

                // Ẩn nếu chưa gửi hoặc đã duyệt/từ chối
                if ($record->status !== 'sent' || $record->type === 'browse' || $record->type === 'refuse') {
                    return true;
                }

                // Cho phép super_admin thấy
                if ($user && $user->hasRole('super_admin')) {
                    return false;
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
            ->action(function (Registration $record) {
                $record->update([
                    'type' => 'browse',
                    'type_date' => now(),
                ]);

                (new RegistrationService)->createRegistrationDirectly($record, $record->fee_id ? 'vehicle' : 'passenger');

                Notification::make()
                    ->title('Phê duyệt thành công')
                    ->success()
                    ->body('Đăng ký khách đã được phê duyệt.')
                    ->send();

                try {
                    $protectUsers = User::role('protect')->get();
                    foreach ($protectUsers as $user) {
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
