<?php

namespace App\Filament\Resources\Invoices\Filters;

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
            ->schema([
                TextInput::make('search')
                    ->label('Tìm kiếm')
                    ->placeholder('Mã hoá đơn, tên khách, biển số...'),
                Select::make('is_issued')
                    ->label('Trạng thái xuất hoá đơn')
                    ->options([
                        'issued' => 'Đã xuất',
                        'not_issued' => 'Chưa xuất',
                    ]),

                Select::make('payment_method')
                    ->label('Phương thức thanh toán')
                    ->options([
                        'Trả tiền cho bảo vệ' => 'Trả tiền cho bảo vệ',
                        'Đơn vị trả tiền' => 'Đơn vị trả tiền',
                        'Tiền mặt' => 'Tiền mặt',
                        'Chuyển khoản' => 'Chuyển khoản',
                    ]),

                DatePicker::make('start_date')
                    ->label('Từ ngày')
                    ->placeholder('Chọn ngày bắt đầu')
                    ->format('d-m-Y'),

                DatePicker::make('end_date')
                    ->label('Đến ngày')
                    ->placeholder('Chọn ngày kết thúc')
                    ->format('d-m-Y'),
            ])
            ->columns(5)
            ->columnSpanFull()
            ->query(function (Builder $query, array $data): Builder {
                return $query
                    ->when(
                        $data['search'] ?? null,
                        function (Builder $query, string $search): Builder {
                            return $query->where(function (Builder $query) use ($search): Builder {
                                return $query
                                    ->where('invoice_code', 'like', "%{$search}%")
                                    ->orWhere('normalized_license_plate', 'like', "%{$search}%")
                                    ->orWhereHas('registrationEntry', fn (Builder $q) => $q->where('name', 'like', "%{$search}%"));
                            });
                        },
                    )
                    ->when(
                        $data['is_issued'] ?? null,
                        fn (Builder $query, string $state): Builder => match ($state) {
                            'issued' => $query->where('is_issued', true),
                            'not_issued' => $query->where('is_issued', false),
                            default => $query,
                        },
                    )
                    ->when(
                        $data['payment_method'] ?? null,
                        fn (Builder $query, string $method): Builder => $query->where('payment_method', $method),
                    )
                    ->when(
                        ($data['start_date'] ?? null) && ! ($data['end_date'] ?? null),
                        fn (Builder $query) => $query->whereDate('created_at', Carbon::parse($data['start_date'], 'Asia/Ho_Chi_Minh')),
                    )
                    ->when(
                        ($data['start_date'] ?? null) && ($data['end_date'] ?? null),
                        fn (Builder $query) => $query->whereBetween(
                            'created_at',
                            [
                                Carbon::parse($data['start_date'], 'Asia/Ho_Chi_Minh')->startOfDay(),
                                Carbon::parse($data['end_date'], 'Asia/Ho_Chi_Minh')->endOfDay(),
                            ],
                        ),
                    );
            })
            ->indicateUsing(function (array $data): array {
                $indicators = [];

                if ($data['search'] ?? null) {
                    $indicators[] = Indicator::make('Tìm kiếm: '.$data['search'])
                        ->removeField('search');
                }

                if ($data['is_issued'] ?? null) {
                    $text = match ($data['is_issued']) {
                        'issued' => 'Đã xuất',
                        'not_issued' => 'Chưa xuất',
                        default => $data['is_issued'],
                    };

                    $indicators[] = Indicator::make('Xuất HĐ: '.$text)
                        ->removeField('is_issued');
                }

                if ($data['payment_method'] ?? null) {
                    $indicators[] = Indicator::make('PTTT: '.$data['payment_method'])
                        ->removeField('payment_method');
                }

                if ($data['start_date'] ?? null) {
                    $indicators[] = Indicator::make('Từ ngày: '.Carbon::parse($data['start_date'], 'Asia/Ho_Chi_Minh')->format('d/m/Y'))
                        ->removeField('start_date');
                }

                if ($data['end_date'] ?? null) {
                    $indicators[] = Indicator::make('Đến ngày: '.Carbon::parse($data['end_date'], 'Asia/Ho_Chi_Minh')->format('d/m/Y'))
                        ->removeField('end_date');
                }

                return $indicators;
            });
    }
}
