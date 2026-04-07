<?php

namespace App\Filament\Resources\VehicleRegistrations\Pages;

use App\Filament\Resources\VehicleRegistrations\VehicleRegistrationResource;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Attributes\On;

class ListVehicleRegistrations extends ListRecords
{
    protected static string $resource = VehicleRegistrationResource::class;

    protected string $view = 'filament.resources.vehicle-registration-resource.pages.list-vehicle-registrations';

    #[On('refresh-vehicle-table')]
    public function refreshTable(): void
    {
        $this->resetTable();
    }

    protected function applyFiltersToTableQuery(Builder $query, bool $isResolvingRecord = false): Builder
    {
        $query = parent::applyFiltersToTableQuery($query, $isResolvingRecord);

        $filterData = $this->tableFilters['vehicle_filter'] ?? [];
        $isPriorityEnabled = $filterData['is_priority'] ?? false;

        $query->getQuery()->orders = null;

        if ($isPriorityEnabled === true) {
            $query->orderByRaw('is_priority DESC, expected_in_at DESC');
        } else {
            $query->orderBy('sort', 'asc');
        }

        return $query;
    }

    public function getHeading(): string
    {
        return 'Danh sách Đăng ký xe kiểm hoá';
    }

    public function reorderTable(array $order, string|int|null $draggedRecordKey = null): void
    {
        if (! $this->getTable()->isReorderable()) {
            return;
        }

        $orderColumn = Str::afterLast($this->getTable()->getReorderColumn(), '.');
        $model = app($this->getTable()->getModel());
        $modelKeyName = $model->getKeyName();

        DB::transaction(function () use ($order, $orderColumn, $model, $modelKeyName) {
            foreach ($order as $position => $recordId) {
                $model->newModelQuery()
                    ->where($modelKeyName, $recordId)
                    ->update([
                        $orderColumn => $position + 1,
                    ]);
            }

            $registrationVehicleRecords = $model->newModelQuery()
                ->whereIn($modelKeyName, array_values($order))
                ->with('registrationEntry')
                ->get()
                ->keyBy($modelKeyName);

            foreach ($order as $recordId) {
                $registrationVehicle = $registrationVehicleRecords->get($recordId);

                if ($registrationVehicle && $registrationVehicle->registrationEntry) {
                    $registrationVehicle->registrationEntry->update([
                        'sort' => $registrationVehicle->sort,
                    ]);
                }
            }
        });

        Notification::make()
            ->title('Cập nhật thứ tự thành công')
            ->body('Đã đồng bộ thứ tự cho VehicleRegistration và RegistrationEntry.')
            ->success()
            ->send();
        try {
            $protectUsers = User::role('protect')->get();
            foreach ($protectUsers as $user) {
                Notification::make()
                    ->title('Cập nhập thứ tự ra vào cho xe khai thác')
                    ->body('Thứ tự xe khác thác đã được cập nhật.')
                    ->success()
                    ->broadcast($user);
            }
        } catch (\Exception $e) {
            Log::error('Broadcast notification failed: '.$e->getMessage());
        }
    }
}
