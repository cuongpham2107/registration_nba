@php
 use SimpleSoftwareIO\QrCode\Facades\QrCode;
@endphp

<!doctype html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Invoice</title>
    <style>
        @font-face {
            font-family: 'DejaVuSans';
            src: url('{{ public_path('fonts/DejaVuSans.ttf') }}') format('truetype');
            font-weight: 400;
            font-style: normal;
        }

        @font-face {
            font-family: 'DejaVuSans';
            src: url('{{ public_path('fonts/DejaVuSans-Bold.ttf') }}') format('truetype');
            font-weight: 700;
            font-style: normal;
        }

        html,
        body {
            font-family: 'DejaVuSans', DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.3;
            color: #111;
        }

        /* Minimal layout helpers (avoid remote Tailwind in PDF renderer) */
        .flex { display: flex; }
        .items-center { align-items: center; }
        .justify-center { justify-content: center; }
        .justify-between { justify-content: space-between; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: 700; }
        .font-normal { font-weight: 400; }
        .text-xs { font-size: 12px; }
        .gap-2 { gap: 8px; }
        .space-y-1 > * + * { margin-top: 4px; }
        .p-1 { padding: 4px; }
        .px-1 { padding-left: 4px; padding-right: 4px; }
        .ml-1 { margin-left: 4px; }
        .mx-1 { margin-left: 4px; margin-right: 4px; }
        .w-16 { width: 64px; }
        .w-20 { width: 80px; }
        .w-10 { width: 40px; }
        .w-28 { width: 112px; }
        .w-32 { width: 128px; }
        .w-112\.5 { width: 450px; }
        .h-10 { height: 40px; }
        .h-8 { height: 32px; }
        .h-4 { height: 16px; }
        .h-2\.5 { height: 10px; }
        .h-full { height: 100%; }
        .h-70 { height: 280px; }
        .bg-gray-100 { background: #f5f5f5; }
        .bg-gray-300 { background: #d1d5db; }
        .border-b { border-bottom: 1px solid #111; }
        .border-dashed { border-bottom-style: dashed; }
        .border-black { border-color: #111; }
        .object-contain { object-fit: contain; }
        .whitespace-nowrap { white-space: nowrap; }
    </style>
</head>

<body>
    <div class="flex gap-2">
        <!-- LIÊN 1 -->
        <div class="h-62 w-112.5 bg-gray-100 p-1 text-xs">
            <!-- Header -->
            <div class="flex flex-row h-10 items-center justify-between">
                <div class="flex h-full w-16 items-center justify-center">
                    @if(file_exists($logo))
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logo)) }}" alt="Company Logo"
                            class="h-8 w-16 object-contain" />
                    @else
                        <div class="h-8 w-8 bg-gray-300 flex items-center justify-center text-xs">LOGO</div>
                    @endif
                </div>
                <div class="flex-1 font-bold">CÔNG TY CỔ PHẦN ASGL</div>
                <div class="pt-1">
                    @php
                        $invoiceIdForQr = $record->id ?? null;
                        $invoiceCompanyUrl = $invoiceIdForQr
                            ? route('invoice.with-company', ['id' => $invoiceIdForQr])
                            : url('/');
                    @endphp
                    {!! QrCode::size(50)->generate($invoiceCompanyUrl) !!}
                </div>
                
            </div>

            <!-- Body -->
            <div class="space-y-1">
                <div class="flex items-center justify-between">
                    <div class="w-20"></div>
                    <div class="text-center font-bold">
                        <div>PHÍ KHAI THÁC</div>
                        <div>KHU DỊCH VỤ LOGISTICS</div>
                    </div>
                    <div class="w-20 text-right">Số: T{{now()->format('m')}}-{{ $record->id }}</div>
                </div>

                <div class="h-4 px-1 font-bold">
                    Liên: Giao khách hàng
                </div>

                <!-- Xe -->
                <div class="flex h-4 items-center justify-center px-1">
                    <span class="whitespace-nowrap font-bold">Xe ô tô, BKS:</span>
                    <span
                        class="ml-1 w-32 border-b border-dashed border-black text-center font-normal">{{ $vehicle_number ?? '' }}</span>
                </div>

                <!-- Tải trọng -->
                <div class="flex h-4 items-center justify-center px-1">
                    <span class="whitespace-nowrap font-bold">Tải trọng:</span>
                    <span
                        class="ml-1 border-b border-dashed border-black text-center font-normal">{{ $vehicle_weight ?? '' }}</span>
                </div>

                <!-- Giờ vào -->
                <div class="flex h-4 items-center gap-1 px-1">
                    <span class="whitespace-nowrap">Giờ vào:</span>
                    <span
                        class="w-10 border-b border-dashed border-black text-center">{{ \Carbon\Carbon::parse($entry_time)->format('H') }}</span>
                    <span class="whitespace-nowrap">h</span>
                    <span class="whitespace-nowrap">/</span>
                    <span
                        class="w-10 border-b border-dashed border-black text-center">{{ \Carbon\Carbon::parse($entry_time)->format('i') }}</span>

                    <span class="whitespace-nowrap">Ngày:</span>
                    <span
                        class="w-32 border-b border-dashed border-black text-center">{{ \Carbon\Carbon::parse($entry_time)->format('d/m/Y') }}</span>
                </div>

                <!-- Giờ ra -->
                <div class="flex h-4 items-center gap-1 px-1">
                    <span class="whitespace-nowrap">Giờ ra:</span>
                    <span
                        class="w-10 border-b border-dashed border-black text-center">{{ \Carbon\Carbon::parse($exit_time)->format('H') }}</span>
                    <span class="whitespace-nowrap">h</span>
                    <span class="whitespace-nowrap">/</span>
                    <span
                        class="w-10 border-b border-dashed border-black text-center">{{ \Carbon\Carbon::parse($exit_time)->format('i') }}</span>

                    <span class="whitespace-nowrap">Ngày:</span>
                    <span
                        class="w-32 border-b border-dashed border-black text-center">{{ \Carbon\Carbon::parse($exit_time)->format('d/m/Y') }}</span>
                </div>

                <!-- Tổng thời gian -->
                <div class="flex h-4 items-center px-1">
                    <span class="whitespace-nowrap">Tổng thời gian khai thác:</span>
                    <span class="ml-1 w-32 border-b border-dashed border-black text-center">
                        @if (($total_minutes ?? 0) < 60)
                            {{ $total_minutes ?? 0 }} phút
                        @else
                            {{ $total_hours }} giờ / {{ $remaining_minutes ?? ($total_minutes % 60) }} phút
                        @endif
                    </span>
                </div>

                <!-- Mức phí -->
                <div class="flex h-4 items-center justify-center px-1 font-bold">
                    <span class="whitespace-nowrap">Phí ra vào:</span>

                    <span
                        class="mx-1 w-28 border-b border-dashed border-black text-center font-normal">{{ number_format($fee_breakdown['fee'] ?? ($fee_breakdown['total'] ?? 0), 0, '.', '.') }}</span>

                    <span class="whitespace-nowrap">Đồng</span>
                </div>

               

                <!-- Tổng phí -->
                <div class="flex h-4 items-center justify-center px-1 font-bold">
                    <span class="whitespace-nowrap">TỔNG:</span>
                    <span class="mx-1 w-28 border-b border-dashed border-black text-center font-normal">
                        {{ number_format($fee ?? 0, 0, '.', '.') }}
                    </span>
                    <span class="whitespace-nowrap">Đồng</span>
                </div>
            </div>

            <!-- Footer -->
            <div class="h-2.5 text-center text-[10px]">(Đã bao gồm thuế GTGT)</div>
        </div>

       
    </div>
</body>

</html>