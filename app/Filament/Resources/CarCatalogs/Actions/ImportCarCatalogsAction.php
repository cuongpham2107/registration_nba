<?php

namespace App\Filament\Resources\CarCatalogs\Actions;

use App\Models\CarCatalog;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportCarCatalogsAction
{
    public static function make(): Action
    {
        return Action::make('importCarCatalogs')
            ->label('Import danh sách biển số')
            ->icon('heroicon-o-arrow-up-on-square')
            ->color('success')
            ->schema([
                FileUpload::make('file')
                    ->label('File import (Excel/CSV không có tiêu đề)')
                    ->helperText('File chỉ cần 1 cột biển số xe, bắt đầu từ dòng 1.')
                    ->acceptedFileTypes([
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'text/csv',
                    ])
                    ->disk('public')
                    ->directory('imports/car-catalogs')
                    ->preserveFilenames()
                    ->required(),
            ])
            ->action(function (array $data) {
                try {
                    $file = $data['file'];
                    $filePath = storage_path('app/public/'.ltrim($file, '/'));

                    if (! file_exists($filePath)) {
                        Notification::make()
                            ->title('Import thất bại')
                            ->danger()
                            ->body('File không tồn tại.')
                            ->send();

                        return;
                    }

                    $spreadsheet = IOFactory::load($filePath);
                    $worksheet = $spreadsheet->getActiveSheet();
                    $rows = $worksheet->toArray();

                    $importedCount = 0;
                    foreach ($rows as $row) {
                        // Skip empty rows
                        if (empty(array_filter($row))) {
                            continue;
                        }

                        $licensePlate = trim($row[0] ?? '');

                        if ($licensePlate === '') {
                            continue;
                        }

                        CarCatalog::updateOrCreate(
                            ['license_plate' => $licensePlate],
                            ['is_paid' => true]
                        );

                        $importedCount++;
                    }

                    Notification::make()
                        ->title('Import thành công')
                        ->success()
                        ->body("Đã import thành công {$importedCount} biển số xe.")
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
