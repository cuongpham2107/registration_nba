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
                    // Lấy tất cả người phê duyệt
                    $approvers = $record->creator?->approverConfigs()
                        ->with('approver')
                        ->get()
                        ->pluck('approver')
                        ->filter();

                    if ($approvers->isEmpty()) {
                        Notification::make()
                            ->title('Gửi xét duyệt thất bại')
                            ->danger()
                            ->body('Không tìm thấy thông tin người phê duyệt')
                            ->send();

                        return;
                    }

                    $customers = Guest::query()
                        ->where('registration_id', $record->id)
                        ->with('fee')
                        ->get();

                    $sentCount = 0;
                    $errors = [];

                    foreach ($approvers as $approver) {
                        if (! $approver->email) {
                            $errors[] = 'Người phê duyệt "'.$approver->name_code.'" chưa có địa chỉ email';

                            continue;
                        }

                        if (! filter_var($approver->email, FILTER_VALIDATE_EMAIL)) {
                            $errors[] = 'Email của người phê duyệt "'.$approver->name_code.'" không hợp lệ: "'.$approver->email.'"';

                            continue;
                        }

                        // Mã hoá registration_id
                        $encryptedId = Crypt::encryptString($record->id);

                        // Gửi email đến từng người phê duyệt
                        $mail = (new MailService)->sendMailWithTemplate(
                            $approver->email,
                            'Đăng ký khách: '.$record->name.' | '.date('d/m/Y H:i:s'),
                            'template-mail.registration',
                            [
                                'id' => $encryptedId,
                                'approver_id' => $approver->id,
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

                        if ($mail) {
                            $sentCount++;
                        } else {
                            $errors[] = 'Không thể gửi email đến: '.$approver->email;
                        }
                    }

                    if ($sentCount === 0) {
                        Notification::make()
                            ->title('Gửi xét duyệt thất bại')
                            ->danger()
                            ->body(implode("\n", $errors))
                            ->send();

                        return;
                    }

                    // Cập nhật status
                    $record->update(['status' => 'sent']);

                    // Refresh UI
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

                    $message = 'Email đã được gửi đến '.$sentCount.' người phê duyệt.';
                    if (! empty($errors)) {
                        $message .= "\nLỗi: ".implode("\n", $errors);
                    }

                    Notification::make()
                        ->title('Gửi xét duyệt thành công')
                        ->success()
                        ->body($message)
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
