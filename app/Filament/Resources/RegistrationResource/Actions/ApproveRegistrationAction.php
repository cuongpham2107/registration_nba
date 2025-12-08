<?php

namespace App\Filament\Resources\RegistrationResource\Actions;

use App\Models\Registration;
use Filament\Notifications\Notification;
use Filament\Support\Enums\ActionSize;
use Filament\Tables\Actions\Action;

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
                    'type' => 'browse',
                    'type_date' => now(),
                ]);

                (new \App\Http\Controllers\RegistrationController())->createRegistrationRirectly($record);

                Notification::make()
                    ->title('Phê duyệt thành công')
                    ->success()
                    ->body('Đăng ký khách đã được phê duyệt.')
                    ->send();
                    
                try {
                    $protectUsers = \App\Models\User::role('protect')->get();
                    foreach ($protectUsers as $user) {
                        Notification::make()
                            ->title('Đơn xét duyệt đăng ký khách mới')
                            ->success()
                            ->body("Đã có 1 đăng ký khách của đơn vị {$record->name} được phê duyệt.")
                            ->broadcast($user);
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Broadcast notification failed: ' . $e->getMessage());
                }
            });
    }
}
