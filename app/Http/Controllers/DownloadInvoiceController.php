<?php

namespace App\Http\Controllers;

use App\Models\RegistrationEntry;
use Carbon\Carbon;

use function Spatie\LaravelPdf\Support\pdf;

class DownloadInvoiceController extends Controller
{
    public function generateInvoice(RegistrationEntry $record)
    {
        // Tạo tên file unique
        $filename = 'invoice_'.$record->id.'_'.time().'.pdf';
        $filePath = 'invoices/'.$filename;

        // Đảm bảo thư mục tồn tại
        $directory = storage_path('app/public/invoices');
        if (! file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $payload = [
            'record' => $record,
            'vehicle_number' => $record->bks,
            'customer_name' => $record->name,
            'entry_time' => $record->actual_date_in,
            'exit_time' => $record->actual_date_out ?? now(),
            'total_hours' => $this->calculateDisplayHours($record->actual_date_in, $record->actual_date_out ?? now()),
            'total_minutes' => (int) $this->calculateMinutes($record->actual_date_in, $record->actual_date_out ?? now()),
            'remaining_minutes' => (int) $this->calculateRemainingMinutes($record->actual_date_in, $record->actual_date_out ?? now()),
            'fee' => $this->calculateFee($record),
            'logo' => public_path('images/ASG.png'),
        ];

        try {
            pdf('invoices.invoice', $payload)
                ->headerHtml('')
                ->footerHtml('')
                ->margins(0, 0, 0, 0)
                ->format('A4')
                ->save(storage_path('app/public/'.$filePath));

            return $filePath;
        } catch (\Throwable $e) {
            // Fallback tạo file HTML để vẫn xem được online nếu PDF render lỗi.
            $html = view('invoices.invoice', $payload)->render();

            $htmlFilePath = str_replace('.pdf', '.html', $filePath);
            file_put_contents(storage_path('app/public/'.$htmlFilePath), $html);

            return $htmlFilePath;
        }
    }

    private function calculateMinutes($entryTime, $exitTime)
    {
        if (blank($entryTime) || blank($exitTime)) {
            return 0;
        }

        $entry = Carbon::parse($entryTime);
        $exit = Carbon::parse($exitTime);

        // diffInMinutes() expects the earlier date as the instance.
        return $entry->diffInMinutes($exit);
    }

    private function calculateHours($entryTime, $exitTime)
    {
        if (blank($entryTime) || blank($exitTime)) {
            return 0;
        }

        $entry = Carbon::parse($entryTime);
        $exit = Carbon::parse($exitTime);

        // diffInHours() expects the earlier date as the instance.
        return $entry->diffInHours($exit);
    }

    private function calculateRemainingMinutes($entryTime, $exitTime)
    {
    return (int) $this->calculateMinutes($entryTime, $exitTime) % 60;
    }

    /**
     * Display hours for invoice:
     * - If < 1 hour: show decimal hours (1 digit), e.g. 0.8
     * - If >= 1 hour: show integer hours only, e.g. 1 (minutes handled separately)
     */
    private function calculateDisplayHours($entryTime, $exitTime)
    {
    $totalMinutes = (int) $this->calculateMinutes($entryTime, $exitTime);

        if ($totalMinutes < 60) {
            return round($totalMinutes / 60, 1);
        }

        return intdiv($totalMinutes, 60);
    }

    private function calculateFee(RegistrationEntry $record)
    {
        // NOTE: Project currently doesn't include a PriceList model/table.
        // Fee is calculated from VehicleRegistration -> GatheringPointFee.

        $vehicleRegistration = $record->vehicleRegistration;
        if (! $vehicleRegistration) {
            return 0;
        }

        $fee = $vehicleRegistration->gatheringPointFee;
        if (! $fee) {
            return 0;
        }

        $exitTime = $record->actual_date_out ?? now();
        $totalMinutes = $this->calculateMinutes($record->actual_date_in, $exitTime);

        // Rough mapping (can be adjusted):
        // - <= 4 hours: charge half-day
        // - <= 8 hours: charge full-day
        // - > 8 hours : charge night fee
        if ($totalMinutes <= 240) {
            return (int) ($fee->morning_fee ?? 0);
        }

        if ($totalMinutes <= 480) {
            return (int) ($fee->full_day_fee ?? 0);
        }

        return (int) ($fee->night_fee ?? 0);
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
        $filename = 'HoaDon_'.$registrationEntry->bks.'_'.date('YmdHis').'.'.$extension;

        return response()->download($fullPath, $filename);
    }
}
