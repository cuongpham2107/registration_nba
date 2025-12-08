<?php

namespace App\Filament\Resources\RegistrationResource\Filters;

use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class RegistrationFilter extends Filter
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
                    ->placeholder('Tên đơn vị, người liên hệ, mục đích...'),
                Select::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'sent' => 'Đã gửi',
                        'not_yet_sent' => 'Chưa gửi',
                    ]),
                    
                Select::make('type')
                    ->label('Duyệt')
                    ->options([
                        'none' => 'Chưa duyệt',
                        'browse' => 'Duyệt',
                        'refuse' => 'Từ chối',
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
            ->query(function (Builder $query, array $data): Builder {
                return $query
                    ->when(
                        $data['search'],
                        fn (Builder $query, $search): Builder => $query->where(function ($query) use ($search) {
                            return $query->where('name', 'like', "%{$search}%")
                                ->orWhere('contact_person', 'like', "%{$search}%")
                                ->orWhere('purpose', 'like', "%{$search}%");
                        }),
                    )
                    ->when(
                        $data['status'],
                        fn (Builder $query, $status): Builder => $query->where('status', $status),
                    )
                    ->when(
                        isset($data['type']),
                        function (Builder $query) use ($data) {
                            if ($data['type'] === 'none') {
                                return $query->whereNull('type');
                            }
                            return $query->where('type', $data['type']);
                        }
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
                        'sent' => 'Đã gửi',
                        'not_yet_sent' => 'Chưa gửi',
                        default => $data['status'],
                    };
                    $indicators[] = Indicator::make('Trạng thái: ' . $statusText)
                        ->removeField('status');
                }
                
                if ($data['type'] ?? null) {
                    $typeText = match ($data['type']) {
                        'none' => 'Chưa duyệt',
                        'browse' => 'Duyệt',
                        'refuse' => 'Từ chối',
                        default => $data['type'],
                    };
                    $indicators[] = Indicator::make('Duyệt: ' . $typeText)
                        ->removeField('type');
                }
                
                if ($data['start_date'] ?? null) {
                    $indicators[] = Indicator::make('Từ ngày: ' . Carbon::parse($data['start_date'], 'Asia/Ho_Chi_Minh')->format('d/m/Y'))
                        ->removeField('start_date');
                }
                
                if ($data['end_date'] ?? null) {
                    $indicators[] = Indicator::make('Đến ngày: ' . Carbon::parse($data['end_date'], 'Asia/Ho_Chi_Minh')->format('d/m/Y'))
                        ->removeField('end_date');
                }
                
                return $indicators;
            });
    }
}