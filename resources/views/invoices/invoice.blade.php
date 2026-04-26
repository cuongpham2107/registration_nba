@php
    use SimpleSoftwareIO\QrCode\Facades\QrCode;
@endphp

<!doctype html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Phí Khai Thác</title>
    <!-- Include inline styles so they render correctly in the PDF -->
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #000;
            background: #fff;
        }

        /* Khổ 80mm, lề 3mm mỗi bên → vùng in ~74mm */
        @page {
            size: 80mm 200mm;
            margin: 0;
        }

        .ticket-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .ticket {
            width: 74mm;
            border: 0.3mm solid #000;
            margin: 0 auto;
        }

        /* ── Header ── */
        .ticket-header {
            border-bottom: 0.3mm solid #000;
            padding: 1.5mm 2mm;
            display: flex;
            align-items: center;
            gap: 1.5mm;
        }

        .logo {
            width: 12mm;
            height: 10mm;
            object-fit: contain;
            flex-shrink: 0;
        }

        .org-name {
            flex: 1;
            font-weight: 700;
            font-size: 7.5pt;
            text-align: center;
            line-height: 1.3;
        }

        /* ── Title ── */
        .ticket-title {
            border-bottom: 0.3mm solid #000;
            padding: 1.5mm 2mm;
            text-align: center;
        }

        .main-title {
            font-weight: 700;
            font-size: 11pt;
            letter-spacing: 0.3mm;
        }

        .sub-title {
            font-size: 8pt;
            margin-top: 0.5mm;
        }

        /* ── QR lớn ── */
        .ticket-qr {
            border-bottom: 0.3mm solid #000;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2.5mm;
        }

        /* ── Dữ liệu ── */
        .ticket-data {
            border-bottom: 0.3mm solid #000;
        }

        .data-row {
            display: flex;
            border-bottom: 0.2mm solid #ccc;
            min-height: 5.5mm;
        }

        .data-row:last-child {
            border-bottom: none;
        }

        .data-label {
            padding: 1mm 2mm;
            white-space: nowrap;
            border-right: 0.2mm solid #ccc;
            width: 22mm;
            display: flex;
            align-items: center;
            font-size: 7.5pt;
            color: #333;
        }

        .data-value {
            padding: 1mm 2mm;
            flex: 1;
            display: flex;
            align-items: center;
            font-size: 8pt;
        }

        /* ── Tổng ── */
        .total-row {
            display: flex;
            min-height: 6mm;
            font-size: 9pt;
            font-weight: 700;
        }

        .total-label {
            padding: 1.5mm 2mm;
            border-right: 0.3mm solid #000;
            width: 22mm;
            display: flex;
            align-items: center;
        }

        .total-value {
            padding: 1.5mm 2mm;
            flex: 1;
            display: flex;
            align-items: center;
        }

        /* ── Footer ── */
        .ticket-footer {
            border-top: 0.3mm dashed #000;
            padding: 1.5mm 2mm;
            text-align: center;
            font-size: 7pt;
            font-style: italic;
        }
    </style>
</head>

<body>
    @php
        $invoiceIdForQr = $record->id ?? null;
        $invoiceCompanyUrl =
            $invoiceIdForQr && \Illuminate\Support\Facades\Route::has('invoice.with-company')
                ? route('invoice.with-company', ['id' => $invoiceIdForQr])
                : url('/');
    @endphp

    <div class="ticket-wrap">
        <div class="ticket">
            <!-- Header -->
            <div class="ticket-header">
                @if (file_exists($logo))
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logo)) }}" alt="Company Logo"
                        class="logo" />
                @else
                    <div class="logo flex items-center justify-center text-xs" style="background:#d1d5db;">LOGO</div>
                @endif
                <div class="org-name">CÔNG TY CỔ PHẦN ASG</div>
            </div>

            <!-- Title -->
            <div class="ticket-title">
                <div class="main-title">PHÍ KHAI THÁC</div>
                <div class="sub-title">KHU DỊCH VỤ LOGISTICS</div>
            </div>

            <!-- QR Code large -->
            <div class="ticket-qr">
                <img src="https://qr.sepay.vn/img?acc=113604245888&bank=ICB&amount={{ $fee ?? 0 }}&des={{ urlencode(($vehicle_number ?? '') . ' thanh toan tien ve xe') }}"
                    style="width: 35mm; height: 35mm;" />
                <div class="text-[8px] italic">({{ $vehicle_number ?? '' }} thanh toan tien ve xe)</div>
            </div>

            <!-- Dữ liệu -->
            <div class="ticket-data">
                <div class="data-row">
                    <div class="data-label">Số vé</div>
                    <div class="data-value">
                        T{{ now()->format('m') }}-{{ str_pad($record->id ?? 0, 2, '0', STR_PAD_LEFT) }}</div>
                </div>
                <div class="data-row">
                    <div class="data-label">BKS xe</div>
                    <div class="data-value" style="font-weight:700;">{{ $vehicle_number ?? 'N/A' }}</div>
                </div>
                <div class="data-row">
                    <div class="data-label">Loại xe</div>
                    <div class="data-value">{{ $vehicle_weight }}</div>
                </div>
                <div class="data-row">
                    <div class="data-label">Giờ vào</div>
                    <div class="data-value">{{ \Carbon\Carbon::parse($entry_time)->format('H\hi / d-m-Y') }}</div>
                </div>
                <div class="data-row">
                    <div class="data-label">Giờ ra</div>
                    <div class="data-value">{{ \Carbon\Carbon::parse($exit_time)->format('H\hi / d-m-Y') }}</div>
                </div>
                <div class="data-row">
                    <div class="data-label">Thời gian</div>
                    <div class="data-value">
                        @if (($total_minutes ?? 0) < 60)
                            {{ $total_minutes ?? 0 }} phút
                        @else
                            {{ $total_hours }} giờ {{ $remaining_minutes ?? $total_minutes % 60 }} phút
                        @endif
                    </div>
                </div>
                <div class="data-row">
                    <div class="data-label">Phí ra vào</div>
                    <div class="data-value">{{ number_format($fee, 0, ',', '.') }} đồng</div>
                </div>
            </div>

            <!-- Tổng -->
            <div class="total-row">
                <div class="total-label">TỔNG PHÍ</div>
                <div class="total-value">{{ number_format($fee, 0, ',', '.') }} đồng</div>
            </div>

            <!-- Chân trang -->
            <div class="ticket-footer">(Đã bao gồm thuế GTGT)</div>
            {{-- <div
                style="border-top: 1px dashed #000; padding: 4px; display:flex; flex-direction: column; align-items: center;">
                <div style="margin-top: 4px;">
                    {!! QrCode::size(45)->margin(0)->generate($invoiceCompanyUrl) !!}
                </div>
                <span style="font-size: 7pt; font-style: italic; margin-top: 3px;">Yêu cầu xuất hóa đơn tại đây</span>
            </div> --}}
        </div>
    </div>
</body>

</html>
