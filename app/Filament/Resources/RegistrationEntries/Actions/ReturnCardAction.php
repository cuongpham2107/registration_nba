<?php

namespace App\Filament\Resources\RegistrationEntries\Actions;

use App\Http\Controllers\DownloadInvoiceController;
use App\Models\Invoice;
use App\Models\RegistrationEntry;
use App\Support\FeeCalculator;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Joaopaulolndev\FilamentPdfViewer\Forms\Components\PdfViewerField;

class ReturnCardAction
{
    public static function make(): Action
    {
        return Action::make('return-card')
            ->label('Ra')
            ->color('danger')
            ->button()
            ->icon('heroicon-o-arrow-uturn-up')
            ->requiresConfirmation()
            ->modalIcon('heroicon-o-arrow-uturn-up')
            ->modalWidth('4xl')
            ->modalHeading(function (RegistrationEntry $record) {
                if ($record->type === 'vehicle') {
                    return 'Xe ra: '.$record->bks.' | Họ tên: '.$record->name;
                } else {
                    return 'Người ra: '.$record->name.' | CMND: '.$record->papers;
                }
            })
            ->modalDescription(function (RegistrationEntry $record) {
                if ($record->type === 'vehicle') {
                    return 'Xe ra khỏi khu vực kiểm soát, thẻ sẽ được trả lại hệ thống.';
                } else {
                    return 'Người ra khỏi khu vực kiểm soát, thẻ sẽ được trả lại hệ thống.';
                }
            }
            )
            ->hidden(
                fn (RegistrationEntry $record) => is_null($record->status) ||
                $record->status === 'none' ||
                $record->status === '' ||
                $record->status === 'came_out'
            )
            ->form([
                PdfViewerField::make('invoice_pdf')
                    ->label('Hóa đơn')
                    ->minHeight('40svh')
                    ->fileUrl(function (RegistrationEntry $record) {
                        $controller = new DownloadInvoiceController;
                        $filePath = $controller->generateInvoice($record);

                        return Storage::url($filePath);
                    }) // Set the file url if you are getting a pdf without database
                    ->columnSpanFull(),
            ])

            ->action(function (RegistrationEntry $record): void {
                try {
                    DB::transaction(function () use ($record) {
                        $controller = new DownloadInvoiceController;
                        $filePath = $controller->generateInvoice($record);

                        $normalizedBks = Invoice::normalizeLicensePlate($record->bks);

                        $feeAmount = (int) (FeeCalculator::forRegistrationEntry($record)['total'] ?? 0);

                        $existingInvoice = $record->invoice;
                        $invoiceData = [
                            'invoice_code' => Invoice::generateInvoiceCode(),
                            'registration_entry_id' => $record->id,
                            'normalized_license_plate' => $normalizedBks,
                            'amount' => $feeAmount,
                            'is_paid' => true,
                            'paid_at' => now(),
                            'payment_method' => 'Trả tiền cho bảo vệ',
                            'file_path' => $filePath,
                            'notes' => 'Tạo khi xe ra khỏi bãi thành công',
                        ];

                        if ($existingInvoice) {
                            $existingInvoice->update($invoiceData);
                        } else {
                            Invoice::create($invoiceData);
                        }

                        if ($record->relationLoaded('card') || $record->card) {
                            $card = $record->card;
                            if ($card instanceof Model) {
                                $card->update(['status' => 'inactive']);
                            }
                        }

                        if ($record->relationLoaded('registrationVehicle') || $record->registrationVehicle) {
                            $registrationVehicle = $record->registrationVehicle;
                            if ($registrationVehicle instanceof Model) {
                                $registrationVehicle->status = 'exited';
                                $registrationVehicle->save();
                            }
                        }

                        $record->update([
                            'status' => 'came_out',
                            'actual_date_out' => Carbon::now('Asia/Ho_Chi_Minh'),
                            'card_id' => null,
                        ]);
                    });

                    // Use a relative signed URL so APP_URL host mismatch doesn't break signature.
                    $downloadUrl = URL::signedRoute(
                        name: 'invoice.download',
                        parameters: ['registrationEntry' => $record->id],
                        absolute: true,
                    );

                    Notification::make()
                        ->title('Trả thẻ thành công')
                        ->body('Hóa đơn đã được tạo. Nhấn để tải về.')
                        ->success()
                        ->actions([
                            Action::make('download_invoice')
                                ->label('Tải hóa đơn')
                                ->icon('heroicon-o-arrow-down-tray')
                                ->url($downloadUrl)
                                ->openUrlInNewTab(),
                        ])
                        ->duration(5000)
                        ->persistent()
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Lỗi')
                        ->body('Có lỗi xảy ra khi trả thẻ: '.$e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
