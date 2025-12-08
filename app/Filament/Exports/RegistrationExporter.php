<?php

namespace App\Filament\Exports;

use App\Models\Registration;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class RegistrationExporter extends Exporter
{
    protected static ?string $model = Registration::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('name')
                ->label('Đơn vị khách'),
            ExportColumn::make('purpose')
                ->label('Mục đích'),
            ExportColumn::make('bks')
                ->label('BKS ô tô'),
            ExportColumn::make('start_date')
                ->label('Giờ vào dự kiến'),
            ExportColumn::make('end_date')
                ->label('Giờ ra dự kiến'),
            ExportColumn::make('status')
                ->label('Trạng thái')
                ->formatStateUsing(fn (?string $state): string => $state ? match ($state) {
                    'sent' => 'Đã gửi',
                    'not_yet_sent' => 'Chưa gửi',
                    default => $state,
                } : ''),
            ExportColumn::make('type')
                ->label('Duyệt')
                ->formatStateUsing(fn (?string $state): string => $state ? match ($state) {
                    'browse' => 'Duyệt',
                    'refuse' => 'Từ chối',
                    default => $state,
                } : ''),
            ExportColumn::make('type_date')
                ->label('Ngày duyệt'),
            ExportColumn::make('asset')
                ->label('Tài sản'),
            ExportColumn::make('note')
                ->label('Ghi chú'),
            ExportColumn::make('user.name')
                ->label('Người tạo'),
            ExportColumn::make('approver.name')
                ->label('Người phê duyệt'),
            ExportColumn::make('created_at')
                ->label('Ngày tạo'),
            ExportColumn::make('updated_at')
                ->label('Ngày cập nhật'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Xuất dữ liệu thành công với ' . number_format($export->successful_rows) . ' ' . str('bản ghi')->plural($export->successful_rows) . '.';

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
