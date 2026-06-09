<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\Registration;
use App\Models\User;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class RegistrationService
{
    /**
     * Gửi email phê duyệt cho registration đến tất cả approver.
     */
    public function sendMailForRegistration(Registration $registration): bool
    {
        try {
            $approvers = $registration->creator?->approverConfigs()
                ->with('approver')
                ->get()
                ->pluck('approver')
                ->filter();

            if ($approvers->isEmpty()) {
                Log::warning('No approvers found for registration #'.$registration->id);

                return false;
            }

            $customers = Guest::query()
                ->where('registration_id', $registration->id)
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

                $encryptedId = Crypt::encryptString($registration->id);

                $mail = (new MailService)->sendMailWithTemplate(
                    $approver->email,
                    'Đăng ký khách: '.$registration->name.' | '.date('d/m/Y H:i:s'),
                    'template-mail.registration',
                    [
                        'id' => $encryptedId,
                        'approver_id' => $approver->id,
                        'name' => $registration->name,
                        'purpose' => $registration->purpose,
                        'start_date' => $registration->start_date,
                        'end_date' => $registration->end_date,
                        'asset' => $registration->asset,
                        'note' => $registration->note,
                        'customers' => $customers,
                        'name_manager' => $approver->full_name ?? '',
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
                Log::error('Send mail for registration failed', ['errors' => $errors]);

                return false;
            }

            // Cập nhật status
            $registration->update(['status' => 'sent']);

            // Broadcast đến các approver
            try {
                $approveVehicleUsers = User::role('approver')->get();
                foreach ($approveVehicleUsers as $user) {
                    Notification::make()
                        ->title('Yêu cầu đăng ký mới')
                        ->success()
                        ->body("Có 1 đăng ký khách của đơn vị {$registration->name} chưa được phê duyệt.")
                        ->broadcast($user);
                }
            } catch (Exception $e) {
                Log::error('Broadcast notification failed: '.$e->getMessage());
            }

            return true;
        } catch (Exception $e) {
            Log::error('Send mail for registration #'.$registration->id.' failed: '.$e->getMessage());

            return false;
        }
    }
}
