<?php

namespace App\Filament\Resources\RegistrationVehicleResource\Actions;

use App\Models\Area;
use App\Models\RegistrationVehicle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Support\Enums\ActionSize;
use Filament\Tables\Actions\Action;

class ApproveVehicleAction
{
    public static function make(): Action
    {
        return Action::make('approve')
            ->label('Chọn vị trí')
            ->icon('heroicon-m-check-circle')
            ->size(ActionSize::Small)
            ->color('success')
            ->requiresConfirmation()
            ->hidden(function (RegistrationVehicle $record) {
                $user = auth()->user();

                // Ẩn nếu không phải sent hoặc đã duyệt/từ chối
                if ($record->status !== 'sent') {
                    return true;
                }

                // Ẩn nếu user không phải approve_vehicle
                if (!$user || !$user->hasRole('approve_vehicle')) {
                    return true;
                }

                return false;
            })
            ->modalWidth(\Filament\Support\Enums\MaxWidth::ThreeExtraLarge)
            ->form(
                fn(Form $form) =>
                $form->schema([
                    Forms\Components\Select::make('areas')
                        ->label('Chọn khu vực cho người đăng ký')
                        ->options(Area::all()->pluck('name', 'code'))
                        ->searchable()
                        ->multiple()
                        ->required(),
                    Forms\Components\Toggle::make('is_priority')
                        ->label('Ưu tiên')
                        ->helperText('Đánh dấu nếu đăng ký xe khai thác này là ưu tiên')
                        ->onIcon('heroicon-o-arrow-up')
                        ->offIcon('heroicon-o-arrow-down')
                        ->inline(false)
                ])->columns(2)
            )
            ->action(function (array $data, RegistrationVehicle $record) {
                try {
                    // Kiểm tra xem record đã được approve chưa để tránh double-click
                    $record->refresh(); // Reload từ database
                    
                    if ($record->status === 'approve') {
                        Notification::make()
                            ->title('Thông báo')
                            ->warning()
                            ->body('Đăng ký này đã được phê duyệt rồi.')
                            ->send();
                        return;
                    }

                    $id = (new \App\Http\Controllers\RegistrationController())->createRegistrationDirectlyFromVehicle($record, $data['areas'], $data['is_priority'] ?? false);
                    
                    if (!$id) {
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
                        'approved_by' => auth()->id(),
                        'id_registration_directly' => $id,
                    ]);

                    // Lấy tất cả user có role "protect"
                    $protectUsers = \App\Models\User::role('protect')->get();

                    // Gửi thông báo đến từng user có role "protect"
                    foreach ($protectUsers as $user) {
                        Notification::make()
                            ->title('Đăng ký xe khai thác mới')
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
                        ->body('Có lỗi xảy ra: ' . $e->getMessage())
                        ->send();
                }
            });
    }
}
