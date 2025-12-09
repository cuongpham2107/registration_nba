<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use App\Models\RegisterDirectly;
use App\Models\Registration;
use App\Models\RegistrationVehicle;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

class RegistrationController extends Controller
{
    public function approve($id, Request $request)
    {
        $name_manager = $request->query('name_manager');
        $job_title_manager = $request->query('job_title_manager');
        $id = Crypt::decryptString($id);
        $registration = Registration::with('customers')->where('id', $id)->first();

        // Kiểm tra xem đã được xử lý chưa
        if ($registration->type !== null) {
            $status = "Lỗi";
            $message = "Đăng ký này đã được thực hiện phê duyệt rồi";
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
                        'quantity' => $customers->count() . ' người',
                        'area' => $customers->pluck('areas')->flatten()->unique()->implode(', '),
                        'request_time' => Carbon::parse($registration->created_at)->format('H:i:s d-m-Y'),
                        'approver' => $name_manager . ($job_title_manager ? ' (' . $job_title_manager . ')' : ''),
                        'approve_time' => now()->format('H:i d-m-Y'),
                    ]
                ];

                \Illuminate\Support\Facades\Http::timeout(10)
                    ->post(config('services.zalo.webhook_url'), $zaloData);

            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Zalo notification failed: ' . $e->getMessage());
            }
        }

        $status = "Duyệt";
        $message = "Đăng ký khách đã được phê duyệt thành công";
        try {
            // Gửi thông báo đến người có role "protect"
            $approveVehicleUsers = \App\Models\User::role('protect')->get();

            foreach ($approveVehicleUsers as $user) {
                Notification::make()
                    ->title('Đăng ký khách mới')
                    ->success()
                    ->body("Đã có 1 đăng ký khách của đơn vị {$registration->name} đã được phê duyệt thành công.")
                    ->broadcast($user);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Notification sending failed: ' . $e->getMessage());
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
            $status = "Lỗi";
            $message = "Đăng ký này đã được thực hiện phê duyệt rồi";
            return view('pages.mail-response')->with(compact('name_manager', 'job_title_manager', 'status', 'message'));
        }

        $registration->update([
            'type' => 'refuse',
            'type_date' => now()
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
                        'quantity' => $customers->count() . ' người',
                        'area' => $customers->pluck('areas')->flatten()->unique()->implode(', '),
                        'request_time' => \Carbon\Carbon::parse($registration->created_at)->format('H:i:s d-m-Y'),
                        'approver' => $name_manager . ($job_title_manager ? ' (' . $job_title_manager . ')' : ''),
                        'reject_time' => now()->format('H:i:s d-m-Y'),
                    ]
                ];

                \Illuminate\Support\Facades\Http::timeout(10)
                    ->post('http://192.168.1.70:5678/webhook/send-registration', $zaloData);

            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Zalo notification failed: ' . $e->getMessage());
            }
        }

        $status = "Từ chối";
        $message = "Đăng ký khách đã bị từ chối";
        return view('pages.mail-response')->with(compact('name_manager', 'job_title_manager', 'status', 'message'));
    }


    public function createRegistrationRirectly(Registration $registration)
    {
        $startDate = Carbon::parse($registration->start_date, 'Asia/Ho_Chi_Minh');
        $endDate = Carbon::parse($registration->end_date, 'Asia/Ho_Chi_Minh');

        $customers = $registration->customers;

        $currentDate = $startDate->copy()->startOfDay();

        while ($currentDate->lte($endDate->endOfDay())) {
            // Xác định thời gian bắt đầu và kết thúc cho ngày hiện tại
            $startOfDay = $currentDate->isSameDay($startDate) ? $startDate->copy() : $currentDate->copy()->startOfDay();
            $endOfDay = $currentDate->isSameDay($endDate) ? $endDate->copy() : $currentDate->copy()->endOfDay();

            // Nếu không có khách, tạo bản ghi với thông tin đơn vị
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
            }
            // Nếu có 1 khách, tạo 1 bản ghi với thông tin khách đó
            elseif ($customers->count() == 1) {
                $customer = $customers->first();
                RegisterDirectly::create([
                    'name' => $customer->name . '|' . $registration->name,
                    'papers' => $customer->papers,
                    'address' => '',
                    'bks' => $customer->license_plate ?? '',
                    'contact_person' => '',
                    'job' => $registration->purpose,
                    'start_date' => $startOfDay,
                    'end_date' => $endOfDay,
                    'type' => 'passenger',
                    'areas' => $customer->areas,
                    'status' => 'none',
                ]);
            }
            // Nếu có nhiều khách, tạo nhiều bản ghi
            else {
                foreach ($customers as $customer) {
                    RegisterDirectly::create([
                        'name' => $customer->name . '|' . $registration->name,
                        'papers' => $customer->papers,
                        'address' => '',
                        'bks' => $customer->license_plate ?? '',
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

            // Chuyển sang ngày tiếp theo
            $currentDate->addDay()->startOfDay();
        }
    }

    public function createRegistrationDirectlyFromVehicle(RegistrationVehicle $registration, array $areas, bool $is_priority = false)
    {
        $startDate = Carbon::parse($registration->expected_in_at, 'Asia/Ho_Chi_Minh');

        $record = RegisterDirectly::create([
            'name' => $registration->driver_name . ' | ' . $registration->name,
            'papers' => $registration->driver_id_card ?? '',
            'address' => '',
            'bks' => $registration->vehicle_number ?? '',
            'contact_person' => '',
            'job' => 'Số HAWB: ' . ($registration->hawb_number ?? '') .
                ($registration->notes ? ' | Ghi chú: ' . $registration->notes : ''),
            'start_date' => $startDate,
            'end_date' => null,
            'is_priority' => $is_priority,
            'type' => 'vehicle',
            'areas' => $areas,
            'status' => 'none',
        ]);

        return $record->id;
    }

    public function storeVehicle(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'driver_name' => 'required|string|max:255',
            'driver_phone' => 'nullable|string|max:20',
            'driver_id_card' => 'required|string|max:255',
            'vehicle_number' => 'required|string|max:255',
            'hawb_number' => 'required|string|max:255',
            'pcs' => 'nullable|string|max:255',
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
            'hawb_number.required' => 'Số HAWB là bắt buộc.',
            'hawb_number.string' => 'Số HAWB phải là chuỗi ký tự.',
            'hawb_number.max' => 'Số HAWB không được vượt quá 255 ký tự.',
            'expected_in_at.required' => 'Thời gian vào dự kiến là bắt buộc.',
            'expected_in_at.date' => 'Thời gian vào dự kiến phải là ngày hợp lệ.',
        ]);

        // Kiểm tra trùng lặp với điều kiện thời gian 4 tiếng
        $newExpectedTime = Carbon::parse($validated['expected_in_at']);
        
        $existingRegistration = RegistrationVehicle::where('name', $validated['name'])
            ->where('driver_name', $validated['driver_name'])
            ->where('driver_phone', $validated['driver_phone'])
            ->where('driver_id_card', $validated['driver_id_card'])
            ->where('vehicle_number', $validated['vehicle_number'])
            ->where('hawb_number', $validated['hawb_number'])
            ->orderBy('expected_in_at', 'desc')
            ->first();
        
        if ($existingRegistration) {
            $existingTime = Carbon::parse($existingRegistration->expected_in_at);
            $hoursDifference = $newExpectedTime->diffInHours($existingTime, false);
            
            // Kiểm tra nếu thời gian mới không cách thời gian cũ ít nhất 4 tiếng
            if (abs($hoursDifference) < 4) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Đăng ký trước đó thành công rồi phải vào giờ khác.');
            }
        }

        $validated['status'] = 'none';

        $record = RegistrationVehicle::create($validated);

        // If action is save_and_send, send email
        if ($request->input('action') === 'save_and_send') {
            try {
                // Tìm tất cả user có quyền approve_vehicle
                $approvers = \App\Models\User::whereHas('roles', function ($query) {
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
                        $mail = (new \App\Services\MailService())->sendMailWithTemplate(
                            $user->email,
                            'Đăng ký xe khai thác: ' . $record->driver_name . ' | ' . $record->vehicle_number . ' | ' . date('Y-m-d H:i:s'),
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
                        $approveVehicleUsers = \App\Models\User::role('approve_vehicle')->get();

                        foreach ($approveVehicleUsers as $user) {
                            Notification::make()
                                ->title('Đăng ký xe khai thác mới')
                                ->success()
                                ->body("Đăng ký xe {$record->vehicle_number} - Tài xế: {$record->driver_name} cần phê duyệt.")
                                ->broadcast($user);
                        }
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Notification sending failed: ' . $e->getMessage());
                    }
                    
                    return redirect()->back()->with('success', 'Đăng ký xe đã được tạo và gửi email thành công!');
                }

                return redirect()->back()->with('error', 'Đăng ký xe đã được tạo nhưng không thể gửi email.');
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Lỗi: ' . $e->getMessage());
            }
        }
        return redirect()->back()->with('success', 'Đăng ký xe đã được tạo thành công!');
    }

}
