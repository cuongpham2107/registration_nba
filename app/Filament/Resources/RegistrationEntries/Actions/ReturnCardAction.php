<?php

namespace App\Filament\Resources\RegistrationEntries\Actions;

use App\Http\Controllers\DownloadInvoiceController;
use App\Models\CarCatalog;
use App\Models\Invoice;
use App\Models\RegistrationEntry;
use App\Support\FeeCalculator;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\ToggleButtons;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Livewire\Component;

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
            ->modalWidth(Width::Large)
            ->modalIcon('heroicon-o-arrow-uturn-up')
            ->modalHeading(function (RegistrationEntry $record) {
                if ($record->type === 'inspection') {
                    return 'Xe ra: '.$record->license_plate;
                } else {
                    return 'Người ra: '.$record->name.' | CMND: '.$record->papers;
                }
            })
            ->modalDescription(function (RegistrationEntry $record) {
                if ($record->type === 'inspection') {
                    $normalizedBks = Invoice::normalizeLicensePlate($record->license_plate);
                    $isCarCatalog = CarCatalog::where('license_plate', $normalizedBks)->exists();

                    $message = 'Xe ra khỏi khu vực kiểm soát, thẻ sẽ được trả lại hệ thống.';

                    if ($isCarCatalog) {
                        return $message.' Xe có trong danh mục (Miễn phí).';
                    }

                    return $message;
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

                Section::make('Thông tin tạm tính')
                    ->description('Thông tin phí tạm tính dựa trên giờ vào và giờ ra hiện tại. Phí chính xác sẽ được tính khi tạo hóa đơn.')
                    ->visible(function (RegistrationEntry $record): bool {
                        if ($record->type !== 'inspection') {
                            return false;
                        }

                        $normalizedBks = Invoice::normalizeLicensePlate($record->license_plate);

                        return ! CarCatalog::where('license_plate', $normalizedBks)->exists();
                    })
                    ->columnSpanFull()
                    ->columns(2)
                    ->components([
                        TextEntry::make('entry_time_preview')
                            ->label('Giờ vào')
                            ->state(fn (RegistrationEntry $record): string => $record->actual_date_in
                                ? Carbon::parse($record->actual_date_in, 'Asia/Ho_Chi_Minh')->format('d/m/Y H:i')
                                : 'Chưa có giờ vào')
                            ->icon('heroicon-o-calendar')
                            ->columnSpan(1),

                        TextEntry::make('exit_time_preview')
                            ->label('Giờ ra (tạm tính)')
                            ->state(fn (): string => Carbon::now('Asia/Ho_Chi_Minh')->format('d/m/Y H:i'))
                            ->icon('heroicon-o-calendar-days')
                            ->columnSpan(1),

                        TextEntry::make('duration_preview')
                            ->label('Thời gian (tạm tính)')
                            ->state(function (RegistrationEntry $record): string {
                                if (! $record->actual_date_in) {
                                    return 'Chưa có giờ vào';
                                }

                                return FeeCalculator::formatDurationForDisplay(
                                    entryTime: $record->actual_date_in,
                                    exitTime: Carbon::now('Asia/Ho_Chi_Minh'),
                                );
                            })
                            ->icon('heroicon-o-clock')
                            ->columnSpan(1),

                        TextEntry::make('vehicle_type_preview')
                            ->label('Loại xe / Trọng tải')
                            ->state(fn (RegistrationEntry $record): string => $record->guest?->fee?->vehicle_type ?: 'Không xác định')
                            ->icon('heroicon-o-truck')
                            ->columnSpanFull(),

                        TextEntry::make('fee_preview')
                            ->label('Số tiền: ')
                            ->inlineLabel(true)
                            ->alignEnd()
                            ->weight(FontWeight::Bold)
                            ->state(function (RegistrationEntry $record): string {
                                $amount = (int) (FeeCalculator::forRegistrationEntry($record)['total'] ?? 0);

                                return number_format((int) $amount, 0, ',', '.').' đ';
                            })
                            ->columnSpanFull(),
                        ToggleButtons::make('payment_method')
                            ->label('Phương thức thanh toán')
                            ->grouped()
                            // ->boolean(trueLabel: 'Tiền mặt', falseLabel: 'Chuyển khoản'),
                            ->options([
                                'Tiền mặt' => 'Tiền mặt',
                                'Chuyển khoản' => 'Chuyển khoản',
                            ]),
                    ]),

            ])

            ->action(function (array $data, RegistrationEntry $record, Component $livewire): void {
                try {
                    $downloadUrl = null;
                    $feeAmount = null;
                    $printUrl = null;

                    $isCarCatalog = false;

                    DB::transaction(function () use ($data, $record, &$downloadUrl, &$feeAmount, &$printUrl, &$isCarCatalog) {
                        // nếu guest_id có dữ liệu và type là inspection thì không tính tiền
                        $normalizedBks = Invoice::normalizeLicensePlate($record->license_plate);
                        $carCatalogExit = CarCatalog::where('license_plate', $normalizedBks)->first();

                        if ($record->type !== 'inspection') {
                            $feeAmount = 0;
                            $downloadUrl = null;
                        } elseif ($carCatalogExit) {
                            $feeAmount = 0;
                            $downloadUrl = null;
                            $printUrl = null;
                            $isCarCatalog = true;
                        } else {
                            $controller = new DownloadInvoiceController;
                            $filePath = $controller->generateInvoice($record);
                            $printUrl = asset('storage/'.$filePath);

                            $feeAmount = (int) (FeeCalculator::forRegistrationEntry($record)['total'] ?? 0);

                            $existingInvoice = $record->invoice;
                            $invoiceData = [
                                'invoice_code' => Invoice::generateInvoiceCode(),
                                'registration_entry_id' => $record->id,
                                'normalized_license_plate' => $normalizedBks,
                                'amount' => $feeAmount,
                                'is_paid' => true,
                                'paid_at' => now(),
                                'payment_method' => $data['payment_method'],
                                'file_path' => $filePath,
                                'notes' => 'Xe ra khỏi bãi thành công',
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
                        ]);
                    });

                    if ($printUrl) {
                        // Gọi hàm in tự động qua Livewire JS
                        $livewire->js("window.printFile('{$printUrl}')");

                        Notification::make()
                            ->title('Trả thẻ thành công')
                            ->body('Hóa đơn đang được lệnh in...')
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
                        $title = 'Trả thẻ thành công';
                        $body = 'Trả thẻ thành công.';

                        if ($isCarCatalog) {
                            $title = 'Xe đã ra khỏi bãi';
                            $body = 'Xe có trong danh mục (Miễn phí). Đã cập nhật trạng thái xe.';
                        } elseif ($feeAmount === 0) {
                            $body = 'Trả thẻ thành công. Không phát sinh phí nên không tạo hóa đơn.';
                        }

                        Notification::make()
                            ->title($title)
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
