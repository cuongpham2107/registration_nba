<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\RegisterDirectly;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ViettelInvoiceService
{
    private string $baseUrl;

    private string $username;

    private string $password;

    private string $templateCode;

    private string $invoiceSeries;

    public function __construct()
    {
        $config = config('services.viettel_invoice');
        $this->baseUrl = $config['base_url'];
        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->templateCode = $config['template_code'];
        $this->invoiceSeries = $config['invoice_series'];
    }

    public function createInvoice(RegisterDirectly $record, Invoice $invoice): array
    {
        $payload = $this->buildPayload($record, $invoice);

        $url = $this->baseUrl.'/services/einvoiceapplication/api/InvoiceAPI/InvoiceWS/createInvoice/'.$this->username;

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic '.base64_encode($this->username.':'.$this->password),
        ])
            ->post($url, $payload);

        $result = $response->json();

        $errorCode = $result['errorCode'] ?? null;

        if (! $response->successful() || $errorCode) {
            $errorMsg = $result['description'] ?? $result['message'] ?? $response->body();

            Log::error('Viettel invoice API error', [
                'invoice_id' => $invoice->id,
                'status' => $response->status(),
                'payload' => $payload,
                'response' => $result,
            ]);

            throw new \RuntimeException('Viettel invoice API error: '.$errorMsg);
        }

        $codeOfTax = $result['result']['codeOfTax'] ?? null;

        if (! $codeOfTax) {
            $errorMsg = $result['description'] ?? 'Không nhận được mã thuế từ Viettel';

            Log::error('Viettel invoice missing codeOfTax', [
                'invoice_id' => $invoice->id,
                'response' => $result,
            ]);

            throw new \RuntimeException($errorMsg);
        }

        $invoice->update([
            'is_invoiced' => true,
            'invoiced_at' => now(),
            'code_of_tax' => $codeOfTax,
        ]);

        Log::info('Viettel invoice created successfully', [
            'invoice_id' => $invoice->id,
            'invoice_code' => $invoice->invoice_code,
            'code_of_tax' => $codeOfTax,
        ]);

        return $result;
    }

    private function buildPayload(RegisterDirectly $record, Invoice $invoice): array
    {
        $feeAmount = (float) $invoice->amount;
        $taxPercentage = 8;
        $totalWithoutTax = round($feeAmount / (1 + $taxPercentage / 100));
        $taxAmount = $feeAmount - $totalWithoutTax;

        $entryTime = Carbon::parse($record->actual_date_in, 'Asia/Ho_Chi_Minh');
        $exitTime = Carbon::parse($record->actual_date_out, 'Asia/Ho_Chi_Minh');
        $duration = FeeCalculator::formatDurationForDisplay($entryTime, $exitTime);

        $vehicleType = $record->fee?->ticket_code ?: 'Không xác định';

        // Nếu xe thuộc đơn vị có mã số thuế → buyer là đơn vị
        $unit = $invoice->carCatalog?->unit;
        if ($unit && $unit->tax_code) {
            $buyerInfo = [
                'buyerName' => $unit->name,
                'buyerAddressLine' => $unit->address,
                'buyerPhoneNumber' => null,
                'buyerFaxNumber' => null,
                'buyerIdType' => null,
                'buyerIdNo' => $unit->tax_code,
                'buyerNotGetInvoice' => 0,
            ];
        } else {
            $buyerInfo = [
                'buyerName' => $record->name ?: 'Khách lẻ',
                'buyerAddressLine' => null,
                'buyerPhoneNumber' => null,
                'buyerFaxNumber' => null,
                'buyerIdType' => $record->papers ? '1' : null,
                'buyerIdNo' => $record->papers,
                'buyerNotGetInvoice' => 1,
            ];
        }

        return [
            'generalInvoiceInfo' => [
                'invoiceType' => null,
                'templateCode' => $this->templateCode,
                'invoiceSeries' => $this->invoiceSeries,
                'invoiceIssuedDate' => null,
                'currencyCode' => 'VND',
                'adjustmentType' => '1',
                'adjustmentInvoiceType' => null,
                'originalInvoiceId' => null,
                'originalInvoiceIssueDate' => null,
                'additionalReferenceDesc' => null,
                'additionalReferenceDate' => null,
                'paymentStatus' => $invoice->is_paid,
                'exchangeRate' => null,
                'userName' => null,
                'certificateSerial' => null,
                'transactionId' => null,
                'invoiceNote' => null,
                'adjustAmount20' => null,
                'originalinvoiceType' => null,
                'originalTemplateCode' => null,
                'adjustedNote' => null,
                'reservationCode' => null,
                'validation' => 0,
                'typeId' => null,
                'classifyId' => null,
            ],
            'sellerInfo' => null,
            'buyerInfo' => $buyerInfo,
            'payments' => [
                [
                    'paymentMethodName' => 'TM/CK',
                    'paymentMethod' => null,
                ],
            ],
            'itemInfo' => [
                [
                    'selection' => 1,
                    'itemCode' => 'HH001',
                    'itemName' => 'Dịch vụ ra vào sân đỗ',
                    'unitCode' => null,
                    'unitName' => null,
                    'itemTotalAmountWithoutTax' => (float) $totalWithoutTax,
                    'taxPercentage' => $taxPercentage,
                    'taxAmount' => (float) $taxAmount,
                    'isIncreaseItem' => null,
                    'itemNote' => 'Biển số: '.($record->license_plate ?: $record->bks),
                    'batchNo' => null,
                    'expDate' => null,
                    'discount' => 0.0,
                    'discount2' => 0.0,
                    'itemDiscount' => 0.0,
                    'itemTotalAmountAfterDiscount' => (float) $totalWithoutTax,
                    'itemTotalAmountWithTax' => $feeAmount,
                    'adjustRatio' => null,
                ],
            ],
            'taxBreakdowns' => [
                [
                    'taxPercentage' => $taxPercentage,
                    'taxableAmount' => (float) $totalWithoutTax,
                    'taxAmount' => (float) $taxAmount,
                ],
            ],
            'summarizeInfo' => [
                'totalAmountWithoutTax' => (float) $totalWithoutTax,
                'totalTaxAmount' => (float) $taxAmount,
                'totalAmountWithTax' => $feeAmount,
                'totalAmountWithTaxFrn' => null,
                'totalAmountWithTaxInWords' => null,
                'discountAmount' => 0.0,
                'settlementDiscountAmount' => null,
                'totalAmountAfterDiscount' => (float) $totalWithoutTax,
                'totalAmountBeforeDiscount' => null,
            ],
            'metadata' => [
                [
                    'keyTag' => 'listenPlate',
                    'stringValue' => $record->license_plate ?: $record->bks,
                    'valueType' => 'text',
                    'keyLabel' => 'Biển số xe',
                ],
                [
                    'keyTag' => 'payload',
                    'stringValue' => $vehicleType,
                    'valueType' => 'text',
                    'keyLabel' => 'Trọng tải',
                ],
                [
                    'keyTag' => 'startTime',
                    'stringValue' => $entryTime->format('d/m/Y H:i:s'),
                    'valueType' => 'text',
                    'keyLabel' => 'Giờ vào',
                ],
                [
                    'keyTag' => 'endTime',
                    'stringValue' => $exitTime->format('d/m/Y H:i:s'),
                    'valueType' => 'text',
                    'keyLabel' => 'Giờ ra',
                ],
                [
                    'keyTag' => 'timeUsed',
                    'stringValue' => $duration,
                    'valueType' => 'text',
                    'keyLabel' => 'Thời gian khai thác',
                ],
            ],
            'meterReading' => null,
            'fuelReading' => null,
            'qrCodeInfo' => null,
        ];
    }
}
