<?php

namespace App\Services;

use App\Models\RegisterDirectly;
use App\Models\Registration;
use Carbon\Carbon;

class RegistrationService
{
    public function createRegistrationDirectly(Registration $registration): void
    {
        $startDate = Carbon::parse($registration->start_date, 'Asia/Ho_Chi_Minh');
        $endDate = Carbon::parse($registration->end_date, 'Asia/Ho_Chi_Minh');

        $customers = $registration->customers;

        $currentDate = $startDate->copy()->startOfDay();

        while ($currentDate->lte($endDate->endOfDay())) {
            $startOfDay = $currentDate->isSameDay($startDate) ? $startDate->copy() : $currentDate->copy()->startOfDay();
            $endOfDay = $currentDate->isSameDay($endDate) ? $endDate->copy() : $currentDate->copy()->endOfDay();

            if ($customers->count() == 0) {
                RegisterDirectly::create([
                    'name' => $registration->name,
                    'papers' => '',
                    'address' => '',
                    'bks' => $registration->bks ?? '',
                    'contact_person' => '',
                    'job' => $registration->purpose,
                    'start_date' => $startOfDay,
                    'end_date' => $endOfDay,
                    'type' => 'passenger',
                    'areas' => '',
                    'status' => 'none',
                ]);
            } elseif ($customers->count() == 1) {
                $customer = $customers->first();
                RegisterDirectly::create([
                    'name' => $customer->name . '|' . $registration->name,
                    'papers' => $customer->papers,
                    'address' => '',
                    'bks' => $customer->license_plate ?: ($registration->bks ?? ''),
                    'contact_person' => '',
                    'job' => $registration->purpose,
                    'start_date' => $startOfDay,
                    'end_date' => $endOfDay,
                    'type' => 'passenger',
                    'areas' => $customer->areas,
                    'status' => 'none',
                ]);
            } else {
                foreach ($customers as $customer) {
                    RegisterDirectly::create([
                        'name' => $customer->name . '|' . $registration->name,
                        'papers' => $customer->papers,
                        'address' => '',
                        'bks' => $customer->license_plate ?: ($registration->bks ?? ''),
                        'contact_person' => '',
                        'job' => $registration->purpose,
                        'start_date' => $startOfDay,
                        'end_date' => $endOfDay,
                        'type' => 'passenger',
                        'areas' => $customer->areas,
                        'status' => 'none',
                    ]);
                }
            }

            $currentDate->addDay()->startOfDay();
        }
    }

    public function sendMailForRegistration(Registration $record): void
    {
        try {
            $approvers = $record->user?->approvers;

            if (!$approvers || $approvers->isEmpty()) {
                \Filament\Notifications\Notification::make()
                    ->title('Gửi xét duyệt thất bại')
                    ->danger()
                    ->body('Không tìm thấy thông tin người phê duyệt')
                    ->send();
                return;
            }

            $customers = \App\Models\Customer::where('registration_id', $record->id)->get();

            $mailSentTo = [];
            $mailFailedTo = [];

            foreach ($approvers as $approver) {
                if (!$approver->email || !filter_var($approver->email, FILTER_VALIDATE_EMAIL)) {
                    $mailFailedTo[] = $approver->name . ' (email không hợp lệ)';
                    continue;
                }

                $mail = (new \App\Services\MailService())->sendMailWithTemplate(
                    $approver->email,
                    'Đăng ký khách: ' . $record->name . ' | ' . date('d/m/Y H:i:s'),
                    'template-mail.registration',
                    [
                        'id' => \Illuminate\Support\Facades\Crypt::encryptString($record->id),
                        'name' => $record->name,
                        'purpose' => $record->purpose,
                        'bks' => $record->bks,
                        'start_date' => $record->start_date,
                        'end_date' => $record->end_date,
                        'asset' => $record->asset,
                        'note' => $record->note,
                        'sender' => $record->user->name ?? 'N/A',
                        'customers' => $customers,
                        'name_manager' => $approver->name,
                        'job_title_manager' => $approver->department_name ?? '',
                    ],
                );

                if ($mail) {
                    $mailSentTo[] = $approver->email;
                } else {
                    $mailFailedTo[] = $approver->name . ' (' . $approver->email . ')';
                }

                if ($approver->zalo_user_id) {
                    try {
                        $encryptedId = \Illuminate\Support\Facades\Crypt::encryptString($record->id);
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
            }

            if (!empty($mailSentTo)) {
                $record->update(['status' => 'sent']);
            }

            try {
                $approveVehicleUsers = \App\Models\User::role('approver')->get();
                foreach ($approveVehicleUsers as $user) {
                    \Filament\Notifications\Notification::make()
                        ->title('Yêu cầu đăng ký mới')
                        ->success()
                        ->body("Có 1 đăng ký khách của đơn vị {$record->name} chưa được phê duyệt.")
                        ->broadcast($user);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Broadcast notification failed: ' . $e->getMessage());
            }

            if (!empty($mailSentTo)) {
                \Filament\Notifications\Notification::make()
                    ->title('Gửi xét duyệt thành công')
                    ->success()
                    ->body('Email đã được gửi đến: ' . implode(', ', $mailSentTo)
                        . (!empty($mailFailedTo) ? "\nGửi thất bại: " . implode(', ', $mailFailedTo) : ''))
                    ->send();
            } else {
                \Filament\Notifications\Notification::make()
                    ->title('Gửi xét duyệt thất bại')
                    ->danger()
                    ->body('Không thể gửi email đến bất kỳ người phê duyệt nào.')
                    ->send();
            }
        } catch (\Throwable $e) {
            \Filament\Notifications\Notification::make()
                ->title('Gửi xét duyệt thất bại')
                ->danger()
                ->body('Lỗi: ' . $e->getMessage())
                ->send();
        }
    }
}
