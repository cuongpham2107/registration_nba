<?php

namespace App\Filament\Exports;

use App\Models\Invoice;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class InvoiceExporter extends Exporter
{
    protected static ?string $model = Invoice::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('invoice_code')
                ->label('Mã hóa đơn'),
            ExportColumn::make('registerDirectly.name')
                ->label('Tên khách hàng'),
            ExportColumn::make('normalized_license_plate')
                ->label('Biển số xe'),
            ExportColumn::make('registerDirectly.fee.vehicle_type')
                ->label('Loại vé / Trọng tải'),
            ExportColumn::make('registerDirectly.actual_date_in')
                ->label('Giờ vào'),
            ExportColumn::make('registerDirectly.actual_date_out')
                ->label('Giờ ra'),
            ExportColumn::make('carCatalog.unit.name')
                ->label('Đơn vị'),
            ExportColumn::make('amount')
                ->label('Số tiền')
                ->formatStateUsing(fn (?string $state): string => $state ? number_format((float) $state, 0, ',', '.') . ' VNĐ' : ''),
            ExportColumn::make('is_paid')
                ->label('Đã thanh toán')
                ->formatStateUsing(fn (?bool $state): string => $state ? 'Đã thanh toán' : 'Chưa thanh toán'),
            ExportColumn::make('payment_method')
                ->label('PT thanh toán'),
            ExportColumn::make('paid_at')
                ->label('Thời gian thanh toán'),
            ExportColumn::make('is_invoiced')
                ->label('Đã xuất hóa đơn')
                ->formatStateUsing(fn (?bool $state): string => $state ? 'Đã xuất' : 'Chưa xuất'),
            ExportColumn::make('invoiced_at')
                ->label('Thời gian xuất hóa đơn'),
            ExportColumn::make('notes')
                ->label('Ghi chú'),
            ExportColumn::make('created_at')
                ->label('Ngày tạo'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Xuất hóa đơn thành công với ' . number_format($export->successful_rows) . ' ' . str('bản ghi')->plural($export->successful_rows) . '.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('bản ghi')->plural($failedRowsCount) . ' xuất thất bại.';
        }

        return $body;
    }

    public function getFormats(): array
    {
        return [
            \Filament\Actions\Exports\Enums\ExportFormat::Xlsx,
        ];
    }
}
