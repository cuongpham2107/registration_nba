<?php

namespace App\Filament\Resources\RegisterDirectlyResource\Actions;

use App\Models\RegisterDirectly;
use App\Models\CarCatalog;
use App\Models\Invoice;
use App\Http\Controllers\DownloadInvoiceController;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\Facades\DB;
use Joaopaulolndev\FilamentPdfViewer\Forms\Components\PdfViewerField;
use Illuminate\Support\Facades\Storage;
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
            ->modalHeading(function (RegisterDirectly $record) {
                    if($record->type === 'vehicle') {
                        return 'Xe ra: ' . $record->bks. ' | Họ tên: ' . $record->name;
                    } else {
                        return 'Người ra: ' . $record->name . ' | CMND: ' . $record->papers;
                    }
                })
            ->modalDescription(function (RegisterDirectly $record) {
                    if($record->type === 'vehicle') {
                        return 'Xe ra khỏi khu vực kiểm soát, thẻ sẽ được trả lại hệ thống.';
                    } else {
                        return 'Người ra khỏi khu vực kiểm soát, thẻ sẽ được trả lại hệ thống.';
                    }
                }
            )

            ->hidden(
                fn(RegisterDirectly $record) =>
                is_null($record->status) ||
                $record->status === 'none' ||
                $record->status === '' ||
                $record->status === 'came_out'
            )
            ->form([
                Placeholder::make('vehicle_info')
                    ->label('Thông tin xe')
                    ->content(function (RegisterDirectly $record) {
                        // Chuẩn hóa biển số từ record
                        $normalizedBks = Invoice::normalizeLicensePlate($record->bks);
                        
                        // Tìm kiếm trong car_catalogs
                        $carCatalog = CarCatalog::where('license_plate', $normalizedBks)->first();
                        
                        if ($carCatalog) {
                            $billingTypeText = match($carCatalog->billing_type) {
                                'prepaid' => 'Thu trước',
                                'postpaid' => 'Thu sau',
                                default => ucfirst($carCatalog->billing_type)
                            };
                            
                            return "Xe đã đăng ký - Loại thanh toán: " . $billingTypeText;
                        }
                        
                        return "Xe chưa đăng ký trong hệ thống";
                    })
                    ->columnSpanFull(),

                PdfViewerField::make('invoice_pdf')
                    ->label('Hóa đơn phí khai thác')
                    ->minHeight('45svh')
                    ->fileUrl(function (RegisterDirectly $record) {
                        // Chỉ tạo PDF để preview, không lưu invoice record
                        $controller = new DownloadInvoiceController();
                        $filePath = $controller->generateInvoice($record);
                        
                        return Storage::url($filePath);
                    })
                    ->columnSpanFull()
            ])
            ->action(function (RegisterDirectly $record): void {
                try {
                    DB::transaction(function () use ($record) {
                        // Tạo invoice chính thức khi trả thẻ thành công
                        $controller = new DownloadInvoiceController();
                        $filePath = $controller->generateInvoice($record);
                        
                        // Chuẩn hóa biển số và tìm car_catalog
                        $normalizedBks = Invoice::normalizeLicensePlate($record->bks);
                        $carCatalog = CarCatalog::where('license_plate', $normalizedBks)->first();
                        
                        // Tính phí
                        $feeAmount = $controller->calculateFeePublic($record);
                        
                        // Xác định trạng thái thanh toán dựa trên billing_type
                        $isPaid = false;
                        $paidAt = null;
                        $paymentMethod = null;
                        
                        if ($carCatalog === null || ($carCatalog && $carCatalog->billing_type === 'prepaid')) {
                            // Xe chưa đăng ký hoặc thanh toán trước → đã thanh toán
                            $isPaid = true;
                            $paidAt = now();
                            $paymentMethod = "Trả tiền cho bảo vệ";
                        } else if ($carCatalog && $carCatalog->billing_type === 'postpaid') {
                            // Xe thanh toán sau → chưa thanh toán
                            $isPaid = false;
                            $paidAt = null;
                            $paymentMethod = null;
                        }
                        
                        // Tạo invoice record chính thức
                        $existingInvoice = $record->invoice;
                        $invoiceData = [
                            'invoice_code' => Invoice::generateInvoiceCode(),
                            'register_directly_id' => $record->id,
                            'normalized_license_plate' => $normalizedBks,
                            'car_catalog_id' => $carCatalog?->id,
                            'amount' => $feeAmount,
                            'is_paid' => $isPaid,
                            'paid_at' => $paidAt,
                            'payment_method' => $paymentMethod,
                            'file_path' => $filePath,
                            'notes' => 'Tạo khi xe ra khỏi bãi thành công',
                        ];
                        
                        if ($existingInvoice) {
                            $existingInvoice->update($invoiceData);
                        } else {
                            Invoice::create($invoiceData);
                        }
                       
                        // Update card status to inactive
                        $record->card->update(['status' => 'inactive']);
                        
                        // Update registration vehicle status (guard and update safely)
                        if ($record->relationLoaded('registrationVehicle') || $record->registrationVehicle) {
                            $registrationVehicle = $record->registrationVehicle;
                            if ($registrationVehicle instanceof \Illuminate\Database\Eloquent\Model) {
                                $registrationVehicle->status = 'exited';
                                $registrationVehicle->save();
                            }
                        }
                        
                        // Update record - remove card_id and set status to came_out
                        $record->update([
                            'status' => 'came_out',
                            'actual_date_out' => Carbon::now('Asia/Ho_Chi_Minh'),
                            'card_id' => null,
                        ]);
                    });

                    // Tạo URL download cho PDF invoice
                    $downloadUrl = route('invoice.download', [
                        'registerDirectly' => $record->id
                    ]);

                    Notification::make()
                        ->title('Trả thẻ thành công')
                        ->body('Hóa đơn đã được tạo. Nhấn để tải về.')
                        ->success()
                        ->actions([
                            \Filament\Notifications\Actions\Action::make('download_invoice')
                                ->label('Tải hóa đơn')
                                ->icon('heroicon-o-arrow-down-tray')
                                ->url($downloadUrl)
                                ->openUrlInNewTab()
                        ])
                        ->duration(5000)
                        ->persistent()
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Lỗi')
                        ->body('Có lỗi xảy ra khi trả thẻ: ' . $e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
