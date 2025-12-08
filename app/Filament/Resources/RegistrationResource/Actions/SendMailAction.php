<?php

namespace App\Filament\Resources\RegistrationResource\Actions;

use App\Models\Customer;
use App\Models\Registration;
use App\Services\MailService;
use Filament\Notifications\Notification;
use Filament\Support\Enums\ActionSize;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Crypt;
use Throwable;

class SendMailAction
{
    public static function make(): Action
    {
        return Action::make('sendMail')
            ->label('Gửi')
            ->icon('heroicon-m-envelope')
            ->size(ActionSize::Small)
            ->requiresConfirmation()
            ->hidden(fn(Registration $record) => $record->status === 'sent' || $record->user_id !== auth()->id())
            ->action(function (Registration $record) {
                try {
                    // Lấy thông tin người phê duyệt từ relationship
                    $approver = $record->approver;

                    if (!$approver) {
                        Notification::make()
                            ->title('Gửi xét duyệt thất bại')
                            ->danger()
                            ->body('Không tìm thấy thông tin người phê duyệt')
                            ->send();
                        return;
                    }

                    if (!$approver->email) {
                        Notification::make()
                            ->title('Gửi xét duyệt thất bại')
                            ->danger()
                            ->body('Người phê duyệt "' . $approver->name . '" chưa có địa chỉ email')
                            ->send();
                        return;
                    }

                    // Validate email format
                    if (!filter_var($approver->email, FILTER_VALIDATE_EMAIL)) {
                        Notification::make()
                            ->title('Gửi xét duyệt thất bại')
                            ->danger()
                            ->body('Email của người phê duyệt "' . $approver->name . '" không hợp lệ: "' . $approver->email . '"')
                            ->send();
                        return;
                    }

                    $customers = Customer::where('registration_id', $record->id)->get();

                    // Gửi email
                    $mail = (new MailService())->sendMailWithTemplate(
                        $approver->email,
                        'Đăng ký khách: ' . $record->name . ' | ' . date('d/m/Y H:i:s'),
                        'template-mail.registration',
                        [
                            'id' => Crypt::encryptString($record->id),
                            'name' => $record->name,
                            'purpose' => $record->purpose,
                            'bks' => $record->bks,
                            'start_date' => $record->start_date,
                            'end_date' => $record->end_date,
                            'asset' => $record->asset,
                            'note' => $record->note,
                            'customers' => $customers,
                            'name_manager' => $approver->name,
                            'job_title_manager' => $approver->department_name ?? '',
                        ],
                    );

                    if (!$mail) {
                        Notification::make()
                            ->title('Gửi xét duyệt thất bại')
                            ->danger()
                            ->body('Không thể gửi email đến: ' . $approver->email)
                            ->send();
                        return;
                    }

                    // Cập nhật status
                    $record->update(['status' => 'sent']);

                    // Gửi Zalo (không hiển thị notification)
                    if ($approver->zalo_user_id) {
                        try {
                            $encryptedId = Crypt::encryptString($record->id);
                            $approveLink = route('approve', $encryptedId) . '?name_manager=' . urlencode($approver->name) . '&job_title_manager=' . urlencode($approver->department_name ?? '');
                            $rejectLink = route('reject', $encryptedId) . '?name_manager=' . urlencode($approver->name) . '&job_title_manager=' . urlencode($approver->department_name ?? '');

                            $zaloData = [
                                'type' => 'approve',
                                'zalo_id_user_approve' => $approver->zalo_user_id,
                                'data' => [
                                    'action' => 'Đăng ký khách mới',
                                    'customer_number' => (string) $record->id,
                                    'requestor' => $record->user->name ?? 'N/A',
                                    'customer_unit' => $record->name,
                                    'purpose' => $record->purpose,
                                    'quantity' => $customers->count() . ' người',
                                    'area' => $customers->pluck('areas')->flatten()->unique()->implode(', '),
                                    'request_time' => now()->format('H:i:s d-m-Y'),
                                    'user_approve' => $approver->name . ($approver->department_name ? ' (' . $approver->department_name . ')' : ''),
                                    'approve_link' => $approveLink,
                                    'reject_link' => $rejectLink,
                                ]
                            ];

                            \Illuminate\Support\Facades\Http::timeout(10)
                                ->post(config('services.zalo.webhook_url'), $zaloData);
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('Zalo notification failed: ' . $e->getMessage());
                        }
                    }

                    // Broadcast đến các approver
                    try {
                        $approveVehicleUsers = \App\Models\User::role('approver')->get();
                        foreach ($approveVehicleUsers as $user) {
                            Notification::make()
                                ->title('Yêu cầu đăng ký mới')
                                ->success()
                                ->body("Có 1 đăng ký khách của đơn vị {$record->name} chưa được phê duyệt.")
                                ->broadcast($user);
                        }
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Broadcast notification failed: ' . $e->getMessage());
                    }

                    // Hiển thị một notification tổng hợp duy nhất
                    Notification::make()
                        ->title('Gửi xét duyệt thành công')
                        ->success()
                        ->body('Email đã được gửi đến: ' . $approver->email)
                        ->send();
                } catch (Throwable $e) {
                    Notification::make()
                        ->title('Gửi xét duyệt thất bại')
                        ->danger()
                        ->body('Lỗi: ' . $e->getMessage())
                        ->send();
                }
            });
    }
}
