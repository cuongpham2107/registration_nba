<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\RegistrationEntry;
use App\Models\User;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class RegistrationController extends Controller
{
    public function approve($id, Request $request)
    {
        $name_manager = $request->query('name_manager');
        $job_title_manager = $request->query('job_title_manager');
        $id = Crypt::decryptString($id);
        $registration = Registration::with('guests')->where('id', $id)->first();

        if (! $registration) {
            $status = 'Lỗi';
            $message = 'Không tìm thấy đăng ký.';

            return view('pages.mail-response')->with(compact('name_manager', 'job_title_manager', 'status', 'message'));
        }

    // Kiểm tra xem đã được xử lý chưa
    if (in_array($registration->status, ['approve', 'reject'], true) || filled($registration->approved_at)) {
            $status = 'Lỗi';
            $message = 'Đăng ký này đã được thực hiện phê duyệt rồi';

            return view('pages.mail-response')->with(compact('name_manager', 'job_title_manager', 'status', 'message'));
        }

        $this->createRegistrationEntryFromGuest($registration);
        $registration->update([
            'type' => $registration->type ?? 'working',
            'status' => 'approve',
            'approved_at' => now(),
        ]);

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

        if (! $registration) {
            $status = 'Lỗi';
            $message = 'Không tìm thấy đăng ký.';

            return view('pages.mail-response')->with(compact('name_manager', 'job_title_manager', 'status', 'message'));
        }

    // Kiểm tra xem đã được xử lý chưa
    if (in_array($registration->status, ['approve', 'reject'], true) || filled($registration->approved_at)) {
            $status = 'Lỗi';
            $message = 'Đăng ký này đã được thực hiện phê duyệt rồi';

            return view('pages.mail-response')->with(compact('name_manager', 'job_title_manager', 'status', 'message'));
        }

        $registration->update([
            'status' => 'reject',
            'approved_at' => now(),
        ]);

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
