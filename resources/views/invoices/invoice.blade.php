<html lang="en">
    <head>
        <title>Invoice</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body>
        <div class="flex gap-2">
            <!-- LIÊN 1 -->
            <div class="h-60 w-[450px] bg-gray-100 p-1 text-xs">
                <!-- Header -->
                <div class="flex h-10 items-center">
                <div class="flex h-full w-16 items-center justify-center">
                    @if(file_exists($logo))
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logo)) }}" alt="Company Logo" class="h-8 w-16 object-contain"/>
                    @else
                        <div class="h-8 w-8 bg-gray-300 flex items-center justify-center text-xs">LOGO</div>
                    @endif
                </div>
                <div class="flex-1 text-center font-bold">CÔNG TY CỔ PHẦN TẬP ĐOÀN ASG</div>
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
                    <span class="ml-1 w-32 border-b border-dashed border-black text-center font-normal">{{ $vehicle_number ?? '' }}</span>
                </div>

                <!-- Tải trọng -->
                <div class="flex h-4 items-center justify-center px-1">
                    <span class="whitespace-nowrap font-bold">Tải trọng:</span>
                    <span class="ml-1 w-32 border-b border-dashed border-black text-center font-normal">Xe ô tô dưới 1,5T</span>
                </div>

                <!-- Giờ vào -->
                <div class="flex h-4 items-center gap-1 px-1">
                    <span class="whitespace-nowrap">Giờ vào:</span>
                    <span class="w-10 border-b border-dashed border-black text-center">{{ \Carbon\Carbon::parse($entry_time)->format('H') }}</span>
                    <span class="whitespace-nowrap">h</span>
                    <span class="whitespace-nowrap">/</span>
                    <span class="w-10 border-b border-dashed border-black text-center">{{ \Carbon\Carbon::parse($entry_time)->format('i') }}</span>

                    <span class="whitespace-nowrap">Ngày:</span>
                    <span class="w-32 border-b border-dashed border-black text-center">{{ \Carbon\Carbon::parse($entry_time)->format('d/m/Y') }}</span>
                </div>

                <!-- Giờ ra -->
                <div class="flex h-4 items-center gap-1 px-1">
                    <span class="whitespace-nowrap">Giờ ra:</span>
                    <span class="w-10 border-b border-dashed border-black text-center">{{ \Carbon\Carbon::parse($exit_time)->format('H') }}</span>
                    <span class="whitespace-nowrap">h</span>
                    <span class="whitespace-nowrap">/</span>
                    <span class="w-10 border-b border-dashed border-black text-center">{{ \Carbon\Carbon::parse($exit_time)->format('i') }}</span>

                    <span class="whitespace-nowrap">Ngày:</span>
                    <span class="w-32 border-b border-dashed border-black text-center">{{ \Carbon\Carbon::parse($exit_time)->format('d/m/Y') }}</span>
                </div>

                <!-- Tổng thời gian -->
                <div class="flex h-4 items-center px-1">
                    <span class="whitespace-nowrap">Tổng thời gian khai thác:</span>
                    <span class="ml-1 w-32 border-b border-dashed border-black text-center">{{ $total_hours }} giờ / {{ $remaining_minutes ?? ($total_minutes % 60) }} phút</span>
                </div>

                <!-- Mức phí -->
                <div class="flex h-4 items-center justify-center px-1 font-bold">
                    <span class="whitespace-nowrap">MỨC PHÍ:</span>

                    <span class="mx-1 w-28 border-b border-dashed border-black text-center font-normal">{{ number_format($fee, 0, '.', '.') }}</span>

                    <span class="whitespace-nowrap">Đồng</span>
                </div>
                </div>

                <!-- Footer -->
                <div class="h-2.5 text-center text-[10px]">(Đã bao gồm thuế GTGT)</div>
            </div>

            <!-- LIÊN 2 (chỉ khác nhãn liên, data dùng chung) -->
            {{-- <div class="h-60 w-[450px] bg-gray-100 p-1 text-xs">
                <div class="flex h-10 items-center">
                <div class="flex h-full w-16 items-center justify-center">
                    @if(file_exists($logo))
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logo)) }}" alt="Company Logo" class="h-8 w-16 object-contain"/>
                    @else
                        <div class="h-8 w-8 bg-gray-300 flex items-center justify-center text-xs">LOGO</div>
                    @endif
                </div>
                <div class="flex-1 text-center font-bold">CÔNG TY CỔ PHẦN TẬP ĐOÀN ASG</div>
                </div>

                <div class="space-y-1">
                <div class="flex items-center justify-between">
                    <div class="w-20"></div>
                    <div class="text-center font-bold">
                    <div>PHÍ KHAI THÁC</div>
                    <div>KHU DỊCH VỤ LOGISTICS</div>
                    </div>
                     <div class="w-20 text-right">Số: T{{now()->format('m')}}-{{ $record->id }}</div>
                </div>

                <div class="h-4 px-1 font-bold">Liên 2: Giao khách hàng</div>

                <!-- Xe -->
                <div class="flex h-4 items-center justify-center px-1">
                    <span class="whitespace-nowrap font-bold">Xe ô tô, BKS:</span>
                    <span class="ml-1 w-32 border-b border-dashed border-black text-center font-normal">{{ $vehicle_number ?? '' }}</span>
                </div>

                <!-- Tải trọng -->
                <div class="flex h-4 items-center justify-center px-1">
                    <span class="whitespace-nowrap font-bold">Tải trọng:</span>
                    <span class="ml-1 w-32 border-b border-dashed border-black text-center font-normal">Xe ô tô dưới 1,5T</span>
                </div>

                <!-- Giờ vào -->
                <div class="flex h-4 items-center gap-1 px-1">
                    <span class="whitespace-nowrap">Giờ vào:</span>
                    <span class="w-10 border-b border-dashed border-black text-center">{{ \Carbon\Carbon::parse($entry_time)->format('H') }}</span>
                    <span class="whitespace-nowrap">h</span>
                    <span class="whitespace-nowrap">/</span>
                    <span class="w-10 border-b border-dashed border-black text-center">{{ \Carbon\Carbon::parse($entry_time)->format('i') }}</span>

                    <span class="whitespace-nowrap">Ngày:</span>
                    <span class="w-32 border-b border-dashed border-black text-center">{{ \Carbon\Carbon::parse($entry_time)->format('d/m/Y') }}</span>
                </div>

                <!-- Giờ ra -->
                <div class="flex h-4 items-center gap-1 px-1">
                    <span class="whitespace-nowrap">Giờ ra:</span>
                    <span class="w-10 border-b border-dashed border-black text-center">{{ \Carbon\Carbon::parse($exit_time)->format('H') }}</span>
                    <span class="whitespace-nowrap">h</span>
                    <span class="whitespace-nowrap">/</span>
                    <span class="w-10 border-b border-dashed border-black text-center">{{ \Carbon\Carbon::parse($exit_time)->format('i') }}</span>

                    <span class="whitespace-nowrap">Ngày:</span>
                    <span class="w-32 border-b border-dashed border-black text-center">{{ \Carbon\Carbon::parse($exit_time)->format('d/m/Y') }}</span>
                </div>

                <!-- Tổng thời gian -->
                <div class="flex h-4 items-center px-1">
                    <span class="whitespace-nowrap">Tổng thời gian khai thác:</span>
                    <span class="ml-1 w-32 border-b border-dashed border-black text-center">{{ $total_hours }} giờ / {{ $remaining_minutes ?? ($total_minutes % 60) }} phút</span>
                </div>

                <!-- Mức phí -->
                <div class="flex h-4 items-center justify-center px-1 font-bold">
                    <span class="whitespace-nowrap">MỨC PHÍ:</span>

                    <span class="mx-1 w-28 border-b border-dashed border-black text-center font-normal">{{ number_format($fee, 0, '.', '.') }}</span>

                    <span class="whitespace-nowrap">Đồng</span>
                </div>
                </div>

                <div class="h-2.5 text-center text-[10px]">(Đã bao gồm thuế GTGT)</div>
            </div> --}}
        </div>
    </body>
</html>