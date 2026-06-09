<?php

namespace App\Filament\Resources\Registrations\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\HtmlString;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportGuestsAction
{
    public static function make(): Action
    {
        return Action::make('import')
            ->label('Import danh sách khách')
            ->modalDescription(new HtmlString('File Excel phải đúng định dạng theo mẫu. Vui lòng tải về mẫu trước khi import. <br><a href="/template.xlsx" download class="text-primary-600 hover:underline font-semibold">📥 Tải file mẫu tại đây</a>'))
            ->icon('heroicon-s-arrow-up-on-square')
            ->form([
                FileUpload::make('file')
                    ->label('File import')
                    ->acceptedFileTypes(['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                    ->disk('public')
                    ->directory('imports')
                    ->preserveFilenames()
                    ->maxSize(10240)
                    ->required(),
            ])
            ->action(function (array $data, Set $set, Get $get) {
                try {
                    $file = $data['file'];
                    // FileUpload sẽ trả về path tương đối trong disk đã chọn.
                    // Với disk 'public' đang trỏ tới storage/app/public.
                    $filePath = storage_path('app/public/'.ltrim($file, '/'));

                    if (! file_exists($filePath)) {
                        Notification::make()
                            ->title('Import thất bại')
                            ->danger()
                            ->body('File không tồn tại hoặc đã bị xóa.')
                            ->send();

                        return;
                    }

                    // Đọc file Excel
                    $spreadsheet = IOFactory::load($filePath);
                    $worksheet = $spreadsheet->getActiveSheet();
                    $rows = $worksheet->toArray();

                    // Luôn bỏ qua dòng đầu tiên (header)
                    if (! empty($rows)) {
                        array_shift($rows);
                    }

                    // Chuyển đổi dữ liệu từ Excel
                    $importedGuests = [];
                    foreach ($rows as $row) {
                        // Bỏ qua dòng trống
                        if (empty(array_filter($row))) {
                            continue;
                        }

                        // Parse areas: hỗ trợ nhiều kiểu phân tách (dấu phẩy, chấm phẩy, xuống dòng)
                        $areasRaw = $row[4] ?? '';
                        if (is_array($areasRaw)) {
                            $areas = $areasRaw;
                        } else {
                            $areasRaw = (string) ($areasRaw ?? '');
                            $areas = preg_split('/[;,\n\r]+/', $areasRaw) ?: [];
                        }
                        $areas = array_values(array_filter(array_map('trim', $areas), static fn ($v) => $v !== ''));

                        $importedGuests[] = [
                            'name' => $row[0] ?? '',           // Column A - Tên khách
                            'papers' => $row[1] ?? '',         // Column B - Số giấy tờ
                            'type' => $row[2] ?? '',           // Column C - Loại giấy tờ
                            'license_plate' => $row[3] ?? '',  // Column D - Biển số
                            'areas' => $areas,                 // Column E - Khu vực (parse thành array)
                            'note' => $row[5] ?? '',           // Column F - Ghi chú
                        ];
                    }

                    // Set dữ liệu import vào Repeater (thay thế hoàn toàn)
                    $set('guests', $importedGuests);

                    Notification::make()
                        ->title('Import thành công')
                        ->success()
                        ->body('Đã thêm '.count($importedGuests).' khách vào danh sách')
                        ->send();

                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Import thất bại')
                        ->danger()
                        ->body('Lỗi: '.$e->getMessage())
                        ->send();
                }
            });
    }
}
