<?php

namespace App\Filament\Resources\RegistrationVehicleResource\Pages;

use App\Filament\Resources\RegistrationVehicleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Models\RegistrationVehicle;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

class ListRegistrationVehicles extends ListRecords
{
    protected static string $resource = RegistrationVehicleResource::class;

    // Sử dụng custom view để thêm auto-reload script
    protected static string $view = 'filament.resources.registration-vehicle-resource.pages.list-registration-vehicles';

    // Listen for the refresh-vehicle-table event
    #[On('refresh-vehicle-table')]
    public function refreshTable(): void
    {
        // This will refresh the entire Livewire component and reload the table
        $this->resetTable();
    }

    protected function applyFiltersToTableQuery(Builder $query): Builder
    {
        $query = parent::applyFiltersToTableQuery($query);
        
        // Lấy filter data
        $filterData = $this->tableFilters['vehicle_filter'] ?? [];
        $isPriorityEnabled = $filterData['is_priority'] ?? false;
        
        // Xóa order by cũ và thêm order mới
        $query->getQuery()->orders = null;
        
        // Nếu filter is_priority được bật, sắp xếp theo is_priority trước, sau đó expected_in_at
        if ($isPriorityEnabled === true) {
            $query->orderByRaw('is_priority DESC, expected_in_at DESC');
        } else {
            $query->orderBy('sort', 'asc');
        }
        
        return $query;
    }

    public function getHeading(): string
    {
        return 'Danh sách đăng ký xe khai thác';
    }
    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make()
            //     ->label('Thêm đăng ký xe')
            //     ->modalHeading('Thêm đăng ký xe khai thác')
        ];
    }
    public function reorderTable(array $order): void
    {
        if (!$this->getTable()->isReorderable()) {
            return;
        }

        $orderColumn = Str::afterLast($this->getTable()->getReorderColumn(), '.');
        $model = app($this->getTable()->getModel());
        $modelKeyName = $model->getKeyName();

        DB::transaction(function () use ($order, $orderColumn, $model, $modelKeyName) {
            // 1. Cập nhật sort cho RegistrationVehicle
            // $order = [0 => "49", 1 => "51", 2 => "50"]
            // Nghĩa là: ID 49 → sort=1, ID 51 → sort=2, ID 50 → sort=3
            foreach ($order as $position => $recordId) {
                $model->newModelQuery()
                    ->where($modelKeyName, $recordId)
                    ->update([
                        $orderColumn => $position + 1,
                    ]);
            }

            // 2. Lấy lại RegistrationVehicle sau khi cập nhật (để có sort mới)
            $registrationVehicleRecords = $model->newModelQuery()
                ->whereIn($modelKeyName, array_values($order))
                ->with('registerDirectly')
                ->get()
                ->keyBy($modelKeyName);

            // 3. Cập nhật sort cho RegisterDirectly tương ứng
            foreach ($order as $recordId) {
                $registrationVehicle = $registrationVehicleRecords->get($recordId);

                if ($registrationVehicle && $registrationVehicle->registerDirectly) {
                    $registrationVehicle->registerDirectly->update([
                        'sort' => $registrationVehicle->sort,
                    ]);
                }
            }
        });

        // Thông báo thành công
        \Filament\Notifications\Notification::make()
            ->title('Cập nhật thứ tự thành công')
            ->body('Đã đồng bộ thứ tự cho RegistrationVehicle và RegisterDirectly.')
            ->success()
            ->send();
        try {
            $protectUsers = \App\Models\User::role('protect')->get();
            foreach ($protectUsers as $user) {
                \Filament\Notifications\Notification::make()
                    ->title('Cập nhập thứ tự ra vào cho xe khai thác')
                    ->body('Thứ tự xe khác thác đã được cập nhật.')
                    ->success()
                    ->broadcast($user);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Broadcast notification failed: ' . $e->getMessage());
        }

    }
}
