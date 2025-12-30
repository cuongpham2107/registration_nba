<?php

namespace App\Filament\Resources\RegisterDirectlyResource\Pages;

use App\Filament\Resources\RegisterDirectlyResource;
use App\Models\Card;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;

class ListRegisterDirectlies extends ListRecords
{
    protected static string $resource = RegisterDirectlyResource::class;

    // Sử dụng custom view để thêm auto-reload script
    protected static string $view = 'filament.resources.register-directly-resource.pages.list-register-directlies';

    // Listen for the refresh-table event
    #[On('refresh-table')]
    public function refreshTable(): void
    {
        // This will refresh the entire Livewire component and reload the table
        $this->resetTable();
    }

    protected function applyFiltersToTableQuery(Builder $query): Builder
    {
        $query = parent::applyFiltersToTableQuery($query);

        // Lấy filter data
        $filterData = $this->tableFilters['date_range'] ?? [];
        $isPriorityEnabled = $filterData['is_priority'] ?? false;
        $searchTerm = $filterData['search'] ?? null;

        // Xóa order by cũ và thêm order mới
        $query->getQuery()->orders = null;

        // Sắp xếp theo thứ tự trạng thái: none -> coming_in -> came_out
        if ($isPriorityEnabled === true) {
            $query->orderByRaw("
                CASE 
                    WHEN status = 'none' OR status IS NULL OR status = '' THEN 0 
                    WHEN status = 'coming_in' THEN 1
                    WHEN status = 'came_out' THEN 2
                    ELSE 3 
                END ASC,
                is_priority DESC,
                sort ASC,
                created_at DESC
            ");
        } else {
            $query->orderByRaw("
                CASE 
                    WHEN status = 'none' OR status IS NULL OR status = '' THEN 0 
                    WHEN status = 'coming_in' THEN 1
                    WHEN status = 'came_out' THEN 2
                    ELSE 3 
                END ASC,
                created_at DESC
            ");
        }

        return $query;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Thêm đăng ký mới')
                ->modalHeading('Thêm đăng ký trực tiếp')
                ->modalWidth(MaxWidth::ScreenExtraLarge)
                ->slideOver()
                ->mutateFormDataUsing(function (array $data): array {

                    $card = Card::where('id', $data['card_id'])->first();
                    $card->status = 'active';
                    $card->save();

                    return $data;
                }),
        ];
    }

    public function getHeading(): string
    {
        return 'Danh sách đơn đăng ký';
    }
}
