<?php

namespace App\Filament\Exports;

use App\Models\Invoice;
use Carbon\Carbon;
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
                ->label('STT'),
            ExportColumn::make('registerDirectly.id')
                ->label('Mã vé')
                ->formatStateUsing(fn ($state) => "T" . now()->format('m') . "-" . str_pad($state ?? 0, 2, '0', STR_PAD_LEFT)),
            ExportColumn::make('entry_date')
                ->label('Ngày vào')
                ->formatStateUsing(fn ($state, $record): string => $record->registerDirectly?->actual_date_in
                    ? Carbon::parse($record->registerDirectly->actual_date_in)->format('d/m/Y')
                    : ''),
            ExportColumn::make('exit_date')
                ->label('Ngày ra')
                ->formatStateUsing(fn ($state, $record): string => $record->registerDirectly?->actual_date_out
                    ? Carbon::parse($record->registerDirectly->actual_date_out)->format('d/m/Y')
                    : ''),
            ExportColumn::make('invoice_code')
                ->label('Mã hoá đơn'),
            ExportColumn::make('registerDirectly.bks')
                ->label('Biển kiểm soát'),
            ExportColumn::make('vehicle_type')
                ->label('Loại xe/ Trọng tải')
                ->formatStateUsing(fn ($state, $record): string => $record->registerDirectly?->fee?->ticket_code ?? ''),
            ExportColumn::make('entry_time')
                ->label('Giờ vào')
                ->formatStateUsing(fn ($state, $record): string => $record->registerDirectly?->actual_date_in
                    ? Carbon::parse($record->registerDirectly->actual_date_in)->format('H:i')
                    : ''),
            ExportColumn::make('exit_time')
                ->label('Giờ ra')
                ->formatStateUsing(fn ($state, $record): string => $record->registerDirectly?->actual_date_out
                    ? Carbon::parse($record->registerDirectly->actual_date_out)->format('H:i')
                    : ''),
            ExportColumn::make('duration')
                ->label('Thời gian khai thác')
                ->formatStateUsing(function ($state, $record): string {
                    $rd = $record->registerDirectly;
                    if (!$rd) return '';

                    $start = $rd->actual_date_in;
                    $end = $rd->actual_date_out;

                    if (!$start || !$end) return '';

                    $start = Carbon::parse($start);
                    $end = Carbon::parse($end);

                    if ($end->lessThan($start)) return '';

                    $diff = $start->diff($end);
                    $hours = $diff->h + ($diff->d * 24);

                    return sprintf('%02d:%02d', $hours, $diff->i);
                }),
            ExportColumn::make('amount')
                ->label('Phí khai thác')
                ->formatStateUsing(fn (?string $state): string => $state ? number_format((float) $state, 0, ',', '.') . ' VNĐ' : ''),
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
