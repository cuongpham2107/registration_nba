<?php

namespace App\Http\Controllers;

use App\Models\RegistrationEntry;
use App\Models\User;
use App\Models\VehicleRegistration;
use App\Models\VisitorRegistration;
use App\Services\MailService;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RegistrationController extends Controller
{
    public function approve($id, Request $request)
    {
        $name_manager = $request->query('name_manager');
        $job_title_manager = $request->query('job_title_manager');
        $id = Crypt::decryptString($id);
        $registration = VisitorRegistration::with('customers')->where('id', $id)->first();
        // Kiểm tra xem đã được xử lý chưa
        if ($registration->type !== null) {
            $status = 'Lỗi';
            $message = 'Đăng ký này đã được thực hiện phê duyệt rồi';

            return view('pages.mail-response')->with(compact('name_manager', 'job_title_manager', 'status', 'message'));
        }

        $this->createRegistrationRirectly($registration);
        $registration->type = 'browse';
        $registration->type_date = now();
        $registration->save();

        // Gửi thông báo Zalo sau khi phê duyệt thành công
        $approver = $registration->approver;
        if ($approver) {
            try {
                $customers = $registration->customers;

                // Tạm thời lấy từ config, không sử dụng zalo_user_id
                $zaloId = config('services.zalo.default_user_id', '3948439024214471746');

                $zaloData = [
                    'type' => 'approved',
                    'zalo_id_user_approve' => $zaloId,
                    'data' => [
                        'action' => 'Đăng ký khách đã được phê duyệt',
                        'customer_number' => (string) $registration->id,
                        'requestor' => $registration->user->name ?? 'N/A',
                        'customer_unit' => $registration->name,
                        'purpose' => $registration->purpose,
                        'quantity' => $customers->count().' người',
                        'area' => $customers->pluck('areas')->flatten()->unique()->implode(', '),
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
        $registration = VisitorRegistration::where('id', $id)->first();

        // Kiểm tra xem đã được xử lý chưa
        if ($registration->type !== null) {
            $status = 'Lỗi';
            $message = 'Đăng ký này đã được thực hiện phê duyệt rồi';

            return view('pages.mail-response')->with(compact('name_manager', 'job_title_manager', 'status', 'message'));
        }

        $registration->update([
            'type' => 'refuse',
            'type_date' => now(),
        ]);

        // Gửi thông báo Zalo sau khi từ chối
        $approver = $registration->approver;
        if ($approver) {
            try {
                $customers = $registration->customers;

                // Tạm thời lấy từ config, không sử dụng zalo_user_id
                $zaloId = config('services.zalo.default_user_id', '3948439024214471746');

                $zaloData = [
                    'type' => 'rejected',
                    'zalo_id_user_approve' => $zaloId,
                    'data' => [
                        'action' => 'Đăng ký khách đã bị từ chối',
                        'customer_number' => (string) $registration->id,
                        'requestor' => $registration->user->name ?? 'N/A',
                        'customer_unit' => $registration->name,
                        'purpose' => $registration->purpose,
                        'quantity' => $customers->count().' người',
                        'area' => $customers->pluck('areas')->flatten()->unique()->implode(', '),
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

    public function createRegistrationRirectly(VisitorRegistration $registration)
    {

        $startDate = Carbon::parse($registration->start_date, 'Asia/Ho_Chi_Minh');
        $endDate = Carbon::parse($registration->end_date, 'Asia/Ho_Chi_Minh');

        $customers = $registration->customers;

        $currentDate = $startDate->copy()->startOfDay();

        while ($currentDate->lte($endDate->endOfDay())) {
            // Xác định thời gian bắt đầu và kết thúc cho ngày hiện tại
            $startOfDay = $currentDate->isSameDay($startDate) ? $startDate->copy() : $currentDate->copy()->startOfDay();
            $endOfDay = $currentDate->isSameDay($endDate) ? $endDate->copy() : $currentDate->copy()->endOfDay();

            foreach ($customers as $customer) {
                RegistrationEntry::create([
                    'name' => $customer->name.'|'.$registration->name,
                    'papers' => $customer->papers,
                    'address' => '',
                    'bks' => $customer->license_plate ? $customer->license_plate : '',
                    'id_customer' => $customer->id,
                    'job' => $registration->purpose,
                    'start_date' => $startOfDay,
                    'end_date' => $endOfDay,
                    'type' => 'passenger',
                    'areas' => $customer->areas,
                    'id_visitor_registration' => $registration->id,
                    'status' => 'none',
                ]);
            }

            // Chuyển sang ngày tiếp theo
            $currentDate->addDay()->startOfDay();
        }
    }

    public function createRegistrationEntryFromVehicle(VehicleRegistration $registration, bool $is_priority = false)
    {
        // Sử dụng database transaction với lock để đảm bảo atomic operation
        return DB::transaction(function () use ($registration, $is_priority) {
            // Lock bản ghi registration để tránh race condition
            $lockedRegistration = VehicleRegistration::where('id', $registration->id)->lockForUpdate()->first();

            if (! $lockedRegistration) {
                throw new \Exception('Không tìm thấy bản ghi đăng ký');
            }
            // Kiểm tra status để tránh double processing
            if ($lockedRegistration->status === 'approve') {
                // Tìm bản ghi RegistrationEntry đã tồn tại
                $existingRecord = RegistrationEntry::where('id_vehicle_registration', $lockedRegistration->id)->first();
                if ($existingRecord) {
                    return $existingRecord->id;
                }
            }

            $startDate = Carbon::parse($registration->expected_in_at, 'Asia/Ho_Chi_Minh');

            // Kiểm tra xem đã có bản ghi nào được tạo từ registration này trong 2 phút qua không (tăng từ 30 giây)
            $existingRecord = RegistrationEntry::where('id_vehicle_registration', $registration->id)
                ->where('created_at', '>=', now()->subMinutes(2))
                ->first();

            if ($existingRecord) {
                // Trả về ID của bản ghi đã tồn tại thay vì tạo mới
                return $existingRecord->id;
            }

            // Kiểm tra xem đã có bản ghi nào với cùng thông tin trong vòng 5 phút (tăng từ 1 phút)
            $duplicateCheck = RegistrationEntry::where('name', $registration->driver_name.' | '.$registration->name)
                ->where('papers', $registration->driver_id_card ?? '')
                ->where('bks', $registration->vehicle_number ?? '')
                ->where('created_at', '>=', now()->subMinutes(5))
                ->first();

            if ($duplicateCheck) {
                return $duplicateCheck->id;
            }

            // Tạo bản ghi mới trong transaction
            $record = RegistrationEntry::create([
                'name' => $registration->driver_name,
                'papers' => $registration->driver_id_card ?? '',
                'address' => '',
                'bks' => $registration->vehicle_number ?? '',
                'contact_person' => '',
                'job' => $registration->gatheringPointFee?->vehicle_type.'|'.$registration->liftingServiceFee?->service_name,
                'start_date' => $startDate,
                'end_date' => null,
                'is_priority' => $is_priority,
                'id_vehicle_registration' => $registration->id,
                'type' => 'vehicle',
                'status' => 'none',
            ]);

            return $record->id;
        });
    }

    public function storeVehicle(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'driver_name' => 'required|string|max:255',
            'driver_phone' => 'nullable|string|max:20',
            'driver_id_card' => 'required|string|max:255',
            'vehicle_number' => 'required|string|max:255',
            'pcs' => 'nullable|integer',
            'expected_in_at' => 'required|date',
            'notes' => 'nullable|string',
        ], [
            'driver_name.required' => 'Tên tài xế là bắt buộc.',
            'driver_name.string' => 'Tên tài xế phải là chuỗi ký tự.',
            'driver_name.max' => 'Tên tài xế không được vượt quá 255 ký tự.',
            'driver_id_card.required' => 'CCCD/CMND tài xế là bắt buộc.',
            'driver_id_card.string' => 'CCCD/CMND tài xế phải là chuỗi ký tự.',
            'driver_id_card.max' => 'CCCD/CMND tài xế không được vượt quá 255 ký tự.',
            'vehicle_number.required' => 'Biển số xe là bắt buộc.',
            'vehicle_number.string' => 'Biển số xe phải là chuỗi ký tự.',
            'vehicle_number.max' => 'Biển số xe không được vượt quá 255 ký tự.',
            'expected_in_at.required' => 'Thời gian vào dự kiến là bắt buộc.',
            'expected_in_at.date' => 'Thời gian vào dự kiến phải là ngày hợp lệ.',
        ]);

        // Kiểm tra trùng lặp với điều kiện thời gian 30 phút
        $newExpectedTime = Carbon::parse($validated['expected_in_at']);

        $existingRegistration = VehicleRegistration::where('name', $validated['name'])
            ->where('driver_name', $validated['driver_name'])
            ->where('driver_phone', $validated['driver_phone'])
            ->where('driver_id_card', $validated['driver_id_card'])
            ->where('vehicle_number', $validated['vehicle_number'])
            // ->where('pcs', $validated['pcs'])
            ->orderBy('expected_in_at', 'desc')
            ->first();

        if ($existingRegistration) {
            $existingTime = Carbon::parse($existingRegistration->expected_in_at);
            $minutesDifference = $newExpectedTime->diffInMinutes($existingTime, false);

            // Kiểm tra nếu thời gian mới không cách thời gian cũ ít nhất 30 phút
            if (abs($minutesDifference) < 30) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Đăng ký trước đó thành công rồi phải vào giờ khác.');
            }
        }

        $validated['status'] = 'none';

        $record = VehicleRegistration::create($validated);

        // If action is save_and_send, send email
        if ($request->input('action') === 'save_and_send') {
            try {
                // Tìm tất cả user có quyền approve_vehicle
                $approvers = User::whereHas('roles', function ($query) {
                    $query->where('name', 'approve_vehicle');
                })->orWhereHas('permissions', function ($query) {
                    $query->where('name', 'approve_vehicle');
                })->get();

                if ($approvers->isEmpty()) {
                    return redirect()->back()->with('error', 'Đăng ký xe đã được tạo nhưng không tìm thấy người phê duyệt.');
                }

                $mailSent = false;
                foreach ($approvers as $user) {
                    if ($user->email) {
                        $mail = (new MailService)->sendMailWithTemplate(
                            $user->email,
                            'Đăng ký xe kiểm hoá: '.$record->driver_name.' | '.$record->vehicle_number.' | '.date('Y-m-d H:i:s'),
                            'template-mail.registration-vehicle',
                            ['registration' => $record]
                        );

                        if ($mail) {
                            $mailSent = true;
                        }
                    }
                }

                if ($mailSent) {
                    $record->update(['status' => 'sent']);

                    try {
                        // Gửi thông báo đến người có role "approve_vehicle"
                        $approveVehicleUsers = User::role('approve_vehicle')->get();

                        foreach ($approveVehicleUsers as $user) {
                            Notification::make()
                                ->title('Đăng ký xe kiểm hoá mới')
                                ->success()
                                ->body("Đăng ký xe {$record->vehicle_number} - Tài xế: {$record->driver_name} cần phê duyệt.")
                                ->broadcast($user);
                        }
                    } catch (\Exception $e) {
                        Log::error('Notification sending failed: '.$e->getMessage());
                    }

                    // Redirect to success page with registration data
                    return redirect()->route('registration-vehicle.success')
                        ->with('registration_data', $validated);
                }

                return redirect()->back()->with('error', 'Đăng ký xe đã được tạo nhưng không thể gửi email.');
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Lỗi: '.$e->getMessage());
            }
        }

        return redirect()->back()->with('success', 'Đăng ký xe đã được tạo thành công!');
    }
}
