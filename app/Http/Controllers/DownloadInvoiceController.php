<?php

namespace App\Http\Controllers;

use App\Models\RegistrationEntry;
use App\Support\FeeCalculator;
use Illuminate\Support\Facades\Log;

use function Spatie\LaravelPdf\Support\pdf;

class DownloadInvoiceController extends Controller
{
    public function generateInvoice(RegistrationEntry $record): string
    {
        // Tạo tên file unique
        $filename = 'invoice_'.$record->id.'_'.time().'.pdf';
        $filePath = 'invoices/'.$filename;

        // Đảm bảo thư mục tồn tại
        $directory = storage_path('app/public/invoices');
        if (! file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $logo = public_path('images/ASG.png');

        $payload = [
            'record' => $record,
            'vehicle_number' => $record->license_plate,
            // Only use `fees` table now.
            'vehicle_weight' => $record->guest?->fee?->vehicle_type,
            'customer_name' => $record->name,
            'entry_time' => $record->actual_date_in,
            'exit_time' => $record->actual_date_out ?? now(),
            // Đồng nhất với UI preview: làm tròn lên theo phút.
            // `total_hours` + `remaining_minutes` dùng để hiển thị kiểu: "H giờ, M phút".
            'total_hours' => intdiv(
                FeeCalculator::durationMinutes($record->actual_date_in, $record->actual_date_out ?? now()),
                60
            ),
            'total_minutes' => FeeCalculator::durationMinutes($record->actual_date_in, $record->actual_date_out ?? now()),
            'remaining_minutes' => FeeCalculator::durationMinutes($record->actual_date_in, $record->actual_date_out ?? now()) % 60,
            // Backward compatible: `fee` is the total.
            'fee_breakdown' => FeeCalculator::forRegistrationEntry($record),
            'fee' => (int) (FeeCalculator::forRegistrationEntry($record)['total'] ?? 0),
            'logo' => public_path('images/ASG.png'),
        ];
        // dd($payload);
        try {
            pdf('invoices.invoice', $payload)
                ->headerHtml('')
                ->footerHtml('')
                ->margins(0, 0, 0, 0)
                ->format('A4')
                ->withBrowsershot(function ($browsershot) {
                    $browsershot->setNodeBinary('node')
                        ->setNpmBinary('npm')
                        ->noSandbox()
                        ->addChromiumArguments(['disable-dev-shm-usage', 'disable-setuid-sandbox']);
                })
                ->save(storage_path('app/public/'.$filePath));

            return $filePath;
        } catch (\Throwable $e) {
            Log::error('PDF Generation Failed: '.$e->getMessage());
            $html = view('invoices.invoice', $payload)->render();

            $htmlFilePath = str_replace('.pdf', '.html', $filePath);
            file_put_contents(storage_path('app/public/'.$htmlFilePath), $html);

            return $htmlFilePath;
        }
    }

    private function calculateFee(RegistrationEntry $record)
    {
        return (int) (FeeCalculator::forRegistrationEntry($record)['total'] ?? 0);
    }

    // Public method để gọi từ bên ngoài
    public function calculateFeePublic(RegistrationEntry $record)
    {
        return $this->calculateFee($record);
    }

    // Method để download invoice
    public function download(RegistrationEntry $registrationEntry)
    {
        $filePath = $this->generateInvoice($registrationEntry);
        $fullPath = storage_path('app/public/'.$filePath);

        if (! file_exists($fullPath)) {
            abort(404, 'File not found');
        }

        $extension = pathinfo($filePath, PATHINFO_EXTENSION) ?: 'pdf';
        $licensePlate = $registrationEntry->license_plate ?: (string) $registrationEntry->id;
        $filename = 'HoaDon_'.$licensePlate.'_'.date('YmdHis').'.'.$extension;

        return response()->download($fullPath, $filename);
    }
}
