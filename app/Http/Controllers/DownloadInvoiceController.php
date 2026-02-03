<?php

namespace App\Http\Controllers;

use App\Models\RegisterDirectly;
use function Spatie\LaravelPdf\Support\pdf;
use Illuminate\Support\Facades\Storage;

class DownloadInvoiceController extends Controller
{
    public function generateInvoice(RegisterDirectly $record)
    {
        // Tạo tên file unique
        $filename = 'invoice_' . $record->id . '_' . time() . '.pdf';
        $filePath = 'invoices/' . $filename;
        
        // Đảm bảo thư mục tồn tại
        $directory = storage_path('app/public/invoices');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        
        try {
            // Tạo PDF từ view invoice và lưu trực tiếp với cấu hình Node.js
            pdf('invoices.invoice', [
                'record' => $record,
                'vehicle_number' => $record->bks,
                'customer_name' => $record->name,
                'entry_time' => $record->actual_date_in,
                'exit_time' => $record->actual_date_out ?? now(),
                'total_hours' => $this->calculateHours($record->actual_date_in, $record->actual_date_out ?? now()),
                'total_minutes' => $this->calculateMinutes($record->actual_date_in, $record->actual_date_out ?? now()),
                'remaining_minutes' => $this->calculateMinutes($record->actual_date_in, $record->actual_date_out ?? now()) % 60,
                'fee' => $this->calculateFee($record),
                'logo' => public_path('images/ASG.png'),
            ])
            ->headerHtml('')
            ->footerHtml('')
            ->margins(0, 0, 0, 0)
            ->format('A4')
            ->save(storage_path('app/public/' . $filePath));
            
            return $filePath;
        } catch (\Exception $e) {
            // Nếu PDF generation thất bại, fallback tạo file HTML
            $html = view('invoices.invoice', [
                'record' => $record,
                'vehicle_number' => $record->bks,
                'customer_name' => $record->name,
                'entry_time' => $record->actual_date_in,
                'exit_time' => $record->actual_date_out ?? now(),
                'total_hours' => $this->calculateHours($record->actual_date_in, $record->actual_date_out ?? now()),
                'total_minutes' => $this->calculateMinutes($record->actual_date_in, $record->actual_date_out ?? now()),
                'remaining_minutes' => $this->calculateMinutes($record->actual_date_in, $record->actual_date_out ?? now()) % 60,
                'fee' => $this->calculateFee($record),
                'logo' => public_path('images/ASG.png'),
            ])->render();
            
            // Lưu HTML file thay vì PDF
            $htmlFilePath = str_replace('.pdf', '.html', $filePath);
            file_put_contents(storage_path('app/public/' . $htmlFilePath), $html);
            
            return $htmlFilePath;
        }
    }

    private function calculateMinutes($entryTime, $exitTime)
    {
        $entry = \Carbon\Carbon::parse($entryTime);
        $exit = \Carbon\Carbon::parse($exitTime);
        
        return $exit->diffInMinutes($entry);
    }

    private function calculateHours($entryTime, $exitTime)
    {
        $entry = \Carbon\Carbon::parse($entryTime);
        $exit = \Carbon\Carbon::parse($exitTime);
        
        return $exit->diffInHours($entry);
    }

    private function calculateFee(RegisterDirectly $record)
    {
        // Tính tổng số phút từ actual_date_in đến actual_date_out (hoặc now nếu chưa ra)
        $exitTime = $record->actual_date_out ?? now();
        $totalMinutes = $this->calculateMinutes($record->actual_date_in, $exitTime);
        
        // Lấy thông tin price list từ registration vehicle
        $priceList = null;
        if ($record->registrationVehicle && $record->registrationVehicle->price_list_id) {
            $priceList = \App\Models\PriceList::find($record->registrationVehicle->price_list_id);
        }
        
        // Nếu không có price list, dùng giá mặc định
        if (!$priceList) {
            // Tìm price list mặc định cho xe nhỏ (ID: 1)
            $priceList = \App\Models\PriceList::find(1); // Xe 3 bánh, Xe ô tô đến 9 chỗ, xe tải dưới 1,5 tấn và xe bán tải
        }
        
        // Fallback nếu vẫn không có price list
        if (!$priceList) {
            return 50000; // Phí cố định
        }
        
        $baseFee = $priceList->base_fee_120min;
        $additionalFee = $priceList->additional_fee_30min;
        
        if ($totalMinutes <= 120) {
            // ≤ 120 phút: fee = base_fee_120min
            return $baseFee;
        } else {
            // > 120 phút: fee = (base_fee_120min + additional_fee_30min × số_block)
            $extraMinutes = $totalMinutes - 120;
            $extraBlocks = ceil($extraMinutes / 30); // Mỗi 30 phút hoặc phần dư tính 1 block
            
            return $baseFee + ($additionalFee * $extraBlocks);
        }
    }

    // Public method để gọi từ bên ngoài
    public function calculateFeePublic(RegisterDirectly $record)
    {
        return $this->calculateFee($record);
    }

    // Method để download invoice
    public function download(RegisterDirectly $registerDirectly)
    {
        $filePath = $this->generateInvoice($registerDirectly);
        $fullPath = storage_path('app/public/' . $filePath);
        
        if (!file_exists($fullPath)) {
            abort(404, 'File not found');
        }
        
        $filename = 'HoaDon_' . $registerDirectly->bks . '_' . date('YmdHis') . '.pdf';
        
        return response()->download($fullPath, $filename);
    }
}
