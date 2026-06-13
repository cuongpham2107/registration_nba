<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\RegistrationEntry;
use App\Models\User;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class RegistrationController extends Controller
{
    public function approve($id, Request $request)
    {
        $name_manager = $request->query('name_manager');
        $job_title_manager = $request->query('job_title_manager');
        // Nếu đã đăng nhập thì dùng ID của user đang đăng nhập, không dùng từ URL
        $approver_id = Auth::check() ? Auth::user()->id : $request->query('approver_id');
        $id = Crypt::decryptString($id);
        $registration = Registration::with('guests')->where('id', $id)->first();
        $user = User::find($registration?->user_id);

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
            'approver_id' => $approver_id,
        ]);

        $areas = $registration->guests
            ->pluck('areas')
            ->flatten()
            ->filter()
            ->unique()
            ->implode(', ');

        $sentAt = Carbon::parse($registration->created_at, 'Asia/Ho_Chi_Minh')->format('H:i:s d-m-Y');
        $approvedAt = now()->timezone('Asia/Ho_Chi_Minh')->format('H:i:s d-m-Y');
        $notificationMessage = "TB Duyệt đoàn khách số: {$registration->id}\n";
        $notificationMessage .= "Người y/c: {$user?->full_name}\n";
        $notificationMessage .= "Đv khách: {$registration->name}\n";
        $notificationMessage .= "Mục đích: {$registration->purpose}\n";
        $notificationMessage .= "Số lượng khách: {$registration->guests->count()} người\n";
        $notificationMessage .= "Khu vực LV: {$areas}\n";
        $notificationMessage .= "Giờ gửi yc: {$sentAt}\n";
        $notificationMessage .= "Người duyệt: {$name_manager} ({$job_title_manager})\n";
        $notificationMessage .= "Giờ duyệt: {$approvedAt}";

        $ch = curl_init();
        $url = 'http://192.168.1.70:5678/webhook/3abf742a-f6fe-4646-b3c9-4e344bccfe0f';

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $notificationMessage);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: */*',
            'User-Agent: Thunder Client (https://www.thunderclient.com)',
            'x-api-key: 76d43e23a183b85d31f140acca740976',
            'Content-Type: text/plain',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            Log::error('Webhook approval call failed', [
                'error' => $error,
                'http_code' => $httpCode,
                'data' => $registration,
            ]);
        } else {
            Log::info('Webhook approval call successful', [
                'response' => $response,
                'http_code' => $httpCode,
                'data' => $registration,
            ]);
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
        // Nếu đã đăng nhập thì dùng ID của user đang đăng nhập, không dùng từ URL
        $approver_id = Auth::check() ? Auth::user()->id : $request->query('approver_id');
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
            'approver_id' => $approver_id,
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
