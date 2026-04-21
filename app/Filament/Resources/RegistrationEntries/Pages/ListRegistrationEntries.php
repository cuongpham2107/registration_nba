<?php

namespace App\Filament\Resources\RegistrationEntries\Pages;

use App\Filament\Resources\RegistrationEntries\RegistrationEntryResource;
use App\Filament\Resources\Registrations\Actions\ImportGuestsAction;
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

            // Action::make('create_registration_entry')
            //     ->label('Tạo đăng ký kiểm hoá')
            //     ->icon('heroicon-s-plus')
            //     ->modalWidth(Width::ScreenTwoExtraLarge)
            //     ->schema([
            //         Repeater::make('guests')
            //             ->table([
            //                 TableColumn::make('Tên khách')
            //                     ->markAsRequired()
            //                     ->width('200px'),
            //                 TableColumn::make('Số điện thoại')
            //                     ->width('100px'),
            //                 TableColumn::make('Số giấy tờ')
            //                     ->markAsRequired()
            //                     ->width('150px'),
            //                 TableColumn::make('Loại giấy tờ')
            //                     ->markAsRequired()
            //                     ->width('100px'),
            //                 TableColumn::make('Biển số')
            //                     ->markAsRequired()
            //                     ->width('150px'),
            //                 TableColumn::make('Loại phương tiện')
            //                     ->markAsRequired()
            //                     ->width('250px'),
            //                 TableColumn::make('Ghi chú')
            //                     ->width('150px'),
            //             ])
            //             ->label('Khách')
            //             ->compact()
            //             ->cloneable()
            //             ->afterLabel([
            //                 ImportGuestsAction::make(),
            //             ])
            //             ->schema([
            //                 TextInput::make('name')
            //                     ->required(),
            //                 TextInput::make('phone')
            //                     ->required(),
            //                 TextInput::make('papers')
            //                     ->required(),
            //                 Select::make('type')
            //                     ->required()
            //                     ->options([
            //                         'cmnd' => 'CMND/CCCD',
            //                         'driver_license' => 'Bằng lái xe',
            //                         'passport' => 'Hộ chiếu',
            //                         'other' => 'Khác',
            //                     ])
            //                     ->default('cmnd'),
            //                 TextInput::make('license_plate'),
            //                 Select::make('fee_id')
            //                     ->label('Loại phương tiện')
            //                     ->options(Fee::all()->pluck('vehicle_type', 'id')->toArray()
            //                     )
            //                     ->searchable()
            //                     ->preload(),
            //                 TextInput::make('note'),
            //             ])
            //             ->defaultItems(1)
            //             ->columns(6),
            //     ])
            //     ->action(function (array $data): void {
            //         if (empty($data['guests']) || ! is_array($data['guests'])) {
            //             Notification::make()
            //                 ->danger()
            //                 ->title('Lỗi dữ liệu')
            //                 ->body('Vui lòng thêm ít nhất một khách vào đơn đăng ký.')
            //                 ->send();

            //             return;
            //         }

            //         try {
            //             foreach ($data['guests'] as $guestData) {
            //                 // Validate required fields
            //                 if (empty($guestData['name']) || empty($guestData['papers']) || empty($guestData['type']) || empty($guestData['fee_id'])) {
            //                     Notification::make()
            //                         ->danger()
            //                         ->title('Lỗi dữ liệu')
            //                         ->body('Vui lòng điền đầy đủ thông tin cho tất cả khách.')
            //                         ->send();

            //                     return;
            //                 }

            //                 $guest = Guest::create([
            //                     'name' => $guestData['name'],
            //                     'phone' => $guestData['phone'] ?? null,
            //                     'papers' => $guestData['papers'],
            //                     'type' => $guestData['type'],
            //                     'areas' => null,
            //                     'license_plate' => $guestData['license_plate'] ?? null,
            //                     'fee_id' => $guestData['fee_id'],
            //                     'note' => $guestData['note'] ?? null,
            //                 ]);
            //                 // dd($guest);

            //                 RegistrationEntry::create([
            //                     'name' => $guestData['name'],
            //                     'papers' => $guestData['papers'],
            //                     'license_plate' => $guestData['license_plate'] ?? null,
            //                     'guest_id' => $guest->id,
            //                     'start_date' => now(),
            //                     'type' => 'inspection',
            //                     'job' => $guestData['note'] ?? null,
            //                     'status' => 'none',
            //                 ]);
            //             }
            //             Notification::make()
            //                 ->success()
            //                 ->title('Tạo đăng ký thành công')
            //                 ->body('Đã tạo đơn đăng ký kiểm hoá cho các khách đã nhập.')
            //                 ->send();
            //         } catch (\Throwable $e) {
            //             report($e);

            //             Notification::make()
            //                 ->danger()
            //                 ->title('Có lỗi xảy ra')
            //                 ->body('Không thể tạo đơn đăng ký. Vui lòng thử lại hoặc liên hệ quản trị viên.')
            //                 ->send();
            //         }
            //     }),
            Action::make('create_registration_entry')
                ->label('Tạo đăng ký kiểm hoá')
                ->modalWidth(Width::Large)
                ->icon('heroicon-s-plus')
                ->schema([
                    TextInput::make('license_plate')
                        ->label('Biển số xe')
                        ->required(),
                    Select::make('fee_id')
                        ->label('Loại phương tiện')
                        ->options(Fee::all()->pluck('vehicle_type', 'id')->toArray()
                        )
                        ->required()
                        ->searchable()
                        ->preload(),
                ])
                ->action(function (array $data): void {
                    try {
                        $guest = Guest::create([
                            'name' => $data['license_plate'], // Tạm thời lấy tên khách bằng biển số xe, có thể chỉnh lại sau nếu cần
                            'phone' => null,
                            'papers' => null,
                            'type' => null,
                            'areas' => null,
                            'license_plate' => $data['license_plate'],
                            'fee_id' => $data['fee_id'],
                            'note' => null,
                        ]);
                        // dd($guest);

                        RegistrationEntry::create([
                            'name' => $data['license_plate'],
                            'papers' => null,
                            'license_plate' => $data['license_plate'] ?? null,
                            'guest_id' => $guest->id,
                            'start_date' => now(),
                            'type' => 'inspection',
                            'job' => null,
                            'status' => 'none',
                        ]);
                        Notification::make()
                            ->success()
                            ->title('Tạo đăng ký thành công')
                            ->body('Đã tạo đơn đăng ký kiểm hoá cho khách có biển số '.$data['license_plate'].'.')
                            ->send();
                    } catch (\Throwable $e) {
                        report($e);

                        Notification::make()
                            ->danger()
                            ->title('Có lỗi xảy ra')
                            ->body('Không thể tạo đơn đăng ký. Vui lòng thử lại hoặc liên hệ quản trị viên.')
                            ->send();
                    }
                })
                ->modalSubmitAction(fn (Action $action) => $action
                    ->label('Tạo')
                ),

        ];
    }

    #[On('card-scanned')]
    public function onCardScanned(string $code): void
    {
        $record = RegistrationEntry::query()
            ->whereHas('card', fn ($query) => $query->where('account_id', $code))
            ->where('status', 'entering')
            ->first();
        if (! $record) {
            Notification::make()
                ->title('Không tìm thấy bản ghi')
                ->body("Không tìm thấy đơn đăng ký đang ở trạng thái 'Đang vào' với mã thẻ: {$code}.")
                ->warning()
                ->send();

            return;
        }

        $this->mountTableAction('return-card', $record->getKey());
    }
}
