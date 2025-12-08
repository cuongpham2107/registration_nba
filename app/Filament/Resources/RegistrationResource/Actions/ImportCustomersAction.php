<?php

namespace App\Filament\Resources\RegistrationResource\Actions;

use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;

class ImportCustomersAction
{
    public static function make(): Action
    {
        return Action::make('import')
            ->label('Import danh sách khách')
            ->modalDescription(new \Illuminate\Support\HtmlString('File Excel phải đúng định dạng theo mẫu. Vui lòng tải về mẫu trước khi import. <br><a href="/template.xlsx" download class="text-primary-600 hover:underline font-semibold">📥 Tải file mẫu tại đây</a>'))
            ->icon('heroicon-s-arrow-up-on-square')
            ->form([
                FileUpload::make('file')
                    ->label('File import')
                    ->acceptedFileTypes(['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                    ->required()
            ])
            ->action(function (array $data, Set $set, Get $get) {
                try {
                    $file = $data['file'];
                    $filePath = storage_path('app/public/' . $file);

                    // Đọc file Excel
                    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
                    $worksheet = $spreadsheet->getActiveSheet();
                    $rows = $worksheet->toArray();

                    // Bỏ qua dòng header (dòng đầu tiên)
                    array_shift($rows);

                    // Lấy dữ liệu customers hiện tại và lọc bỏ dòng trống
                    $currentCustomers = $get('customers') ?? [];
                    $currentCustomers = array_filter($currentCustomers, function ($customer) {
                        // Giữ lại những dòng có ít nhất 1 field không rỗng
                        return !empty($customer['name']) ||
                            !empty($customer['papers']) ||
                            !empty($customer['type']);
                    });

                    // Chuyển đổi dữ liệu từ Excel
                    $importedCustomers = [];
                    foreach ($rows as $row) {
                        // Bỏ qua dòng trống
                        if (empty(array_filter($row))) {
                            continue;
                        }

                        // Xử lý areas: chuyển từ string "A01,A02" thành array
                        $areas = [];
                        if (!empty($row[3])) {
                            $areas = array_map('trim', explode(',', $row[3]));
                        }

                        $importedCustomers[] = [
                            'name' => $row[0] ?? '',
                            'papers' => $row[1] ?? '',
                            'type' => $row[2] ?? '',
                            'areas' => $areas,
                            'license_plate' => $row[4] ?? '',
                            'note' => $row[5] ?? '',
                        ];
                    }

                    // Gộp dữ liệu cũ và mới
                    $allCustomers = array_merge($currentCustomers, $importedCustomers);

                    // Set lại dữ liệu vào TableRepeater
                    $set('customers', $allCustomers);

                    Notification::make()
                        ->title('Import thành công')
                        ->success()
                        ->body('Đã thêm ' . count($importedCustomers) . ' khách vào danh sách')
                        ->send();

                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Import thất bại')
                        ->danger()
                        ->body('Lỗi: ' . $e->getMessage())
                        ->send();
                }
            });
    }
}
