<?php

namespace App\Filament\Resources\RegistrationEntries\Pages;

use App\Filament\Resources\RegistrationEntries\RegistrationEntryResource;
use App\Models\Fee;
use App\Models\Guest;
use App\Models\RegistrationEntry;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;
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
        $query->getQuery()->orders = null;
        $query->orderByRaw("
            CASE
                WHEN status = 'none' OR status IS NULL OR status = '' THEN 0
                WHEN status = 'coming_in' THEN 1
                WHEN status = 'came_out' THEN 2
                ELSE 3
            END ASC,
            created_at DESC
        ");

        return $query;
    }

    public function getHeading(): string
    {
        return 'Danh sách đơn đăng ký';
    }

    protected function getHeaderActions(): array
    {
        return [

            Action::make('create_registration_entry')
                ->label('Tạo đăng ký kiểm hoá')
                ->icon('heroicon-s-plus')
                ->modalWidth(Width::ScreenTwoExtraLarge)
                ->schema([
                    Repeater::make('guests')
                        ->table([
                            TableColumn::make('Tên khách')
                                ->markAsRequired()
                                ->width('200px'),
                            TableColumn::make('Số giấy tờ')
                                ->markAsRequired()
                                ->width('150px'),
                            TableColumn::make('Loại giấy tờ')
                                ->markAsRequired()
                                ->width('150px'),
                            TableColumn::make('Biển số')
                                ->markAsRequired()
                                ->width('150px'),
                            TableColumn::make('Loại phương tiện')
                                ->markAsRequired()
                                ->width('250px'),
                            TableColumn::make('Ghi chú')
                                ->width('150px'),
                        ])
                        ->label('Khách')
                        ->compact()
                        ->cloneable()
                        ->schema([
                            TextInput::make('name')
                                ->required(),
                            TextInput::make('papers')
                                ->required(),
                            TextInput::make('type')
                                ->required(),
                            TextInput::make('license_plate'),
                            Select::make('fee_id')
                                ->label('Loại phương tiện')
                                ->options(Fee::all()->pluck('vehicle_type', 'id')->toArray()
                                )
                                ->searchable()
                                ->preload(),
                            TextInput::make('note'),
                        ])
                        ->defaultItems(1)
                        ->columns(6),
                ])
                ->action(function (array $data): void {
                    if (empty($data['guests']) || ! is_array($data['guests'])) {
                        Notification::make()
                            ->danger()
                            ->title('Lỗi dữ liệu')
                            ->body('Vui lòng thêm ít nhất một khách vào đơn đăng ký.')
                            ->send();

                        return;
                    }

                    try {
                        foreach ($data['guests'] as $guestData) {
                            // Validate required fields
                            if (empty($guestData['name']) || empty($guestData['papers']) || empty($guestData['type']) || empty($guestData['fee_id'])) {
                                Notification::make()
                                    ->danger()
                                    ->title('Lỗi dữ liệu')
                                    ->body('Vui lòng điền đầy đủ thông tin cho tất cả khách.')
                                    ->send();

                                return;
                            }

                            $guest = Guest::create([
                                'name' => $guestData['name'],
                                'papers' => $guestData['papers'],
                                'type' => $guestData['type'],
                                'areas' => null,
                                'license_plate' => $guestData['license_plate'] ?? null,
                                'fee_id' => $guestData['fee_id'],
                                'note' => $guestData['note'] ?? null,
                            ]);
                            // dd($guest);

                            RegistrationEntry::create([
                                'name' => $guestData['name'],
                                'papers' => $guestData['papers'],
                                'license_plate' => $guestData['license_plate'] ?? null,
                                'guest_id' => $guest->id,
                                'start_date' => now(),
                                'type' => 'inspection',
                                'job' => $guestData['note'] ?? null,
                                'status' => 'none',
                            ]);
                        }
                        Notification::make()
                            ->success()
                            ->title('Tạo đăng ký thành công')
                            ->body('Đã tạo đơn đăng ký kiểm hoá cho các khách đã nhập.')
                            ->send();
                    } catch (\Throwable $e) {
                        report($e);

                        Notification::make()
                            ->danger()
                            ->title('Có lỗi xảy ra')
                            ->body('Không thể tạo đơn đăng ký. Vui lòng thử lại hoặc liên hệ quản trị viên.')
                            ->send();
                    }
                }),
        ];
    }
}
