<?php

namespace App\Filament\Resources\RegistrationEntries\Pages;

use App\Filament\Resources\RegistrationEntries\RegistrationEntryResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;

class ListRegistrationEntries extends ListRecords
{
    protected static string $resource = RegistrationEntryResource::class;

    protected string $view = 'filament.resources.registration-entry-resource.pages.list-registration-entries';

    #[On('refresh-table')]
    public function refreshTable(): void
    {
        $this->resetTable();
    }

    protected function applyFiltersToTableQuery(Builder $query, bool $isResolvingRecord = false): Builder
    {
        $query = parent::applyFiltersToTableQuery($query);

        $filterData = $this->tableFilters['date_range'] ?? [];
        $isPriorityEnabled = $filterData['is_priority'] ?? false;

        $query->getQuery()->orders = null;

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

    public function getHeading(): string
    {
        return 'Danh sách đơn đăng ký';
    }
}
