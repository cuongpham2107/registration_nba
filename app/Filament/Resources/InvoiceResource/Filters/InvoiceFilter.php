<?php

namespace App\Filament\Resources\InvoiceResource\Filters;

use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class InvoiceFilter extends Filter
{
    public static function make(?string $name = 'invoice_filter'): static
    {
        return parent::make($name);
    }

    protected function setUp(): void
    {
        $this
            ->form([
                TextInput::make('search')
                    ->label('Tìm kiếm')
                    ->placeholder('Mã hóa đơn, tên khách hàng, biển số...')
                    ->columnSpan(1),

                Select::make('payment_status')
                    ->label('Trạng thái thanh toán')
                    ->options([
                        'paid' => 'Đã thanh toán',
                        'unpaid' => 'Chưa thanh toán',
                    ])
                    ->placeholder('Tất cả')
                    ->columnSpan(1),

                Select::make('invoice_status')
                    ->label('Trạng thái xuất HĐ')
                    ->options([
                        'invoiced' => 'Đã xuất',
                        'not_invoiced' => 'Chưa xuất',
                    ])
                    ->placeholder('Tất cả')
                    ->columnSpan(1),

                DatePicker::make('created_from')
                    ->label('Từ ngày')
                    ->placeholder('Chọn ngày bắt đầu')
                    ->native(true)
                    ->prefixIcon('heroicon-o-calendar')
                    ->format('d/m/Y')
                    ->default(Carbon::now('Asia/Ho_Chi_Minh'))
                    ->columnSpan(1),

                DatePicker::make('created_to')
                    ->label('Đến ngày')
                    ->placeholder('Chọn ngày kết thúc')
                    ->native(true)
                    ->prefixIcon('heroicon-o-calendar')
                    ->format('d/m/Y')
                    ->columnSpan(1),
            ])
            ->columns(5)
            ->columnSpanFull()
            ->query(function (Builder $query, array $data): Builder {
                return $query
                    ->when(
                        $data['search'] ?? null,
                        fn (Builder $query, $search): Builder => $query->where(function ($query) use ($search) {
                            return $query->where('invoice_code', 'like', "%{$search}%")
                                ->orWhere('normalized_license_plate', 'like', "%{$search}%")
                                ->orWhereHas('registerDirectly', function ($query) use ($search) {
                                    $query->where('name', 'like', "%{$search}%")
                                        ->orWhere('bks', 'like', "%{$search}%");
                                });
                        })
                    )
                    ->when(
                        $data['payment_status'] ?? null,
                        function (Builder $query, $status) {
                            if ($status === 'paid') {
                                return $query->where('is_paid', true);
                            } elseif ($status === 'unpaid') {
                                return $query->where('is_paid', false);
                            }

                            return $query;
                        }
                    )
                    ->when(
                        $data['invoice_status'] ?? null,
                        function (Builder $query, $status) {
                            if ($status === 'invoiced') {
                                return $query->where('is_invoiced', true);
                            } elseif ($status === 'not_invoiced') {
                                return $query->where('is_invoiced', false);
                            }

                            return $query;
                        }
                    )
                    ->when(
                        $data['payment_method'] ?? null,
                        fn (Builder $query, $method): Builder => $query->where('payment_method', $method)
                    )
                    ->when(
                        $data['created_from'] ?? null,
                        fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', Carbon::parse($date))
                    )
                    ->when(
                        $data['created_to'] ?? null,
                        fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', Carbon::parse($date))
                    )
                    ->when(
                        $data['amount_from'] ?? null,
                        fn (Builder $query, $amount): Builder => $query->where('amount', '>=', $amount)
                    )
                    ->when(
                        $data['amount_to'] ?? null,
                        fn (Builder $query, $amount): Builder => $query->where('amount', '<=', $amount)
                    )
                    ->when(
                        $data['unit_name'] ?? null,
                        fn (Builder $query, $unitId): Builder => $query->whereHas('carCatalog.unit', function ($query) use ($unitId) {
                            $query->where('id', $unitId);
                        })
                    );
            })
            ->indicateUsing(function (array $data): array {
                $indicators = [];

                if ($data['search'] ?? null) {
                    $indicators[] = Indicator::make('Tìm kiếm: '.$data['search'])
                        ->removeField('search');
                }

                if ($data['payment_status'] ?? null) {
                    $statusText = $data['payment_status'] === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán';
                    $indicators[] = Indicator::make('Trạng thái: '.$statusText)
                        ->removeField('payment_status');
                }

                if ($data['invoice_status'] ?? null) {
                    $statusText = $data['invoice_status'] === 'invoiced' ? 'Đã xuất' : 'Chưa xuất';
                    $indicators[] = Indicator::make('Xuất HĐ: '.$statusText)
                        ->removeField('invoice_status');
                }

                if ($data['payment_method'] ?? null) {
                    $indicators[] = Indicator::make('Phương thức: '.$data['payment_method'])
                        ->removeField('payment_method');
                }

                if ($data['created_from'] ?? null) {
                    $indicators[] = Indicator::make('Từ ngày: '.Carbon::parse($data['created_from'])->format('d/m/Y'))
                        ->removeField('created_from');
                }

                if ($data['created_to'] ?? null) {
                    $indicators[] = Indicator::make('Đến ngày: '.Carbon::parse($data['created_to'])->format('d/m/Y'))
                        ->removeField('created_to');
                }

                if ($data['amount_from'] ?? null) {
                    $indicators[] = Indicator::make('Từ: '.number_format($data['amount_from']).'₫')
                        ->removeField('amount_from');
                }

                if ($data['amount_to'] ?? null) {
                    $indicators[] = Indicator::make('Đến: '.number_format($data['amount_to']).'₫')
                        ->removeField('amount_to');
                }

                return $indicators;
            });
    }
}
