<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\RegistrationEntry;
use App\Models\User;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RegistrationController extends Controller
{
    public function approve($id, Request $request)
    {
        $name_manager = $request->query('name_manager');
        $job_title_manager = $request->query('job_title_manager');
        $id = Crypt::decryptString($id);
        $registration = Registration::with('guests')->where('id', $id)->first();
        // Kiểm tra xem đã được xử lý chưa
        if ($registration->type !== null) {
            $status = 'Lỗi';
            $message = 'Đăng ký này đã được thực hiện phê duyệt rồi';

            return view('pages.mail-response')->with(compact('name_manager', 'job_title_manager', 'status', 'message'));
        }

        $this->createRegistrationEntryFromGuest($registration);
        $registration->type = 'working';
        $registration->approved_at = now();
        $registration->save();

        // Gửi thông báo Zalo sau khi phê duyệt thành công
        $approver = $registration->approver;
        if ($approver) {
            try {
                $guests = $registration->guests;

                // Tạm thời lấy từ config, không sử dụng zalo_user_id
                $zaloId = config('services.zalo.default_user_id', '3948439024214471746');

                $zaloData = [
                    'type' => 'approved',
                    'zalo_id_user_approve' => $zaloId,
                    'data' => [
                        'action' => 'Đăng ký khách đã được phê duyệt',
                        'customer_number' => (string) $registration->id,
                        'requestor' => $registration->creator?->name ?? 'N/A',
                        'customer_unit' => $registration->name,
                        'purpose' => $registration->purpose,
                        'quantity' => $guests->count().' người',
                        'area' => $guests->pluck('areas')->flatten()->unique()->implode(', '),
                        'request_time' => Carbon::parse($registration->created_at)->format('H:i:s d-m-Y'),
                        'approver' => $name_manager.($job_title_manager ? ' ('.$job_title_manager.')' : ''),
                        'approve_time' => now()->format('H:i d-m-Y'),
                    ],
                ];

                Http::timeout(10)
                    ->post(config('services.zalo.webhook_url'), $zaloData);

            } catch (\Exception $e) {
                Log::error('Zalo notification failed: '.$e->getMessage());
            }
        }

        $status = 'Duyệt';
        $message = 'Đăng ký khách đã được phê duyệt thành công';
        try {
            // Gửi thông báo đến người có role "protect"
            $approveVehicleUsers = User::whereHas('roles', fn ($query) => $query->where('name', 'protect'))->get();

            foreach ($approveVehicleUsers as $user) {
                if (! $user instanceof Model) {
                    continue;
                }
                Notification::make()
                    ->title('Đăng ký khách mới')
                    ->success()
                    ->body("Đã có 1 đăng ký khách của đơn vị {$registration->name} đã được phê duyệt thành công.")
                    ->broadcast($user);
            }
        } catch (\Exception $e) {
            Log::error('Notification sending failed: '.$e->getMessage());
        }

        return view('pages.mail-response')->with(compact('name_manager', 'job_title_manager', 'status', 'message'));
    }

    public function reject($id, Request $request)
    {
        $name_manager = $request->query('name_manager');
        $job_title_manager = $request->query('job_title_manager');
        $id = Crypt::decryptString($id);
        $registration = Registration::where('id', $id)->first();

        // Kiểm tra xem đã được xử lý chưa
        if ($registration->type !== null) {
            $status = 'Lỗi';
            $message = 'Đăng ký này đã được thực hiện phê duyệt rồi';

            return view('pages.mail-response')->with(compact('name_manager', 'job_title_manager', 'status', 'message'));
        }

        $registration->update([
            'status' => 'reject',
            'approved_at' => now(),
        ]);

        // Gửi thông báo Zalo sau khi từ chối
        $approver = $registration->approver;
        if ($approver) {
            try {
                $guests = $registration->guests;

                // Tạm thời lấy từ config, không sử dụng zalo_user_id
                $zaloId = config('services.zalo.default_user_id', '3948439024214471746');

                $zaloData = [
                    'type' => 'rejected',
                    'zalo_id_user_approve' => $zaloId,
                    'data' => [
                        'action' => 'Đăng ký khách đã bị từ chối',
                        'customer_number' => (string) $registration->id,
                        'requestor' => $registration->creator?->name ?? 'N/A',
                        'customer_unit' => $registration->name,
                        'purpose' => $registration->purpose,
                        'quantity' => $guests->count().' người',
                        'area' => $guests->pluck('areas')->flatten()->unique()->implode(', '),
                        'request_time' => Carbon::parse($registration->created_at)->format('H:i:s d-m-Y'),
                        'approver' => $name_manager.($job_title_manager ? ' ('.$job_title_manager.')' : ''),
                        'reject_time' => now()->format('H:i:s d-m-Y'),
                    ],
                ];

                Http::timeout(10)
                    ->post('http://192.168.1.70:5678/webhook/send-registration', $zaloData);

            } catch (\Exception $e) {
                Log::error('Zalo notification failed: '.$e->getMessage());
            }
        }

        $status = 'Từ chối';
        $message = 'Đăng ký khách đã bị từ chối';

        return view('pages.mail-response')->with(compact('name_manager', 'job_title_manager', 'status', 'message'));
    }

    public function createRegistrationEntryFromGuest(Registration $registration)
    {
        $startDate = Carbon::parse($registration->start_date, 'Asia/Ho_Chi_Minh');
        $endDate = Carbon::parse($registration->end_date, 'Asia/Ho_Chi_Minh');

        $guests = $registration->guests;

        $currentDate = $startDate->copy()->startOfDay();

        while ($currentDate->lte($endDate->endOfDay())) {
            // Determine start and end time for the current day
            $startOfDay = $currentDate->isSameDay($startDate) ? $startDate->copy() : $currentDate->copy()->startOfDay();
            $endOfDay = $currentDate->isSameDay($endDate) ? $endDate->copy() : $currentDate->copy()->endOfDay();

            foreach ($guests as $guest) {
                RegistrationEntry::create([
                    'name' => $guest->name.'|'.$registration->name,
                    'papers' => $guest->papers,
                    'license_plate' => $guest->license_plate ?: null,
                    'guest_id' => $guest->id,
                    'job' => $registration->purpose,
                    'start_date' => $startOfDay,
                    'end_date' => $endOfDay,
                    'type' => $registration->type,
                    'areas' => $guest->areas,
                    'registration_id' => $registration->id,
                    'status' => 'none',
                ]);
            }

            // Move to next day
            $currentDate->addDay()->startOfDay();
        }
    }
}
