<?php

namespace App\Filament\Resources\ViolationReportResource\Pages;

use App\Filament\Resources\ViolationReportResource;
use App\Services\GeminiOcrService;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class ListViolationReports extends ListRecords
{
    protected static string $resource = ViolationReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Actions\Action::make('scanViolationReport')
                ->label('Quét biên bản')
                ->icon('heroicon-o-document-magnifying-glass')
                ->color('success')
                ->modalHeading('Tải ảnh biên bản lên')
                ->modalSubmitActionLabel('Xử lý')
                ->form([
                    FileUpload::make('image')
                        ->label('Chọn ảnh biên bản')
                        ->image()
                        ->required()
                        ->maxSize(10240),
                ])
                ->action(function (array $data) {
                    $path = $data['image'] ?? null;

                    if (empty($path)) {
                        Notification::make()
                            ->title('Vui lòng chọn ảnh')
                            ->danger()
                            ->send();

                        return;
                    }

                    $fullPath = Storage::disk('public')->path($path);
                    $mimeType = mime_content_type($fullPath);
                    $base64 = base64_encode(file_get_contents($fullPath));

                    try {
                        $service = app(GeminiOcrService::class);
                        $result = $service->analyze($base64, $mimeType);

                        if ($result === null) {
                            Notification::make()
                                ->title('Không thể đọc được nội dung từ ảnh, vui lòng thử lại')
                                ->danger()
                                ->send();

                            return;
                        }

                        $recordedAt = null;
                        if (! empty($result['recorded_at'])) {
                            $parsed = Carbon::createFromFormat('H:i d/m/Y', $result['recorded_at']);
                            if ($parsed) {
                                $recordedAt = $parsed;
                            }
                        }

                        \App\Models\ViolationReport::create([
                            'recorded_at' => $recordedAt ?? now(),
                            'location' => $result['location'] ?? '',
                            'reporters' => $result['reporters'] ?? [],
                            'witnesses' => $result['witnesses'] ?? [],
                            'violators' => $result['violators'] ?? [],
                            'target' => $result['target'] ?? null,
                            'violation_content' => $result['violation_content'] ?? '',
                            'violation_count' => $result['violation_count'] ?? null,
                            'violator_attitude' => $result['violator_attitude'] ?? null,
                            'resolution_direction' => $result['resolution_direction'] ?? null,
                            'image' => $path,
                        ]);

                        Notification::make()
                            ->title('Tạo biên bản thành công')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Lỗi: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
