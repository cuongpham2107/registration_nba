<?php

namespace App\Filament\Exports;

use App\Models\RegistrationVehicle;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use OpenSpout\Common\Entity\Style\Style;

class RegistrationVehicleExporter extends Exporter
{
    protected static ?string $model = RegistrationVehicle::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('driver_name')
                ->label('Tên tài xế'),
            ExportColumn::make('name')
                ->label('Tên đơn vị'),
            ExportColumn::make('driver_id_card')
                ->label('Số CMND/CCCD'),
            ExportColumn::make('driver_phone')
                ->label('Số điện thoại'),
            ExportColumn::make('vehicle_number')
                ->label('Biển số xe'),
            ExportColumn::make('hawb_number')
                ->label('Số Hawb'),
            ExportColumn::make('expected_in_at')
                ->label('Thời gian vào dự kiến'),
            ExportColumn::make('expected_out_at')
                ->label('Thời gian ra dự kiến'),
            ExportColumn::make('notes')
                ->label('Ghi chú'),
            ExportColumn::make('status')
                ->label('Trạng thái')
                ->formatStateUsing(fn (?string $state): string => $state ? match ($state) {
                    'none' => 'Chưa gửi',
                    'sent' => 'Đã gửi',
                    'approve' => 'Đã phê duyệt',
                    'reject' => 'Bị từ chối',
                    default => $state,
                } : ''),
            ExportColumn::make('approver.name')
                ->label('Người phê duyệt'),
            ExportColumn::make('approved_at')
                ->label('Thời gian phê duyệt'),
            ExportColumn::make('created_at')
                ->label('Ngày tạo'),
            ExportColumn::make('updated_at')
                ->label('Ngày cập nhật'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Xuất dữ liệu xe khai thác thành công với ' . number_format($export->successful_rows) . ' ' . str('bản ghi')->plural($export->successful_rows) . '.';

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



    public function getXlsxCellStyle(): ?Style
    {
        return (new Style())
            ->setFontSize(16)
            ->setFontName('Times New Roman');
    }
}
