<?php

namespace App\Filament\Resources\RegisterDirectlyResource\Filters;

use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class ListFilterRegisterDirectly extends Filter
{
    public static function make(?string $name = 'date_range'): static
    {
        return parent::make($name);
    }

    protected function setUp(): void
    {
        $this
            ->form([
                        TextInput::make('search')
                            ->label('Tìm kiếm')
                            ->placeholder('Tên, CCCD, biển số xe...'),
                        Select::make('status')
                            ->label('Trạng thái')
                            ->options([
                                'none' => 'Chờ vào',
                                'coming_in' => 'Đang vào',
                                'came_out' => 'Đã ra'
                            ]),
                       
                        DatePicker::make('start_date')
                            ->label('Từ ngày')
                            ->placeholder('Chọn ngày bắt đầu')
                            ->format('d-m-Y')
                            ->default(Carbon::now('Asia/Ho_Chi_Minh')),
                        DatePicker::make('end_date')
                            ->label('Đến ngày')
                            ->placeholder('Chọn ngày kết thúc')
                            ->format('d-m-Y'),
                        Toggle::make('is_priority')
                            ->label('Ưu tiên')
                            ->helperText('Sắp xếp các đăng ký ưu tiên lên đầu')
                            ->inline(false)
                            ->default(true), 
               
            ])->columns(5)
            ->query(function (Builder $query, array $data): Builder {
                return $query
                    ->when(
                        $data['search'],
                        fn (Builder $query, $search): Builder => $query->where(function ($query) use ($search) {
                            return $query->where('name', 'like', "%{$search}%")
                                        ->orWhere('papers', 'like', "%{$search}%")
                                        ->orWhere('bks', 'like', "%{$search}%");
                        }),
                    )
                    ->when(
                        isset($data['status']),
                        function (Builder $query) use ($data) {
                            if ($data['status'] === 'none') {
                                return $query->where(function ($q) {
                                    $q->whereNull('status')
                                      ->orWhere('status', 'none')
                                      ->orWhere('status', '');
                                });
                            }
                            return $query->where('status', $data['status']);
                        }
                    )
                    ->when(
                        $data['start_date'] || $data['end_date'],
                        function (Builder $query) use ($data) {
                            $startDate = $data['start_date'] ? Carbon::parse($data['start_date'], 'Asia/Ho_Chi_Minh')->startOfDay() : null;
                            $endDate = $data['end_date'] ? Carbon::parse($data['end_date'], 'Asia/Ho_Chi_Minh')->endOfDay() : null;
                            
                            $status = $data['status'] ?? null;
                            
                            // Xác định cột ngày để lọc dựa vào trạng thái
                            $dateColumn = 'start_date';
                            if ($status === 'came_out') {
                                // Lọc theo ngày ra
                                return $query->where(function ($q) use ($startDate, $endDate) {
                                    if ($startDate && !$endDate) {
                                        $q->whereDate('actual_date_out', $startDate)
                                          ->orWhere(function ($q2) use ($startDate) {
                                              $q2->whereNull('actual_date_out')->whereDate('updated_at', $startDate);
                                          });
                                    } elseif (!$startDate && $endDate) {
                                        $q->where('actual_date_out', '<=', $endDate)
                                          ->orWhere(function ($q2) use ($endDate) {
                                              $q2->whereNull('actual_date_out')->where('updated_at', '<=', $endDate);
                                          });
                                    } elseif ($startDate && $endDate) {
                                        $q->whereBetween('actual_date_out', [$startDate, $endDate])
                                          ->orWhere(function ($q2) use ($startDate, $endDate) {
                                              $q2->whereNull('actual_date_out')->whereBetween('updated_at', [$startDate, $endDate]);
                                          });
                                    }
                                });
                            } elseif ($status === 'coming_in') {
                                $dateColumn = 'actual_date_in';
                            }

                            if ($startDate && !$endDate) {
                                return $query->whereDate($dateColumn, $startDate);
                            }
                            if (!$startDate && $endDate) {
                                return $query->where($dateColumn, '<=', $endDate);
                            }
                            if ($startDate && $endDate) {
                                return $query->whereBetween($dateColumn, [$startDate, $endDate]);
                            }
                        }
                    );

            })
            ->indicateUsing(function (array $data): array {
                $indicators = [];
                if ($data['search'] ?? null) {
                    $indicators[] = Indicator::make('Tìm kiếm: ' . $data['search'])
                        ->removeField('search');
                }
                if ($data['status'] ?? null) {
                    $statusText = match ($data['status']) {
                        'none' => 'Chờ vào',
                        'coming_in' => 'Đang vào',
                        'came_out' => 'Đã ra',
                        default => $data['status'],
                    };
                    $indicators[] = Indicator::make('Trạng thái: ' . $statusText)
                        ->removeField('status');
                }
                if ($data['start_date'] ?? null) {
                    $indicators[] = Indicator::make('Từ ngày: ' . Carbon::parse($data['start_date'], 'Asia/Ho_Chi_Minh')->format('d/m/Y'))
                        ->removeField('start_date');
                }
         
                if ($data['end_date'] ?? null) {
                    $indicators[] = Indicator::make('Đến ngày: ' . Carbon::parse($data['end_date'], 'Asia/Ho_Chi_Minh')->format('d/m/Y'))
                        ->removeField('end_date');
                }
                
                if (isset($data['is_priority']) && $data['is_priority'] === true) {
                    $indicators[] = Indicator::make('Sắp xếp ưu tiên: Bật')
                        ->removeField('is_priority');
                }
         
                return $indicators;
            });
    }
}