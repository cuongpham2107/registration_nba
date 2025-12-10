<?php

namespace App\Filament\Resources\RegistrationVehicleResource\Filters;

use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class RegistrationVehicleFilter extends Filter
{
    public static function make(?string $name = 'vehicle_filter'): static
    {
        return parent::make($name);
    }

    protected function setUp(): void
    {
        $this
            ->form([
                TextInput::make('search')
                    ->label('Tìm kiếm')
                    ->placeholder('Tên tài xế, CMND, biển số xe, số Hawb...'),
                Select::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'none' => 'Chưa gửi',
                        'sent' => 'Cần duyệt',
                        'approve' => 'Đã phê duyệt',
                        'entering' => 'Đang vào',
                        'exited' => 'Đã ra',
                        'reject' => 'Bị từ chối',
                    ])
                    ->default('sent'),
                DatePicker::make('start_date')
                    ->label('Từ ngày')
                    ->placeholder('Chọn ngày bắt đầu')
                    ->format('d-m-Y'),
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
                            return $query->where('driver_name', 'like', "%{$search}%")
                                ->orWhere('driver_id_card', 'like', "%{$search}%")
                                ->orWhere('vehicle_number', 'like', "%{$search}%")
                                ->orWhere('hawb_number', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%");
                        }),
                    )
                    ->when(
                        $data['status'],
                        fn (Builder $query, $status): Builder => $query->where('status', $status),
                    )
                    ->when(
                        $data['start_date'] && !$data['end_date'],
                        fn (Builder $query) => 
                            $query->whereDate('created_at', Carbon::parse($data['start_date'], 'Asia/Ho_Chi_Minh'))
                    )
                    ->when(
                        $data['start_date'] && $data['end_date'],
                        fn (Builder $query) => 
                            $query->whereBetween(
                                'created_at', 
                                [
                                    Carbon::parse($data['start_date'], 'Asia/Ho_Chi_Minh')->startOfDay(), 
                                    Carbon::parse($data['end_date'], 'Asia/Ho_Chi_Minh')->endOfDay()
                                ]
                            )
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
                        'none' => 'Chưa gửi',
                        'sent' => 'Cần duyệt',
                        'approve' => 'Đã phê duyệt',
                        'entering' => 'Đang vào',
                        'exited' => 'Đã ra',
                        'reject' => 'Bị từ chối',
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
