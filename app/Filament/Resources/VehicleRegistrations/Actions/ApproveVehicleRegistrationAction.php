<?php

namespace App\Filament\Resources\VehicleRegistrations\Actions;

use App\Http\Controllers\RegistrationController;
use App\Models\User;
use App\Models\VehicleRegistration;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Size;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ApproveVehicleRegistrationAction
{
    public static function make(): Action
    {
        return Action::make('approve')
            ->label('Phê duyệt')
            ->icon('heroicon-m-check-circle')
            ->size(Size::Small)
            ->color('success')
            ->requiresConfirmation()
            ->hidden(function (VehicleRegistration $record) {
                $user = Auth::user();

                if ($record->status !== 'sent') {
                    return true;
                }

                if (! $user || ! $user->hasRole('approve_vehicle') && ! $user->hasRole('super_admin')) {
                    return true;
                }

                return false;
            })
            ->schema([
                Forms\Components\Toggle::make('is_priority')
                    ->label('Ưu tiên')
                    ->helperText('Đánh dấu nếu Đăng ký xe kiểm hoá này là ưu tiên')
                    ->onIcon('heroicon-o-arrow-up')
                    ->offIcon('heroicon-o-arrow-down')
                    ->inline(true),
            ])
            ->action(function (array $data, VehicleRegistration $record) {
                try {
                    $record->refresh();
                    if ($record->status === 'approve') {
                        Notification::make()
                            ->title('Thông báo')
                            ->warning()
                            ->body('Đăng ký này đã được phê duyệt rồi.')
                            ->send();

                        return;
                    }

                    $id = (new RegistrationController)->createRegistrationEntryFromVehicle($record, $data['is_priority'] ?? false);

                    if (! $id) {
                        Notification::make()
                            ->title('Lỗi')
                            ->danger()
                            ->body('Không thể tạo bản ghi đăng ký trực tiếp.')
                            ->send();

                        return;
                    }

                    $record->update([
                        'status' => 'approve',
                        'is_priority' => $data['is_priority'] ?? false,
                        'approved_at' => now(),
                        'approved_by' => Auth::id(),
                        'id_registration_entry' => $id,
                    ]);

                    $protectUsers = User::whereHas('roles', fn ($query) => $query->where('name', 'protect'))->get();

                    foreach ($protectUsers as $user) {
                        if (! $user instanceof Model) {
                            continue;
                        }

                        Notification::make()
                            ->title('Đăng ký xe kiểm hoá mới')
                            ->success()
                            ->body("Đăng ký xe {$record->vehicle_number} - Tài xế: {$record->driver_name} đã được phê duyệt.")
                            ->broadcast($user);
                    }

                    Notification::make()
                        ->title('Phê duyệt thành công')
                        ->success()
                        ->body('Đăng ký xe đã được phê duyệt và thông báo đã được gửi đến bảo vệ.')
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Lỗi phê duyệt')
                        ->danger()
                        ->body('Có lỗi xảy ra: '.$e->getMessage())
                        ->send();
                }
            });
    }
}
