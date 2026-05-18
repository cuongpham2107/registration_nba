<?php

namespace App\Filament\Resources\Registrations\Actions;

use App\Models\Guest;
use App\Models\Registration;
use App\Models\User;
use App\Services\MailService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Size;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Throwable;

class SendMailRegistrationAction
{
    public static function make(): Action
    {
        return Action::make('sendMail')
            ->label('Gửi')
            ->icon('heroicon-m-envelope')
            ->size(Size::Small)
            ->requiresConfirmation()
            ->hidden(fn (Registration $record): bool => in_array($record->status, ['sent', 'approve', 'reject', 'entering', 'exited'], true)
                || ! Auth::user()?->can('sendEmail', $record)
            )
            ->action(function (Registration $record, Component $livewire): void {
                try {
                    // Lấy thông tin người phê duyệt từ relationship
                    $approver = $record->approver;
                    // dd($approver->department);
                    if (! $approver) {
                        Notification::make()
                            ->title('Gửi xét duyệt thất bại')
                            ->danger()
                            ->body('Không tìm thấy thông tin người phê duyệt')
                            ->send();

                        return;
                    }

                    if (! $approver->email) {
                        Notification::make()
                            ->title('Gửi xét duyệt thất bại')
                            ->danger()
                            ->body('Người phê duyệt "'.$approver->name_code.'" chưa có địa chỉ email')
                            ->send();

                        return;
                    }

                    // Validate email format
                    if (! filter_var($approver->email, FILTER_VALIDATE_EMAIL)) {
                        Notification::make()
                            ->title('Gửi xét duyệt thất bại')
                            ->danger()
                            ->body('Email của người phê duyệt "'.$approver->name_code.'" không hợp lệ: "'.$approver->email.'"')
                            ->send();

                        return;
                    }
                    $customers = Guest::query()
                        ->where('registration_id', $record->id)
                        ->with('fee')
                        ->get();
                    // Gửi email
                    $mail = (new MailService)->sendMailWithTemplate(
                        $approver->email,
                        'Đăng ký khách: '.$record->name.' | '.date('d/m/Y H:i:s'),
                        'template-mail.registration',
                        [
                            'id' => Crypt::encryptString($record->id),
                            'name' => $record->name,
                            'purpose' => $record->purpose,
                            'start_date' => $record->start_date,
                            'end_date' => $record->end_date,
                            'asset' => $record->asset,
                            'note' => $record->note,
                            'customers' => $customers,
                            'name_manager' => $approver->name_code ?? '',
                            'job_title_manager' => $approver->department ?? '',
                        ],
                    );
                    if (! $mail) {
                        Notification::make()
                            ->title('Gửi xét duyệt thất bại')
                            ->danger()
                            ->body('Không thể gửi email đến: '.$approver->email)
                            ->send();

                        return;
                    }

                    // Cập nhật status
                    $record->update(['status' => 'sent']);

                    // Refresh UI so `hidden()` is re-evaluated immediately.
                    $livewire->dispatch('$refresh');
                    // Broadcast đến các approver
                    try {
                        $approveVehicleUsers = User::role('approver')->get();
                        foreach ($approveVehicleUsers as $user) {
                            Notification::make()
                                ->title('Yêu cầu đăng ký mới')
                                ->success()
                                ->body("Có 1 đăng ký khách của đơn vị {$record->name} chưa được phê duyệt.")
                                ->broadcast($user);
                        }
                    } catch (\Exception $e) {
                        Log::error('Broadcast notification failed: '.$e->getMessage());
                    }

                    // Hiển thị một notification tổng hợp duy nhất
                    Notification::make()
                        ->title('Gửi xét duyệt thành công')
                        ->success()
                        ->body('Email đã được gửi đến: '.$approver->email)
                        ->send();
                } catch (Throwable $e) {
                    Notification::make()
                        ->title('Gửi xét duyệt thất bại')
                        ->danger()
                        ->body('Lỗi: '.$e->getMessage())
                        ->send();
                }
            });
    }
}
