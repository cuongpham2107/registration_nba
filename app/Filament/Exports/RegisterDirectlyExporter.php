<?php

namespace App\Filament\Exports;

use App\Models\RegisterDirectly;
use App\Models\Area;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class RegisterDirectlyExporter extends Exporter
{
    protected static ?string $model = RegisterDirectly::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('name')
                ->label('Họ và tên'),
            ExportColumn::make('papers')
                ->label('Số CCCD'),
            ExportColumn::make('address')
                ->label('Địa chỉ'),
            ExportColumn::make('bks')
                ->label('Biển kiểm soát'),
            ExportColumn::make('contact_person')
                ->label('Người liên hệ'),
            ExportColumn::make('job')
                ->label('Mục đích công việc'),
            ExportColumn::make('card.card_name')
                ->label('Tên thẻ'),
            ExportColumn::make('start_date')
                ->label('Giờ vào'),
            ExportColumn::make('end_date')
                ->label('Giờ ra dự kiến'),
            ExportColumn::make('actual_date_in')
                ->label('Giờ vào thực tế'),
            ExportColumn::make('actual_date_out')
                ->label('Giờ ra thực tế'),
            ExportColumn::make('areas')
                ->label('Khu vực')
                ->formatStateUsing(function (?string $state): string {
                    if (!$state) return '';
                    
                    $area = Area::where('code', $state)->first();
                    return $area ? $area->name : $state;
                }),
            ExportColumn::make('status')
                ->label('Trạng thái')
                ->formatStateUsing(fn (?string $state): string => match ($state) {
                    'coming_in' => 'Đang vào',
                    'came_out' => 'Đã ra',
                    default => 'Chưa vào',
                }),
            ExportColumn::make('created_at')
                ->label('Ngày tạo'),
            ExportColumn::make('updated_at')
                ->label('Ngày cập nhật'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Xuất danh sách ra vào thành công với ' . number_format($export->successful_rows) . ' ' . str('bản ghi')->plural($export->successful_rows) . '.';

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
