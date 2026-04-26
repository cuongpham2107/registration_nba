<?php

namespace App\Http\Controllers;

use App\Models\RegisterDirectly;
use App\Services\FeeCalculator;

class InvoiceFee
{
    public function getInvoiceHtml(RegisterDirectly $record): string
    {
        $logo = public_path('images/ASG.png');
        $entryTime = $record->actual_date_in;
        $exitTime = $record->actual_date_out ?? now();
        $totalMinutes = FeeCalculator::durationMinutes($entryTime, $exitTime);

        $invoice = $record->invoice;
        $feeBreakdown = FeeCalculator::forRegistrationEntry($record);

        $payload = [
            'record' => $record,
            'vehicle_number' => $record->license_plate ?? $record->bks,
            'vehicle_weight' => $record->fee?->vehicle_type ?: 'N/A',
            'customer_name' => $record->name,
            'entry_time' => $entryTime,
            'exit_time' => $exitTime,
            'total_hours' => intdiv($totalMinutes, 60),
            'total_minutes' => $totalMinutes,
            'remaining_minutes' => $totalMinutes % 60,
            'fee_breakdown' => $feeBreakdown,
            'fee' => $invoice ? $invoice->amount : (int) ($feeBreakdown['total'] ?? 0),
            'logo' => $logo,
            'is_print' => true,
        ];

        return view('invoices.invoice', $payload)->render();
    }

    public function download(RegisterDirectly $registerDirectly)
    {
        $html = $this->getInvoiceHtml($registerDirectly);

        return response($html)
            ->header('Content-Type', 'text/html');
    }
}
