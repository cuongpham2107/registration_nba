<?php

namespace App\Filament\Resources\RegistrationEntries\Actions;

use App\Http\Controllers\DownloadInvoiceController;
use App\Models\Invoice;
use App\Models\RegistrationEntry;
use App\Support\FeeCalculator;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

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
            ->modalHeading(function (RegistrationEntry $record) {
                if ($record->type === 'inspection') {
                    return 'Xe ra: '.$record->license_plate.' | Họ tên: '.$record->name;
                } else {
                    return 'Người ra: '.$record->name.' | CMND: '.$record->papers;
                }
            })
            ->modalDescription(function (RegistrationEntry $record) {
                if ($record->type === 'inspection') {
                    return 'Xe ra khỏi khu vực kiểm soát, thẻ sẽ được trả lại hệ thống.';
                } else {
                    return 'Người ra khỏi khu vực kiểm soát, thẻ sẽ được trả lại hệ thống.';
                }
            })
            ->hidden(
                fn (RegistrationEntry $record) => is_null($record->status) ||
                $record->status === 'none' ||
                $record->status === 'exited'
            )
            ->form([
                Toggle::make('is_money')
                    ->label('Trả tiền cho bảo vệ')
                    ->default(false)
                    ->inline(false)
                    ->columnSpanFull(),

            ])

            ->action(function (array $data, RegistrationEntry $record): void {
                try {
                    $downloadUrl = null;
                    $feeAmount = null;

                    DB::transaction(function () use ($data, $record, &$downloadUrl, &$feeAmount) {
                        // nếu guest_id có dữ liệu và type là inspection thì không tính tiền
                        if ($data['is_money'] === false) {
                            $feeAmount = 0;
                            $downloadUrl = null;
                        } else {
                            $controller = new DownloadInvoiceController;
                            $filePath = $controller->generateInvoice($record);

                            $normalizedBks = Invoice::normalizeLicensePlate($record->license_plate);
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
                                'notes' => $record->type === 'working' ? 'Khách ra vào trả thẻ ra thành công' : 'Xe ra khỏi bãi thành công',
                            ];

                            if ($existingInvoice) {
                                $existingInvoice->update($invoiceData);
                            } else {
                                Invoice::create($invoiceData);
                            }

                            // Signed URL để tải hóa đơn
                            $downloadUrl = URL::signedRoute(
                                name: 'invoice.download',
                                parameters: ['registrationEntry' => $record->id],
                                absolute: true,
                            );
                        }

                        if ($record->relationLoaded('card') || $record->card) {
                            $card = $record->card;
                            if ($card instanceof Model) {
                                $card->update(['status' => 'inactive']);
                            }
                        }

                        if ($record->relationLoaded('registration') || $record->registration) {
                            $registration = $record->registration;
                            if ($registration instanceof Model) {
                                $registration->status = 'exited';
                                $registration->save();
                            }
                        }

                        $record->update([
                            'status' => 'exited',
                            'actual_date_out' => Carbon::now('Asia/Ho_Chi_Minh'),
                            'card_id' => null,
                        ]);
                    });

                    if ($downloadUrl) {
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
                    } else {
                        $body = 'Trả thẻ thành công.';

                        if ($feeAmount === 0) {
                            $body = 'Trả thẻ thành công. Không phát sinh phí nên không tạo hóa đơn.';
                        }

                        Notification::make()
                            ->title('Trả thẻ thành công')
                            ->body($body)
                            ->success()
                            ->duration(4000)
                            ->send();
                    }
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
