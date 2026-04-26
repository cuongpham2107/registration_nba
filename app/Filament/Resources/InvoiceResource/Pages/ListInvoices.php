<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Models\Invoice;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Tất cả')
                ->badge(Invoice::count())
                ->badgeColor('primary'),

            'paid' => Tab::make('Đã thanh toán')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_paid', true))
                ->badge(Invoice::where('is_paid', true)->count())
                ->badgeColor('success'),

            'unpaid' => Tab::make('Chưa thanh toán')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_paid', false))
                ->badge(Invoice::where('is_paid', false)->count())
                ->badgeColor('warning'),

            'today' => Tab::make('Hôm nay')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('created_at', today()))
                ->badge(Invoice::whereDate('created_at', today())->count())
                ->badgeColor('info'),

            'this_week' => Tab::make('Tuần này')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek(),
                ]))
                ->badge(Invoice::whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek(),
                ])->count())
                ->badgeColor('gray'),

            'this_month' => Tab::make('Tháng này')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year))
                ->badge(Invoice::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)->count())
                ->badgeColor('purple'),
        ];
    }
}
