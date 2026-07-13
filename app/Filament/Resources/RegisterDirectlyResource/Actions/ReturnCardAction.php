<?php

namespace App\Filament\Resources\RegisterDirectlyResource\Actions;

use App\Http\Controllers\InvoiceFee;
use App\Models\CarCatalog;
use App\Models\Invoice;
use App\Models\RegisterDirectly;
use App\Services\FeeCalculator;
use App\Services\ViettelInvoiceService;
use Carbon\Carbon;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
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
            ->modalIcon('heroicon-o-arrow-uturn-up')
            ->modalWidth(MaxWidth::Large)
            ->modalHeading(function (RegisterDirectly $record) {
                if ($record->type === 'vehicle') {
                    return 'Xe ra: '.$record->bks.' | Họ tên: '.$record->name;
                } else {
                    return 'Người ra: '.$record->name.' | CMND: '.$record->papers;
                }
            })
            ->modalDescription(function (RegisterDirectly $record) {
                if ($record->type === 'vehicle') {
                    return 'Xe ra khỏi khu vực kiểm soát, thẻ sẽ được trả lại hệ thống.';
                } else {
                    return 'Người ra khỏi khu vực kiểm soát, thẻ sẽ được trả lại hệ thống.';
                }
            })
            ->hidden(
                fn (RegisterDirectly $record) => is_null($record->status) ||
                $record->status === 'none' ||
                $record->status === '' ||
                $record->status === 'came_out' ||
                $record->status === 'temporary_out'
            )
            ->form(function (RegisterDirectly $record) {
                if ($record->type !== 'vehicle') {
                    return [
                        ToggleButtons::make('exit_type')
                            ->label('Loại ra')
                            ->options([
                                'came_out' => 'Ra hẳn',
                                'temporary_out' => 'Ra tạm thời',
                            ])
                            ->default('came_out')
                            ->grouped()
                            ->required()
                            ->columnSpanFull(),
                    ];
                }

                if (! config('registration.is_price', false)) {
                    return [];
                }

                $normalizedBks = Invoice::normalizeLicensePlate($record->bks);
                $carCatalog = CarCatalog::where('license_plate', $normalizedBks)->first();
                $isPostpaid = $carCatalog && $carCatalog->billing_type === 'postpaid';

                if ($isPostpaid) {
                    return [
                        Section::make('Thông tin xe trả sau')
                            ->description('Xe này thuộc danh sách thanh toán sau. Không thu phí trực tiếp tại trạm.')
                            ->columnSpanFull()
                            ->schema([
                                Placeholder::make('vehicle_type_preview')
                                    ->label('Loại xe / Trọng tải')
                                    ->content(function (RegisterDirectly $record): HtmlString {
                                        $type = $record->fee?->ticket_code ?: 'Không xác định';

                                        return new HtmlString('<div class="flex items-center gap-2 font-bold">'.svg('heroicon-o-truck', 'w-5 h-5 text-gray-500')->toHtml().$type.'</div>');
                                    })
                                    ->columnSpanFull(),
                                Placeholder::make('note')
                                    ->label('Trạng thái')
                                    ->content(new HtmlString('<div class="flex items-center gap-2 font-bold text-success-600">'.svg('heroicon-o-check-circle', 'w-5 h-5')->toHtml().'Xe thu phí trả sau - Không thu tiền</div>'))
                                    ->columnSpanFull(),
                                Placeholder::make('fee_preview')
                                    ->label('Số tiền: ')
                                    ->content(function (RegisterDirectly $record): HtmlString {
                                        $amount = FeeCalculator::calculateFee($record);
                                        $formatted = number_format($amount, 0, ',', '.').' đ';

                                        return new HtmlString('<div class="flex items-center w-full"><strong class="text-lg text-primary-600">'.$formatted.'</strong></div>');
                                    })
                                    ->columnSpanFull(),
                            ]),
                    ];
                }

                return [
                    Section::make('Thông tin tạm tính')
                        ->description('Thông tin phí tạm tính dựa trên giờ vào và giờ ra hiện tại. Phí chính xác sẽ được tính khi tạo hóa đơn.')
                        ->columnSpanFull()
                        ->columns(2)
                        ->schema([
                            Placeholder::make('entry_time_preview')
                                ->label('Giờ vào')
                                ->content(function (RegisterDirectly $record): HtmlString {
                                    $date = $record->actual_date_in
                                        ? Carbon::parse($record->actual_date_in, 'Asia/Ho_Chi_Minh')->format('d/m/Y H:i')
                                        : 'Chưa có giờ vào';

                                    return new HtmlString('<div class="flex items-center gap-2 font-bold">'.svg('heroicon-o-calendar', 'w-5 h-5 text-gray-500')->toHtml().$date.'</div>');
                                })
                                ->columnSpan(1),

                            Placeholder::make('exit_time_preview')
                                ->label('Giờ ra (tạm tính)')
                                ->content(function (): HtmlString {
                                    $date = Carbon::now('Asia/Ho_Chi_Minh')->format('d/m/Y H:i');

                                    return new HtmlString('<div class="flex items-center gap-2 font-bold">'.svg('heroicon-o-calendar-days', 'w-5 h-5 text-gray-500')->toHtml().$date.'</div>');
                                })
                                ->columnSpan(1),

                            Placeholder::make('duration_preview')
                                ->label('Thời gian (tạm tính)')
                                ->content(function (RegisterDirectly $record): HtmlString {
                                    if (! $record->actual_date_in) {
                                        $duration = 'Chưa có giờ vào';
                                    } else {
                                        $duration = FeeCalculator::formatDurationForDisplay(
                                            entryTime: $record->actual_date_in,
                                            exitTime: Carbon::now('Asia/Ho_Chi_Minh'),
                                        );
                                    }

                                    return new HtmlString('<div class="flex items-center gap-2 font-bold">'.svg('heroicon-o-clock', 'w-5 h-5 text-gray-500')->toHtml().$duration.'</div>');
                                })
                                ->columnSpanFull(),

                            Placeholder::make('vehicle_type_preview')
                                ->label('Loại xe / Trọng tải')
                                ->content(function (RegisterDirectly $record): HtmlString {
                                    $type = $record->fee?->ticket_code ?: 'Không xác định';

                                    return new HtmlString('<div class="flex items-center gap-2 font-bold">'.svg('heroicon-o-truck', 'w-5 h-5 text-gray-500')->toHtml().$type.'</div>');
                                })
                                ->columnSpanFull(),

                            Placeholder::make('fee_preview')
                                ->label('Số tiền: ')
                                ->content(function (RegisterDirectly $record): HtmlString {
                                    $amount = FeeCalculator::calculateFee($record);
                                    $formatted = number_format($amount, 0, ',', '.').' đ';

                                    return new HtmlString('<div class="flex items-center w-full"><strong class="text-lg text-primary-600">'.$formatted.'</strong></div>');
                                })
                                ->columnSpanFull(),
                            ToggleButtons::make('payment_method')
                                ->label('Phương thức thanh toán')
                                ->grouped()
                                ->options([
                                    'Tiền mặt' => 'Tiền mặt',
                                    'Chuyển khoản' => 'Chuyển khoản',
                                ])
                                ->default('Tiền mặt')
                                ->required()
                                ->columnSpanFull(),
                        ]),
                ];
            })
            ->action(function (RegisterDirectly $record, array $data, Component $livewire): void {
                try {
                    $exitType = $data['exit_type'] ?? 'came_out';

                    if ($exitType === 'temporary_out') {
                        DB::transaction(function () use ($record) {
                            $record->cards()->update(['status' => 'inactive']);

                            if ($record->relationLoaded('registrationVehicle') || $record->registrationVehicle) {
                                $registrationVehicle = $record->registrationVehicle;
                                if ($registrationVehicle instanceof Model) {
                                    $registrationVehicle->status = 'exited';
                                    $registrationVehicle->save();
                                }
                            }

                            $record->update([
                                'status' => 'temporary_out',
                                'actual_date_out' => Carbon::now('Asia/Ho_Chi_Minh'),
                            ]);
                        });

                        Notification::make()
                            ->title('Thành công')
                            ->body($record->type === 'vehicle' ? 'Xe ra tạm thời, thẻ đã được trả lại.' : 'Khách ra tạm thời, thẻ đã được trả lại.')
                            ->success()
                            ->send();

                        return;
                    }

                    $shouldDownloadInvoice = true;
                    $createdInvoice = null;

                    DB::transaction(function () use ($record, $data, &$shouldDownloadInvoice, &$createdInvoice) {
                        if ($record->type === 'vehicle' && config('registration.is_price', false)) {
                            // Chuẩn hóa biển số và tìm car_catalog
                            $normalizedBks = Invoice::normalizeLicensePlate($record->bks);
                            $carCatalog = CarCatalog::where('license_plate', $normalizedBks)->first();
                            $isPostpaid = $carCatalog && $carCatalog->billing_type === 'postpaid';

                            // Tính phí
                            $feeAmount = FeeCalculator::calculateFee($record);

                            // Xác định trạng thái thanh toán dựa trên billing_type
                            $isPaid = false;
                            $paidAt = null;
                            $paymentMethod = $data['payment_method'] ?? null;

                            if ($feeAmount == 0 || $isPostpaid) {
                                $shouldDownloadInvoice = false;
                            }

                            if ($carCatalog === null || ($carCatalog && $carCatalog->billing_type === 'prepaid')) {
                                // Xe chưa đăng ký hoặc thanh toán trước → đã thanh toán
                                $isPaid = true;
                                $paidAt = now();
                                // Nếu đã chọn trong form thì dùng, nếu không dùng mặc định
                                $paymentMethod = $paymentMethod ?: 'Tiền mặt';
                            } elseif ($isPostpaid) {
                                // Xe thanh toán sau → chưa thanh toán, không cần paymentMethod
                                $isPaid = false;
                                $paidAt = null;
                                $paymentMethod = null;
                            }

                            $invoiceCode = Invoice::generateInvoiceCode();

                            // Gán tạm thời actual_date_out để render HTML chính xác
                            $record->actual_date_out = Carbon::now('Asia/Ho_Chi_Minh');

                            // Lưu trữ hóa đơn dưới dạng HTML file
                            $invoiceHtml = (new InvoiceFee)->getInvoiceHtml($record);
                            $fileName = 'invoice_'.$invoiceCode.'_'.time().'.html';
                            $filePath = 'invoices/'.$fileName;
                            Storage::disk('public')->put($filePath, $invoiceHtml);

                            // Tạo invoice record chính thức
                            $existingInvoice = $record->invoice;
                            $invoiceData = [
                                'invoice_code' => $invoiceCode,
                                'register_directly_id' => $record->id,
                                'normalized_license_plate' => $normalizedBks,
                                'car_catalog_id' => $carCatalog?->id,
                                'amount' => $feeAmount,
                                'is_paid' => $isPaid,
                                'paid_at' => $paidAt,
                                'payment_method' => $paymentMethod,
                                'file_path' => $filePath,
                                'notes' => 'Tạo khi xe ra khỏi bãi thành công',
                                'is_invoiced' => false,
                                'invoiced_at' => null,
                            ];

                            if ($existingInvoice) {
                                $existingInvoice->update($invoiceData);
                                $createdInvoice = $existingInvoice;
                            } else {
                                $createdInvoice = Invoice::create($invoiceData);
                            }
                        }

                        // Update all cards status to inactive
                        $record->cards()->update(['status' => 'inactive']);

                        // Update registration vehicle status (guard and update safely)
                        if ($record->relationLoaded('registrationVehicle') || $record->registrationVehicle) {
                            $registrationVehicle = $record->registrationVehicle;
                            if ($registrationVehicle instanceof Model) {
                                $registrationVehicle->status = 'exited';
                                $registrationVehicle->save();
                            }
                        }

                        // Update record
                        $record->update([
                            'status' => 'came_out',
                            'actual_date_out' => Carbon::now('Asia/Ho_Chi_Minh'),
                        ]);
                    });

                    // Gọi Viettel Invoice API để xuất hóa đơn điện tử
                    if ($createdInvoice && $shouldDownloadInvoice) {
                        try {
                            $viettelResult = app(ViettelInvoiceService::class)->createInvoice($record, $createdInvoice);
                            $codeOfTax = $viettelResult['result']['codeOfTax'] ?? null;

                            Notification::make()
                                ->title('Hóa đơn điện tử đã được tạo')
                                ->body('Mã số thuế: '.$codeOfTax.' | Xe: '.($record->license_plate ?: $record->bks))
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            // Không block luồng trả thẻ nếu API Viettel lỗi
                            \Illuminate\Support\Facades\Log::error('Viettel invoice creation failed', [
                                'invoice_id' => $createdInvoice->id,
                                'error' => $e->getMessage(),
                            ]);

                            Notification::make()
                                ->title('Lỗi tạo hóa đơn điện tử')
                                ->body('Không thể xuất hóa đơn Viettel: '.$e->getMessage())
                                ->warning()
                                ->send();
                        }
                    }

                    $notification = Notification::make()
                        ->title('Trả thẻ thành công')
                        ->success();

                    if ($record->type === 'vehicle') {
                        if (config('registration.is_price', false)) {
                            if ($shouldDownloadInvoice) {
                                // Tạo URL download cho PDF invoice - TODO: verify route exists
                                $downloadUrl = route('invoice.download', [
                                    'registerDirectly' => $record->id,
                                ]);

                                $livewire->js("window.printFile('{$downloadUrl}')");

                                $notification->body('Hóa đơn đã được tạo. Nhấn để tải về.')
                                    ->actions([
                                        \Filament\Notifications\Actions\Action::make('download_invoice')
                                            ->label('Tải hóa đơn')
                                            ->icon('heroicon-o-arrow-down-tray')
                                            ->url($downloadUrl)
                                            ->openUrlInNewTab(),
                                    ])
                                    ->duration(5000)
                                    ->persistent();
                            } else {
                                $notification->body('Xe ra thành công. Không cần tải hóa đơn.');
                            }
                        } else {
                            $notification->body('Xe ra thành công.');
                        }
                    } else {
                        $notification->body('Khách đã ra khỏi khu vực kiểm soát.');
                    }

                    $notification->send();
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
