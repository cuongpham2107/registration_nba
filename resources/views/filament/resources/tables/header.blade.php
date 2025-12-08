<div class="filament-table-header px-6 py-3">
    @php
        $records = $this->getTableRecords();
    @endphp

    @if ($records instanceof \Illuminate\Contracts\Pagination\Paginator)
        <div class="text-sm text-end text-gray-700 dark:text-gray-200">
            @if ($records instanceof \Illuminate\Pagination\LengthAwarePaginator)
                Hiển thị {{ number_format($records->firstItem() ?? 0) }} đến {{ number_format($records->lastItem() ?? 0) }} trong tổng số {{ number_format($records->total()) }} kết quả
            @endif
        </div>
    @endif
</div>
