<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\RegisterDirectly;
use App\Models\Registration;
use App\Models\User;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class RegistrationService
{
    public function createRegistrationDirectly(Registration $registration, $type = ''): void
    {
        $startDate = Carbon::parse($registration->start_date, 'Asia/Ho_Chi_Minh');
        $endDate = Carbon::parse($registration->end_date, 'Asia/Ho_Chi_Minh');

        $customers = $registration->customers;

        $currentDate = $startDate->copy()->startOfDay();

        while ($currentDate->lte($endDate->endOfDay())) {
            $startOfDay = $currentDate->isSameDay($startDate) ? $startDate->copy() : $currentDate->copy()->startOfDay();
            $endOfDay = $currentDate->isSameDay($endDate) ? $endDate->copy() : $currentDate->copy()->endOfDay();

            $commonData = [
                'start_date' => $startOfDay,
                'end_date' => $endOfDay,
                'status' => 'none',
                'registration_id' => $registration->id,
            ];

            if ($registration->fee_id) {
                $commonData['fee_id'] = $registration->fee_id;
            }

            if ($customers->count() == 0) {
                RegisterDirectly::create(array_merge($commonData, [
                    'name' => $registration->name,
                    'papers' => '',
                    'address' => '',
                    'bks' => $registration->bks ?? '',
                    'contact_person' => '',
                    'job' => $registration->purpose,
                    'type' => $type ? $type : 'passenger',
                    'areas' => '',
                ]));
            } elseif ($customers->count() == 1) {
                $customer = $customers->first();
                RegisterDirectly::create(array_merge($commonData, [
                    'name' => $customer->name.'|'.$registration->name,
                    'papers' => $customer->papers,
                    'address' => '',
                    'bks' => $customer->license_plate ?: ($registration->bks ?? ''),
                    'contact_person' => '',
                    'job' => $registration->purpose,
                    'type' => $type ? $type : 'passenger',
                    'areas' => $customer->areas,
                ]));
            } else {
                foreach ($customers as $customer) {
                    RegisterDirectly::create(array_merge($commonData, [
                        'name' => $customer->name.'|'.$registration->name,
                        'papers' => $customer->papers,
                        'address' => '',
                        'bks' => $customer->license_plate ?: ($registration->bks ?? ''),
                        'contact_person' => '',
                        'job' => $registration->purpose,
                        'type' => $type ? $type : 'passenger',
                        'areas' => $customer->areas,
                    ]));
                }
            }

            $currentDate->addDay()->startOfDay();
        }
    }

    public function sendMailForRegistration(Registration $record): void
    {
        try {
            $approvers = $record->user?->approvers;

            if (! $approvers || $approvers->isEmpty()) {
                Notification::make()
                    ->title('Gửi xét duyệt thất bại')
                    ->danger()
                    ->body('Không tìm thấy thông tin người phê duyệt')
                    ->send();

                return;
            }

            $customers = Customer::where('registration_id', $record->id)->get();

            $mailSentTo = [];
            $mailFailedTo = [];

            foreach ($approvers as $approver) {
                if (! $approver->email || ! filter_var($approver->email, FILTER_VALIDATE_EMAIL)) {
                    $mailFailedTo[] = $approver->name.' (email không hợp lệ)';

                    continue;
                }

                $mailData = [
                    'id' => Crypt::encryptString($record->id),
                    'name' => $record->name,
                    'purpose' => $record->purpose,
                    'bks' => $record->bks ?? $customers->pluck('license_plate')->filter()->implode(', '),
                    'start_date' => $record->start_date,
                    'end_date' => $record->end_date,
                    'asset' => $record->asset,
                    'note' => $record->note,
                    'sender' => $record->user->name ?? 'N/A',
                    'customers' => $customers,
                    'approver_id' => $approver->id,
                    'name_manager' => $approver->name,
                    'job_title_manager' => $approver->department_name ?? '',
                ];

                $mailHtml = view('template-mail.registration', $mailData)->render();
                Log::info('Mail HTML registration: '.$mailHtml);

                $mail = (new MailService)->sendMailWithTemplate(
                    $approver->email,
                    'Đăng ký khách: '.$record->name.' | '.date('d/m/Y H:i:s'),
                    'template-mail.registration',
                    $mailData,
                );

                if ($mail) {
                    $mailSentTo[] = $approver->email;
                } else {
                    $mailFailedTo[] = $approver->name.' ('.$approver->email.')';
                }

            }

            if (! empty($mailSentTo)) {
                $record->update(['status' => 'sent']);
            }

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

            if (! empty($mailSentTo)) {
                Notification::make()
                    ->title('Gửi xét duyệt thành công')
                    ->success()
                    ->body('Email đã được gửi đến: '.implode(', ', $mailSentTo)
                        .(! empty($mailFailedTo) ? "\nGửi thất bại: ".implode(', ', $mailFailedTo) : ''))
                    ->send();
            } else {
                Log::error('Send mail failed - no valid approvers', [
                    'registration_id' => $record->id,
                    'approvers' => $approvers->pluck('email')->toArray(),
                ]);
                Notification::make()
                    ->title('Gửi xét duyệt thất bại')
                    ->danger()
                    ->body('Không thể gửi email đến bất kỳ người phê duyệt nào.')
                    ->send();
            }
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gửi xét duyệt thất bại')
                ->danger()
                ->body('Lỗi: '.$e->getMessage())
                ->send();
        }
    }
}
